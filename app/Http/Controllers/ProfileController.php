<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->loadCount(['createdItems', 'stockMovements', 'handoverDocuments']);

        $myRecentLogs = AccessLog::where('user_id', $user->id)
            ->latest('accessed_at')
            ->limit(8)
            ->get();

        $allUsers = collect();
        if ($user->isAdmin()) {
            $allUsers = User::withCount(['createdItems', 'stockMovements', 'handoverDocuments'])
                ->orderBy('role')
                ->orderBy('name')
                ->get();
        }

        return view('profile.edit', compact('user', 'myRecentLogs', 'allUsers'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'employee_id' => ['nullable', 'string', 'max:60'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $user->update($validated);

        AccessLog::record(
            'activity',
            "Memperbarui pengaturan profil pengguna ({$user->name})",
            $user,
            $request
        );

        return back()->with('success', 'Pengaturan profil Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama yang Anda masukkan tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AccessLog::record(
            'activity',
            "Mengubah password keamanan akun pribadi ({$user->email})",
            $user,
            $request
        );

        return back()->with('success', 'Password akun Anda berhasil diperbarui.');
    }

    public function storeUser(Request $request)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isAdmin(), 403, 'Hanya Admin yang dapat menambahkan akun petugas baru.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'employee_id' => ['nullable', 'string', 'max:60'],
            'role' => ['required', 'in:admin,staff'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'username' => trim($validated['username']),
            'email' => trim($validated['email']),
            'employee_id' => $validated['employee_id'] ?? null,
            'role' => $validated['role'],
            'job_title' => $validated['job_title'] ?? 'Petugas Logistik',
            'department' => $validated['department'] ?? 'Divisi Logistik',
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        AccessLog::record(
            'activity',
            "Menambahkan akun pengguna baru: {$newUser->name} ({$newUser->email} - Role: {$newUser->role})",
            $admin,
            $request
        );

        return back()->with('success', "Akun pengguna \"{$newUser->name}\" berhasil dibuat.");
    }

    public function toggleUser(Request $request, User $targetUser)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isAdmin(), 403);

        if ($targetUser->id === $admin->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $targetUser->update([
            'is_active' => !$targetUser->is_active,
        ]);

        $statusLabel = $targetUser->is_active ? 'mengaktifkan' : 'menonaktifkan';
        AccessLog::record(
            'activity',
            "Admin {$statusLabel} akun pengguna: {$targetUser->name}",
            $admin,
            $request
        );

        return back()->with('success', "Status akun \"{$targetUser->name}\" berhasil diperbarui.");
    }
}
