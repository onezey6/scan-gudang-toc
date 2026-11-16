<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $avatarPath = $user->avatar;

        if ($request->hasFile('avatar')) {
            $this->deleteAvatarFile($user->avatar);
            $avatarPath = $this->handleAvatarUpload($request);
        } elseif ($request->boolean('remove_avatar')) {
            $this->deleteAvatarFile($user->avatar);
            $avatarPath = null;
        }

        $validated['avatar'] = $avatarPath;

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
        abort_unless($admin && $admin->isAdmin(), 403, 'Hanya Administrator yang dapat menambahkan akun pengguna baru.');

        $allowedRoles = $admin->isSuperAdmin() ? ['super_admin', 'admin', 'staff'] : ['admin', 'staff'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'employee_id' => ['nullable', 'string', 'max:60'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
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

        // Admin biasa tidak boleh menonaktifkan Super Admin
        if ($targetUser->isSuperAdmin() && !$admin->isSuperAdmin()) {
            abort(403, 'Admin biasa tidak memiliki wewenang untuk menonaktifkan Super Admin.');
        }

        $targetUser->update([
            'is_active' => !$targetUser->is_active,
        ]);

        $statusLabel = $targetUser->is_active ? 'mengaktifkan' : 'menonaktifkan';
        AccessLog::record(
            'activity',
            "Admin ({$admin->name}) {$statusLabel} akun pengguna: {$targetUser->name}",
            $admin,
            $request
        );

        return back()->with('success', "Status akun \"{$targetUser->name}\" berhasil diperbarui.");
    }

    public function destroyUser(Request $request, User $targetUser)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isSuperAdmin(), 403, 'Akses ditolak: Hanya Super Admin yang berhak menghapus akun pengguna.');

        if ($targetUser->id === $admin->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.']);
        }

        // Pastikan tidak menghapus Super Admin terakhir
        if ($targetUser->isSuperAdmin()) {
            $otherSuperAdminsCount = User::where('role', 'super_admin')
                ->where('id', '!=', $targetUser->id)
                ->count();

            if ($otherSuperAdminsCount < 1) {
                return back()->withErrors(['user' => 'Tidak dapat menghapus akun Super Admin terakhir pada sistem.']);
            }
        }

        $userName = $targetUser->name;
        $userEmail = $targetUser->email;
        $userRole = $targetUser->role;

        $targetUser->delete(); // Soft delete aman

        AccessLog::record(
            'activity',
            "Super Admin ({$admin->name}) menghapus akun pengguna: {$userName} ({$userEmail} - Role: " . strtoupper($userRole) . ")",
            $admin,
            $request
        );

        return back()->with('success', "Akun pengguna \"{$userName}\" berhasil dihapus.");
    }

    private function handleAvatarUpload(Request $request): ?string
    {
        if (!$request->hasFile('avatar') || !$request->file('avatar')->isValid()) {
            return null;
        }

        $uploadDir = public_path('uploads/avatars');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $file = $request->file('avatar');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'avatar_' . Auth::id() . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $file->move($uploadDir, $filename);

        return 'uploads/avatars/' . $filename;
    }

    private function deleteAvatarFile(?string $avatarPath): void
    {
        if (empty($avatarPath) || !str_starts_with($avatarPath, 'uploads/avatars/')) {
            return;
        }

        $fullPath = public_path($avatarPath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
