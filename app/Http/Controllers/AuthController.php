<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $user = User::where('email', $loginValue)
            ->orWhere('username', $loginValue)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
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
