@extends('layouts.app')

@section('title', 'Setingan Profil & Manajemen Akun')
@section('page_title', 'Setingan Profil & Manajemen Pengguna')
@section('page_subtitle', 'Atur informasi profil, jabatan berita acara, keamanan password, serta kelola akun petugas logistik')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 7 Cols: Edit Profile & Password -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Digital Logistics ID Card User -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#0B1120] via-[#0F172A] to-emerald-950 text-white p-5 sm:p-6 rounded-3xl shadow-xl border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="pointer-events-none absolute -right-12 -top-12 w-48 h-48 rounded-full bg-emerald-500/15 blur-2xl"></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-xl font-extrabold shadow-lg ring-2 ring-white/20">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            ID CARD PETUGAS LOGISTIK RESMI
                        </span>
                        <div class="text-lg sm:text-xl font-extrabold mt-1">{{ $user->name }}</div>
                        <div class="text-xs text-emerald-400 font-semibold">{{ $user->job_title ?: 'Petugas Logistik' }} • {{ $user->department ?: 'Divisi Logistik' }}</div>
                        <div class="text-xs text-slate-400 font-mono-code mt-0.5">NIP/ID: {{ $user->employee_id ?: 'NIP-LOG-00' . $user->id }} • Role: {{ strtoupper($user->role) }}</div>
                    </div>
                </div>
                <div class="relative z-10 grid grid-cols-3 gap-2 text-center bg-slate-900/90 p-3.5 rounded-2xl border border-slate-700/80">
                    <div class="px-2">
                        <div class="text-lg font-extrabold text-white">{{ $user->created_items_count }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Barang Dibuat</div>
                    </div>
                    <div class="px-2 border-x border-slate-700">
                        <div class="text-lg font-extrabold text-emerald-400">{{ $user->stock_movements_count }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Total Scan</div>
                    </div>
                    <div class="px-2">
                        <div class="text-lg font-extrabold text-amber-400">{{ $user->handover_documents_count }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Berita Acara</div>
                    </div>
                </div>
            </div>

            <!-- Form Setingan Profil -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Informasi Profil & Identitas Petugas</h3>
                    <p class="text-xs text-slate-500">Data ini otomatis digunakan saat Anda menambahkan barang, scan barcode, dan membuat Berita Acara</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Lengkap *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Username Login *</label>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Alamat Email *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">NIP / ID Pegawai</label>
                            <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                                   placeholder="Contoh: NIP-LOG-001"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Jabatan (Untuk BAST/BAP)</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                   placeholder="Contoh: Kepala Gudang / Petugas Scan"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Divisi / Unit Kerja</label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}"
                                   placeholder="Contoh: Divisi Logistik & Inventaris"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-xs">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form Ganti Password -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Ubah Password Keamanan</h3>
                    <p class="text-xs text-slate-500">Pastikan menggunakan kombinasi password yang aman</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Password Saat Ini *</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Password Baru *</label>
                            <input type="password" name="password" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Ulangi Password Baru *</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold">
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right 5 Cols: Admin User Management & Personal Access History -->
        <div class="lg:col-span-5 space-y-6">
            @if ($user->isAdmin())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 sm:p-5 bg-slate-900 text-white">
                        <h3 class="text-sm font-bold">Manajemen Akun Petugas (Khusus Admin)</h3>
                        <p class="text-xs text-slate-300 mt-0.5">Tambahkan akun petugas baru agar setiap orang punya log tambah & scan barang masing-masing</p>
                    </div>

                    <form action="{{ route('profile.users.store') }}" method="POST" class="p-4 sm:p-5 space-y-3 border-b border-slate-200">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Nama Petugas *</label>
                                <input type="text" name="name" required placeholder="Nama lengkap" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Username *</label>
                                <input type="text" name="username" required placeholder="username.staff" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Email *</label>
                                <input type="email" name="email" required placeholder="staff@logiscan.id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Password *</label>
                                <input type="password" name="password" value="password123" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Peran (Role)</label>
                                <select name="role" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white">
                                    <option value="staff">Petugas / Staff</option>
                                    <option value="admin">Admin Logistik</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Jabatan</label>
                                <input type="text" name="job_title" value="Petugas Gudang Logistik" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                            + Tambah Akun Pengguna Baru
                        </button>
                    </form>

                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                        @foreach ($allUsers as $u)
                            <div class="p-3.5 flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">
                                        {{ $u->name }}
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] uppercase {{ $u->isAdmin() ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $u->role }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500">{{ $u->email }} • {{ $u->job_title }}</div>
                                    <div class="text-[11px] text-emerald-700 font-medium mt-0.5">
                                        Tambah Barang: <strong>{{ $u->created_items_count }}</strong> • Scan: <strong>{{ $u->stock_movements_count }}x</strong> • BAST/BAP: <strong>{{ $u->handover_documents_count }}</strong>
                                    </div>
                                </div>
                                @if ($u->id !== $user->id)
                                    <form action="{{ route('profile.users.toggle', $u) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                            {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Riwayat Akses Pribadi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Akses & Aktivitas Terakhir Anda</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($myRecentLogs as $log)
                        <div class="p-3.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold uppercase text-slate-800">{{ $log->event_type }}</span>
                                <span class="text-slate-400">{{ $log->accessed_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">{{ $log->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
