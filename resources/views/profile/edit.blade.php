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
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover shadow-lg ring-2 ring-emerald-400">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-xl font-extrabold shadow-lg ring-2 ring-white/20">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
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

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Upload Foto Profil -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative shrink-0">
                            @if ($user->avatar_url)
                                <img id="profile-avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-emerald-500 shadow-sm">
                            @else
                                <div id="profile-avatar-fallback" class="w-16 h-16 rounded-2xl bg-slate-800 text-emerald-400 border-2 border-slate-700 flex items-center justify-center text-lg font-black shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <img id="profile-avatar-preview" src="" alt="Preview" class="hidden w-16 h-16 rounded-2xl object-cover border-2 border-emerald-500 shadow-sm">
                            @endif
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Foto Profil Akun Petugas</label>
                            <p class="text-xs text-slate-500 mb-2.5">Unggah foto profil Anda (Format: JPG, PNG, WEBP, Maks: 3MB). Foto akan tampil di navbar atas, profil samping, dan kartu ID.</p>
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                                <label class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold cursor-pointer transition-all inline-flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Pilih Foto Baru</span>
                                    <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                                </label>
                                @if ($user->avatar)
                                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 cursor-pointer">
                                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus Foto Profil</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

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
                    <div class="p-4 sm:p-5 {{ $user->isSuperAdmin() ? 'bg-gradient-to-r from-purple-950 via-slate-900 to-indigo-950 border-b border-purple-500/30' : 'bg-slate-900' }} text-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                @if ($user->isSuperAdmin())
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-400 text-slate-950 text-xs">👑</span>
                                    <span>Manajemen Akun Pengguna (Super Admin)</span>
                                @else
                                    <span>Manajemen Akun Petugas (Admin Logistik)</span>
                                @endif
                            </h3>
                            @if ($user->isSuperAdmin())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">Otoritas Penuh</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-300 mt-1">
                            @if ($user->isSuperAdmin())
                                Kelola seluruh akun, tambah hak akses Super Admin/Admin/Staff, serta hapus akun pengguna.
                            @else
                                Tambahkan akun petugas baru agar setiap orang punya log tambah & scan barang masing-masing.
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('profile.users.store') }}" method="POST" class="p-4 sm:p-5 space-y-3 border-b border-slate-200">
                        @csrf
                        <datalist id="jabatan-presets">
                            <option value="Kepala Seksi Logistik (Kasilog)">
                            <option value="Kepala Urusan Logistik (Kaur Logistik)">
                            <option value="Bintara Administrasi Logistik (Bamin Logistik)">
                            <option value="Operator Scanner & Barcode TOC">
                            <option value="Pengelola Gudang Senjata & Amunisi">
                            <option value="Pengelola Gudang Perlengkapan & Kaporlap">
                            <option value="Petugas Pemeriksa & Pemeliharaan Materiil">
                            <option value="Operator Piket Siaga Logistik 24 Jam">
                            <option value="Staff Administrasi Pergudangan">
                            <option value="Petugas Penerimaan & Pengeluaran Barang">
                        </datalist>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Nama Lengkap *</label>
                                <input type="text" name="name" required placeholder="Nama lengkap" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Username *</label>
                                <input type="text" name="username" required placeholder="username.petugas" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Email *</label>
                                <input type="email" name="email" required placeholder="petugas@logiscan.id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Password *</label>
                                <input type="password" name="password" value="password123" required class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Peran (Role) *</label>
                                <select name="role" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="staff">Petugas / Staff (Operator Gudang)</option>
                                    <option value="admin">Admin Logistik (Pengelola Gudang)</option>
                                    @if ($user->isSuperAdmin())
                                        <option value="super_admin">👑 Super Admin (Otoritas Tertinggi)</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Jabatan Kedinasan *</label>
                                <input type="text" name="job_title" list="jabatan-presets" value="Petugas Gudang Logistik" required placeholder="Pilih atau ketik jabatan..." class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2 rounded-xl {{ $user->isSuperAdmin() ? 'bg-purple-700 hover:bg-purple-800' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white text-xs font-semibold shadow-xs transition-colors">
                            + Tambah Akun Pengguna Baru
                        </button>
                    </form>

                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                        @foreach ($allUsers as $u)
                            <div class="p-3.5 flex items-center justify-between gap-2 hover:bg-slate-50/70 transition-colors">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-slate-900 flex items-center flex-wrap gap-1.5">
                                        <span class="truncate">{{ $u->name }}</span>
                                        @if ($u->isSuperAdmin())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-950 text-purple-200 border border-purple-500/40 shadow-xs">
                                                <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                Super Admin
                                            </span>
                                        @elseif ($u->role === 'admin')
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-900 text-white">
                                                Admin
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Staff
                                            </span>
                                        @endif
                                        @if ($u->id === $user->id)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-medium bg-blue-100 text-blue-800 border border-blue-200">Anda</span>
                                        @endif
                                    </div>

                                    <!-- Jabatan & Departemen Highlight -->
                                    <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 text-purple-900 border border-purple-200 text-[10px] font-bold">
                                            <span>🏷️</span>
                                            <span>{{ $u->job_title ?: 'Belum diatur' }}</span>
                                        </span>
                                        @if ($u->department)
                                            <span class="text-[10px] text-slate-500 font-medium">({{ $u->department }})</span>
                                        @endif
                                        @if ($u->employee_id)
                                            <span class="text-[10px] text-slate-400 font-mono">NRP: {{ $u->employee_id }}</span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ $u->email }}</div>
                                    <div class="text-[11px] text-emerald-700 font-medium mt-0.5">
                                        Tambah Barang: <strong>{{ $u->created_items_count }}</strong> • Scan: <strong>{{ $u->stock_movements_count }}x</strong> • BAST/BAP: <strong>{{ $u->handover_documents_count }}</strong>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    {{-- Tombol EDIT JABATAN & DATA: UNTUK SUPER ADMIN / ADMIN --}}
                                    @if ($user->isSuperAdmin() || ($user->isAdmin() && !$u->isSuperAdmin()))
                                        <button type="button" onclick="openEditUserModal({{ json_encode([
                                            'id' => $u->id,
                                            'name' => $u->name,
                                            'username' => $u->username,
                                            'email' => $u->email,
                                            'employee_id' => $u->employee_id,
                                            'role' => $u->role,
                                            'job_title' => $u->job_title,
                                            'department' => $u->department,
                                            'phone' => $u->phone,
                                            'update_url' => route('profile.users.update', $u),
                                        ]) }})" class="p-1.5 rounded-lg text-amber-600 hover:text-white hover:bg-amber-600 bg-amber-50 border border-amber-200 transition-colors" title="Atur Jabatan & Edit Data Pengguna">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    @if ($u->id !== $user->id && (!$u->isSuperAdmin() || $user->isSuperAdmin()))
                                        <form action="{{ route('profile.users.toggle', $u) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-colors {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }}" title="Ubah status akun">
                                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Tombol HAPUS AKUN: EKSKLUSIF HANYA UNTUK SUPER ADMIN --}}
                                    @if ($user->isSuperAdmin() && $u->id !== $user->id)
                                        <form action="{{ route('profile.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS akun pengguna \'{{ addslashes($u->name) }}\'? Tindakan ini bersifat permanen dan hanya dapat dilakukan oleh Super Admin.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:text-white hover:bg-rose-600 bg-rose-50 border border-rose-200 transition-colors" title="Hapus Akun Pengguna (Hanya Super Admin)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Kartu Cadangan Database 1-Klik (Disaster Recovery) -->
                <div class="bg-gradient-to-br from-[#0B1120] to-slate-900 text-white rounded-2xl border-2 border-emerald-500/40 p-5 shadow-lg relative overflow-hidden">
                    <div class="pointer-events-none absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-emerald-500/20 blur-xl"></div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/50 flex items-center justify-center text-emerald-300 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold uppercase tracking-wide text-white">Cadangan Database (Disaster Recovery)</h4>
                            <p class="text-xs text-slate-300 mt-0.5">Unduh seluruh master barang, barcode, transaksi scan, dan BAST/BAP dalam format SQL.</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3.5 border-t border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-[11px] text-emerald-400 font-mono-code flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Mesin: MariaDB / MySQL Full Dump</span>
                        </div>
                        <a href="{{ route('settings.backup-db') }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-md active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Unduh Backup (.SQL)</span>
                        </a>
                    </div>
                <!-- Kartu Integrasi Bot Telegram Logistik -->
                <div class="bg-gradient-to-br from-[#071324] to-[#0F1E36] text-white rounded-2xl border-2 border-cyan-500/40 p-5 shadow-lg relative overflow-hidden">
                    <div class="pointer-events-none absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-cyan-500/15 blur-xl"></div>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 border border-cyan-400/50 flex items-center justify-center text-cyan-300 shrink-0 text-xl">
                                🤖
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-extrabold uppercase tracking-wide text-white">Bot Telegram LogiScan</h4>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ config('services.telegram.bot_token') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                        {{ config('services.telegram.bot_token') ? 'TERHUBUNG' : 'BELUM AKTIF' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 mt-0.5">Notifikasi otomatis mutasi scan, radar BAST jatuh tempo, dan pencarian stok via chat HP.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3.5 pt-3 border-t border-slate-700/60 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-mono">
                        <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                            <span class="text-[10px] text-slate-400 block">STATUS TOKEN BOT:</span>
                            <strong class="{{ config('services.telegram.bot_token') ? 'text-emerald-400' : 'text-slate-500' }}">
                                {{ config('services.telegram.bot_token') ? '✓ Terpasang di .env' : 'Belum diisi di .env' }}
                            </strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                            <span class="text-[10px] text-slate-400 block">CHAT ID TUJUAN:</span>
                            <strong class="{{ config('services.telegram.chat_id') ? 'text-cyan-400' : 'text-slate-500' }}">
                                {{ config('services.telegram.chat_id') ?: 'Belum diisi di .env' }}
                            </strong>
                        </div>
                    </div>

                    <div class="mt-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-[11px] text-slate-400 font-mono flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <span>Listener Lokal: <code>php artisan telegram:poll</code></span>
                        </div>
                        <button type="button" onclick="testTelegramConnection()" id="btn-test-telegram"
                                class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold transition-all shadow-md active:scale-95 cursor-pointer">
                            <span>Tes Kirim Pesan ke Telegram</span>
                        </button>
                    </div>

                    <div id="telegram-test-feedback" class="hidden mt-3 p-2.5 rounded-xl text-xs font-mono"></div>
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

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('profile-avatar-preview');
                    var fallback = document.getElementById('profile-avatar-fallback');
                    if (fallback) fallback.classList.add('hidden');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        function testTelegramConnection() {
            var btn = document.getElementById('btn-test-telegram');
            var feedback = document.getElementById('telegram-test-feedback');
            btn.disabled = true;
            btn.innerHTML = 'Mengirim...';
            feedback.className = 'hidden';

            fetch('{{ route('telegram.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Tes Kirim Pesan ke Telegram';
                feedback.classList.remove('hidden');
                if (data.success) {
                    feedback.className = 'mt-3 p-3 rounded-xl text-xs font-mono bg-emerald-950/80 border border-emerald-500/50 text-emerald-300';
                    feedback.innerHTML = '✓ ' + data.message;
                } else {
                    feedback.className = 'mt-3 p-3 rounded-xl text-xs font-mono bg-rose-950/80 border border-rose-500/50 text-rose-300';
                    feedback.innerHTML = '⚠ ' + data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Tes Kirim Pesan ke Telegram';
                feedback.classList.remove('hidden');
                feedback.className = 'mt-3 p-3 rounded-xl text-xs font-mono bg-rose-950/80 border border-rose-500/50 text-rose-300';
                feedback.innerHTML = '⚠ Terjadi kesalahan jaringan saat menghubungi server.';
            });
        }

        function openEditUserModal(userData) {
            document.getElementById('form-edit-user').action = userData.update_url;
            document.getElementById('edit-user-name').value = userData.name || '';
            document.getElementById('edit-user-username').value = userData.username || '';
            document.getElementById('edit-user-email').value = userData.email || '';
            document.getElementById('edit-user-employee-id').value = userData.employee_id || '';
            document.getElementById('edit-user-job-title').value = userData.job_title || '';
            document.getElementById('edit-user-department').value = userData.department || '';
            document.getElementById('edit-user-phone').value = userData.phone || '';

            var roleSelect = document.getElementById('edit-user-role');
            if (roleSelect) {
                roleSelect.value = userData.role || 'staff';
            }

            var modal = document.getElementById('modal-edit-user');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeEditUserModal() {
            var modal = document.getElementById('modal-edit-user');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    </script>

    <!-- Modal Atur Jabatan & Edit Pengguna -->
    <div id="modal-edit-user" class="fixed inset-0 z-50 hidden bg-slate-950/70 backdrop-blur-xs items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden my-8">
            <div class="p-4 sm:p-5 bg-gradient-to-r from-[#0F172A] via-slate-900 to-purple-950 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/20 border border-purple-400/40 text-purple-300 flex items-center justify-center font-bold text-sm">
                        ✏️
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold">Atur Jabatan & Data Pengguna</h3>
                        <p class="text-[11px] text-slate-300">Penetapan struktur organisasi kedinasan & hak akses</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditUserModal()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-sm cursor-pointer">✕</button>
            </div>

            <form id="form-edit-user" method="POST" action="" class="p-5 space-y-3.5">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" id="edit-user-name" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Username *</label>
                        <input type="text" name="username" id="edit-user-username" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Email *</label>
                        <input type="email" name="email" id="edit-user-email" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">NIP / NRP</label>
                        <input type="text" name="employee_id" id="edit-user-employee-id" placeholder="NRP-8809..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    
                    <!-- Jabatan Kedinasan (Highlight) -->
                    <div class="sm:col-span-2 p-3 rounded-2xl bg-purple-50/70 border border-purple-200">
                        <label class="block font-bold uppercase text-purple-900 mb-1 flex items-center justify-between">
                            <span>🏷️ Jabatan Kedinasan *</span>
                            <span class="text-[10px] text-purple-600 font-normal">Pilih rekomendasi atau ketik bebas</span>
                        </label>
                        <input type="text" name="job_title" id="edit-user-job-title" list="jabatan-presets" required placeholder="Pilih atau ketik jabatan..."
                               class="w-full px-3.5 py-2 rounded-xl border border-purple-300 bg-white font-semibold text-slate-800 text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Divisi / Satuan</label>
                        <input type="text" name="department" id="edit-user-department" placeholder="Detasemen Gegana..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Nomor HP / WhatsApp</label>
                        <input type="text" name="phone" id="edit-user-phone" placeholder="0812-..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold uppercase text-slate-600 mb-1">Peran Akses Sistem (Role) *</label>
                        <select name="role" id="edit-user-role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <option value="staff">Petugas / Staff (Operator Gudang)</option>
                            <option value="admin">Admin Logistik (Pengelola Gudang)</option>
                            @if ($user->isSuperAdmin())
                                <option value="super_admin">👑 Super Admin (Otoritas Tertinggi)</option>
                            @endif
                        </select>
                    </div>

                    <div class="sm:col-span-2 pt-2 border-t border-slate-200">
                        <label class="block font-semibold uppercase text-slate-600 mb-0.5">Ubah Password Pengguna (Opsional)</label>
                        <p class="text-[11px] text-slate-400 mb-1.5">Kosongkan jika tidak ingin mengubah password akun.</p>
                        <input type="password" name="password" placeholder="Masukkan password baru (minimal 6 karakter)" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none text-xs">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-1.5 cursor-pointer">
                        <span>Simpan Perubahan Jabatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </script>
@endsection
