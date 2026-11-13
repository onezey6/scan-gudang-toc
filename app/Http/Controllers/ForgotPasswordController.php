<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'identity' => ['required', 'string'],
            'reset_channel' => ['nullable', 'in:email,phone'],
        ], [
            'identity.required' => 'Email, username, atau nomor HP wajib diisi.',
        ]);

        $identity = trim($request->input('identity'));
        $channel = $request->input('reset_channel', 'email');

        $user = User::where('email', $identity)
            ->orWhere('username', $identity)
            ->orWhere('phone', $identity)
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors(['identity' => 'Akun dengan identitas tersebut tidak ditemukan dalam sistem.']);
        }

        if (!$user->is_active) {
            return back()
                ->withInput()
                ->withErrors(['identity' => 'Akun ini sedang dinonaktifkan oleh Administrator. Hubungi admin untuk aktivasi.']);
        }

        // Generate 6-digit OTP
        $otp = (string) mt_rand(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        session([
            'reset_user_id' => $user->id,
            'reset_user_name' => $user->name,
            'reset_user_email' => $user->email,
            'reset_user_phone' => $user->phone,
            'reset_channel' => $channel,
            'reset_demo_otp' => $otp, // Kode siap pakai untuk kemudahan operasional lokal/intranet
        ]);

        AccessLog::record(
            'activity',
            "Permintaan reset kata sandi diajukan untuk akun: {$user->username} via " . strtoupper($channel),
            $user,
            $request
        );

        $targetInfo = $channel === 'phone'
            ? ($user->phone ?: 'Nomor HP Akun')
            : ($user->email ?: 'Email Akun');

        return redirect()->route('password.reset')
            ->with('success', "Kode verifikasi 6-digit berhasil dibuat untuk {$user->name} ({$targetInfo}). Masukkan kode verifikasi dan tentukan kata sandi baru.");
    }

    public function showResetForm()
    {
        if (!session()->has('reset_user_id')) {
            return redirect()->route('password.request')
                ->withErrors(['identity' => 'Silakan ajukan permintaan reset sandi terlebih dahulu.']);
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'otp.required' => 'Kode verifikasi 6-digit wajib diisi.',
            'otp.size' => 'Kode verifikasi harus tepat 6 digit angka.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $userId = session('reset_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['identity' => 'Sesi reset sandi telah berakhir. Silakan ulangi kembali.']);
        }

        $resetRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if (!$resetRecord || !Hash::check($request->input('otp'), $resetRecord->token)) {
            return back()
                ->withInput()
                ->withErrors(['otp' => 'Kode verifikasi 6-digit yang Anda masukkan tidak valid atau salah.']);
        }

        // Cek kadaluarsa (30 menit)
        if (now()->diffInMinutes($resetRecord->created_at) > 30) {
            return back()
                ->withInput()
                ->withErrors(['otp' => 'Kode verifikasi telah kadaluarsa (lebih dari 30 menit). Silakan ajukan ulang.']);
        }

        // Update password baru
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Hapus token reset
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        AccessLog::record(
            'activity',
            "Kata sandi berhasil diperbarui via modul Lupa Sandi untuk akun: {$user->username}",
            $user,
            $request
        );

        session()->forget([
            'reset_user_id',
            'reset_user_name',
            'reset_user_email',
            'reset_user_phone',
            'reset_channel',
            'reset_demo_otp',
        ]);

        return redirect()->route('login')
            ->with('success', "Kata sandi untuk akun {$user->name} berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.");
    }
}
