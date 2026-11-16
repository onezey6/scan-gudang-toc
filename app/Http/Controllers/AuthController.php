<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginValue = trim($credentials['login']);
        $throttleKey = Str::transliterate(Str::lower($loginValue) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AccessLog::record(
                'brute_force_blocked',
                "Percobaan login diblokir sementara (Proteksi Anti Brute-Force) untuk: {$loginValue}. Sisa waktu: {$seconds} detik.",
                null,
                $request,
                $loginValue
            );

            // Kirim Notifikasi Darurat ke Telegram Bot (Jika Terkonfigurasi)
            try {
                $telegram = app(\App\Services\TelegramService::class);
                if ($telegram->isConfigured()) {
                    $alertMsg = "🚨 <b>PERINGATAN KEAMANAN (BRUTE FORCE BLOCKED)</b>\n";
                    $alertMsg .= "━━━━━━━━━━━━━━━━━━━━\n";
                    $alertMsg .= "Terdeteksi serangan tebak password massal di sistem LogiScan Pro!\n\n";
                    $alertMsg .= "🎯 Target Akun: <code>" . e($loginValue) . "</code>\n";
                    $alertMsg .= "🌐 Alamat IP Penyerang: <code>" . e($request->ip()) . "</code>\n";
                    $alertMsg .= "🛡️ Status: <b>Akses Login Diblokir Sementara ({$seconds}s)</b>\n";
                    $alertMsg .= "⏰ Jam Kejadian: " . now()->format('H:i:s') . " WIB";

                    $telegram->sendNotification($alertMsg);
                }
            } catch (\Throwable $e) {}

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => "Terlalu banyak percobaan login gagal. Demi keamanan sistem, silakan tunggu {$seconds} detik sebelum mencoba lagi."]);
        }

        $user = User::where('email', $loginValue)
            ->orWhere('username', $loginValue)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            AccessLog::record(
                'failed_login',
                "Percobaan login gagal untuk akun: {$loginValue}",
                $user,
                $request,
                $loginValue
            );

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Email/Username atau password yang Anda masukkan salah.']);
        }

        if (!$user->is_active) {
            RateLimiter::hit($throttleKey, 60);

            AccessLog::record(
                'failed_login',
                "Percobaan login ditolak karena akun nonaktif: {$user->email}",
                $user,
                $request,
                $loginValue
            );

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Akun Anda sedang dinonaktifkan oleh Administrator.']);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        AccessLog::record(
            'login',
            "Berhasil masuk (Login) ke dalam web aplikasi sebagai " . strtoupper($user->role),
            $user,
            $request
        );

        return redirect()->intended(route('dashboard'))
            ->with('success', "Selamat datang kembali, {$user->name}!");
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AccessLog::record(
                'logout',
                "Keluar (Logout) dari sesi web aplikasi",
                $user,
                $request
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sesi aplikasi.');
    }
}
