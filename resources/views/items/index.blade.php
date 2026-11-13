@extends('layouts.app')

@section('title', 'Data Barang & Penyimpanan')
@section('page_title', 'Master Barang & Database Penyimpanan')
@section('page_subtitle', 'Kelola daftar barang ber-barcode, stok gudang, serta pantau siapa user yang menambahkan barang')

@section('content')
    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openModal('modal-add-item')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs sm:text-sm font-semibold shadow-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Barang & Buat Barcode</span>
            </button>

            <a href="{{ route('items.barcode-studio') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h2v12H4V6zm4 0h1v12H8V6zm3 0h3v12h-3V6zm5 0h1v12h-1V6zm3 0h1v12h-1V6z"/>
                </svg>
                <span>Mode Studio Cetak Barcode</span>
            </a>

            <button type="button" onclick="openModal('modal-add-category')"
                    class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-medium transition-all">
                <span>+ Kategori Baru</span>
            </button>
        </div>

        <div class="text-xs text-slate-500">
            Petugas Login: <strong class="text-slate-800">{{ auth()->user()->name }}</strong> (Otomatis tercatat saat tambah barang)
        </div>
    </div>

    <!-- Backend Search & Filter Bar -->
    <form action="{{ route('items.index') }}" method="GET" class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Cari Nama / Kode / Barcode</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Ketik nama barang, kode, atau scan barcode..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Status Barang</label>
                <select name="availability_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="sedia" {{ request('availability_status') === 'sedia' ? 'selected' : '' }}>Sedia</option>
                    <option value="sedang_keluar" {{ request('availability_status') === 'sedang_keluar' ? 'selected' : '' }}>Sedang Keluar</option>
                    <option value="sedang_dipakai" {{ request('availability_status') === 'sedang_dipakai' ? 'selected' : '' }}>Sedang Dipakai</option>
                    <option value="sedang_dipinjam" {{ request('availability_status') === 'sedang_dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Ditambahkan Oleh (User)</label>
                <select name="created_by" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Petugas</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ (string) request('created_by') === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold transition-all">
                    Filter Data
                </button>
                <a href="{{ route('items.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs sm:text-sm font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Master Items Table (Desktop) & Smart Mobile Cards (Mobile) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- TAMPILAN TABEL KHUSUS KOMPUTER / DESKTOP (MD+) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse table-fluid">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Kode & Nama Barang</th>
                        <th class="py-3.5 px-4">Barcode Barang</th>
                        <th class="py-3.5 px-4">Tipe & Lokasi Penyimpanan</th>
                        <th class="py-3.5 px-4">Stok Tersedia</th>
                        <th class="py-3.5 px-4">User Pembuat Barang</th>
                        <th class="py-3.5 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 text-sm">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/90 transition-colors">
                            <td class="py-3.5 px-4 align-top">
                                <div class="flex items-start gap-3">
                                    @if ($item->photo_url)
                                        <button type="button"
                                                onclick="openPhotoLightbox('{{ $item->photo_url }}', '{{ addslashes($item->name) }}')"
                                                title="Klik untuk perbesar foto barang"
                                                class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-2xs group relative">
                                            <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                        </button>
                                    @else
                                        <div class="w-12 h-12 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 flex flex-col items-center justify-center shrink-0 text-[9px] font-semibold">
                                            <svg class="w-4 h-4 mb-0.5 opacity-70" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>NO IMG</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-mono-code text-xs font-semibold text-emerald-700">{{ $item->item_code }}</div>
                                        <div class="font-bold text-slate-900 mt-0.5">{{ $item->name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            {{ $item->category?->name ?? 'Tanpa Kategori' }}
                                            @if ($item->brand)
                                                • Merek: {{ $item->brand }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="keep-light inline-block bg-white px-1.5 py-0.5 rounded-md border border-slate-200">
                                    <svg class="js-barcode max-w-[108px] h-auto block" data-barcode="{{ $item->barcode }}" data-height="18" data-width="0.9" data-fontsize="8" data-margin="1"></svg>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @php $avStatus = $item->availability_status ?? 'sedia'; @endphp
                                    @if ($avStatus === 'sedia')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Sedia
                                        </span>
                                    @elseif ($avStatus === 'sedang_keluar')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Sedang Keluar
                                        </span>
                                    @elseif ($avStatus === 'sedang_dipakai')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>Sedang Dipakai
                                        </span>
                                    @elseif ($avStatus === 'sedang_dipinjam')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Sedang Dipinjam
                                        </span>
                                    @endif

                                    @if ($item->item_type === 'asset')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            Aset
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            Stok
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-700 font-medium mt-1.5">
                                    Lokasi: {{ $item->storage_location ?: 'Gudang Utama' }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Kondisi: <strong>{{ $item->condition_status }}</strong>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="text-base font-bold {{ $item->isLowStock() ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ number_format($item->stock) }} <span class="text-xs font-normal text-slate-500">{{ $item->unit }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400">Min. Stok: {{ $item->min_stock }} {{ $item->unit }}</div>
                                @if ($item->isLowStock())
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                        STOK MENIPIS
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="text-xs font-semibold text-slate-900">
                                    {{ $item->creator?->name ?? 'Admin Sistem' }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $item->creator?->job_title ?: 'Petugas Logistik' }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $item->created_at?->format('d/m/Y H:i') }} WIB
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-top text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('scan.index', ['barcode' => $item->barcode, 'mode' => 'in']) }}"
                                       title="Scan Masuk/Keluar Barang Ini"
                                       class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                                        Scan
                                    </a>
                                    <a href="{{ route('items.barcode-studio', ['item_id' => $item->id]) }}"
                                       title="Cetak Stiker Barcode"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                        Cetak Barcode
                                    </a>
                                    <button type="button"
                                            data-item="{{ json_encode($item) }}"
                                            onclick="openEditItemModal(this)"
                                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-medium">
                                        Edit
                                    </button>
                                    <form action="{{ route('items.destroy', $item) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus barang ini dari penyimpanan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-slate-500">
                                Tidak ada data barang yang ditemukan. Klik tombol <strong>"Tambah Barang & Buat Barcode"</strong> untuk mulai menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAMPILAN KARTU CERDAS KHUSUS PONSEL / SMARTPHONE (MD HIDDEN) -->
        <div class="md:hidden divide-y divide-slate-100 p-3 space-y-3">
            @forelse ($items as $item)
                <div class="card-elevated bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
                    <!-- Atas: Foto, Nama, Kode SKU, Kategori & Status -->
                    <div class="flex items-start gap-3">
                        @if ($item->photo_url)
                            <button type="button"
                                    onclick="openPhotoLightbox('{{ $item->photo_url }}', '{{ addslashes($item->name) }}')"
                                    title="Klik untuk perbesar foto barang"
                                    class="w-14 h-14 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-2xs group relative">
                                <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            </button>
                        @else
                            <div class="w-14 h-14 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 flex flex-col items-center justify-center shrink-0 text-[10px] font-semibold">
                                <svg class="w-5 h-5 mb-0.5 opacity-60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>NO IMG</span>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1.5">
                                <span class="font-mono-code text-[11px] font-extrabold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/80">{{ $item->item_code }}</span>
                                @php $avStatus = $item->availability_status ?? 'sedia'; @endphp
                                @if ($avStatus === 'sedia')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Sedia
                                    </span>
                                @elseif ($avStatus === 'sedang_keluar')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Sedang Keluar
                                    </span>
                                @elseif ($avStatus === 'sedang_dipakai')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>Dipakai
                                    </span>
                                @elseif ($avStatus === 'sedang_dipinjam')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Dipinjam
                                    </span>
                                @endif
                            </div>

                            <h4 class="font-bold text-slate-900 text-sm mt-1 leading-snug">{{ $item->name }}</h4>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                {{ $item->category?->name ?? 'Tanpa Kategori' }}
                                @if ($item->brand) • {{ $item->brand }} @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tengah: Barcode Mini + Info Stok Fisik & Lokasi Rak -->
                    <div class="p-2.5 rounded-xl bg-slate-50/90 border border-slate-200/80 flex items-center justify-between gap-2.5">
                        <div class="keep-light bg-white px-2 py-1 rounded-lg border border-slate-200 shrink-0 shadow-2xs">
                            <svg class="js-barcode max-w-[105px] h-auto block" data-barcode="{{ $item->barcode }}" data-height="18" data-width="0.9" data-fontsize="8" data-margin="1"></svg>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-slate-400 uppercase font-semibold leading-none">Stok Fisik:</div>
                            <div class="text-lg font-extrabold {{ $item->isLowStock() ? 'text-rose-600' : 'text-slate-900' }} leading-tight mt-0.5">
                                {{ number_format($item->stock) }} <span class="text-xs font-normal text-slate-500">{{ $item->unit }}</span>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                Rak: <strong class="text-slate-700">{{ $item->storage_location ?: 'Gudang Utama' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Bawah: Tombol Aksi Sentuh Ergonomis Jempol -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-100">
                        <a href="{{ route('scan.index', ['barcode' => $item->barcode, 'mode' => 'in']) }}"
                           class="py-2.5 px-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>Scan</span>
                        </a>
                        <a href="{{ route('items.barcode-studio', ['item_id' => $item->id]) }}"
                           class="py-2.5 px-2 rounded-xl bg-slate-800 text-slate-100 text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs active:scale-95">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Stiker</span>
                        </a>
                        <button type="button"
                                onclick="openEditModal({{ json_encode($item) }})"
                                class="py-2.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold flex items-center justify-center gap-1.5 active:scale-95 border border-slate-200">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Ubah</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    Belum ada barang di database.
                </div>
            @endforelse
        </div>

        @if ($items->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL TAMBAH BARANG & PEMBUAT BARCODE -->
    <div id="modal-add-item" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('modal-add-item')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold">Tambah Barang Baru & Generate Barcode</h3>
                        <p class="text-xs text-slate-300">Pembuat barang tercatat otomatis: <strong>{{ auth()->user()->name }}</strong></p>
                    </div>
                    <button type="button" onclick="closeModal('modal-add-item')" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kode Barang (Otomatis)</label>
                            <input type="text" id="add_item_code" name="item_code" value="{{ $nextItemCode }}" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono-code text-sm bg-slate-50">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold uppercase text-slate-600">Kode Barcode Barang</label>
                                <button type="button" onclick="regenerateBarcodeField()" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">
                                    Acak / Buat Baru
                                </button>
                            </div>
                            <input type="text" id="add_barcode" name="barcode" value="{{ $nextBarcode }}" required
                                   oninput="updateLiveBarcodePreview(this.value)"
                                   class="w-full px-3 py-2 rounded-xl border border-emerald-400 font-mono-code text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Live Barcode Preview Box -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-slate-600">
                            <strong class="text-slate-900 block">Pratinjau Grafik Barcode (Code-128):</strong>
                            Barcode ini siap dicetak dan langsung bisa di-scan untuk barang masuk maupun keluar.
                        </div>
                        <div class="bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                            <svg id="live-barcode-preview"></svg>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Lengkap Barang *</label>
                            <input type="text" name="name" required placeholder="Contoh: Handy Talky Motorola CP1660 / Kabel LAN Cat6"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Upload Foto Barang (Opsional — JPG/PNG/WEBP, Maks 4MB)</label>
                            <div class="flex items-center gap-3 p-3 rounded-xl border border-dashed border-slate-300 bg-slate-50">
                                <div id="add_photo_preview_box" class="w-14 h-14 rounded-xl border border-slate-200 bg-white flex items-center justify-center overflow-hidden shrink-0 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                                           onchange="previewUploadedPhoto(this, 'add_photo_preview_box')"
                                           class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                    <p class="text-[11px] text-slate-400 mt-1">Foto barang akan otomatis muncul di tabel Barang, Terminal Scan, Laporan, dan BAST/BAP.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kategori Barang</label>
                            <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Tipe Pengelolaan *</label>
                            <select name="item_type" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="asset">Barang Aset / Inventaris (Bisa BAST & BAP)</option>
                                <option value="consumable">Barang Habis Pakai (Stok Reguler)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Merek / Tipe</label>
                            <input type="text" name="brand" placeholder="Contoh: Motorola / Lenovo / 3M"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Satuan Hitung *</label>
                            <select name="unit" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="Unit">Unit</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Set">Set</option>
                                <option value="Box">Box</option>
                                <option value="Roll">Roll</option>
                                <option value="Pack">Pack</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Stok Awal Masuk Gudang *</label>
                            <input type="number" name="stock" value="1" min="0" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Batas Minimum Peringatan Stok *</label>
                            <input type="number" name="min_stock" value="3" min="0" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Lokasi Rak / Gudang Penyimpanan</label>
                            <input type="text" name="storage_location" value="Rak A-01 / Gudang Utama"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kondisi Barang Saat Ini</label>
                            <select name="condition_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="Baik">Baik / Baru</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Perlu Pengecekan">Perlu Pengecekan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Status Ketersediaan Barang *</label>
                            <select name="availability_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="sedia">Sedia (Di Gudang)</option>
                                <option value="sedang_keluar">Sedang Keluar</option>
                                <option value="sedang_dipakai">Sedang Dipakai</option>
                                <option value="sedang_dipinjam">Sedang Dipinjam</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Keterangan / Spesifikasi</label>
                            <textarea name="description" rows="2" placeholder="Catatan nomor seri, kelengkapan, atau detail penyimpanan..."
                                      class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" onclick="closeModal('modal-add-item')"
                                class="px-4 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-sm">
                            Simpan Barang & Buat Barcode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT BARANG -->
    <div id="modal-edit-item" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('modal-edit-item')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold">Edit Informasi Barang & Barcode</h3>
                    <button type="button" onclick="closeModal('modal-edit-item')" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="form-edit-item" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kode Barang</label>
                            <input type="text" id="edit_item_code" name="item_code" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono-code text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kode Barcode</label>
                            <input type="text" id="edit_barcode" name="barcode" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono-code text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Barang</label>
                            <input type="text" id="edit_name" name="name" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Foto Barang (Ganti / Upload Foto Baru)</label>
                            <div class="flex items-center gap-3 p-3 rounded-xl border border-dashed border-slate-300 bg-slate-50">
                                <div id="edit_photo_preview_box" class="w-14 h-14 rounded-xl border border-slate-200 bg-white flex items-center justify-center overflow-hidden shrink-0 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                                           onchange="previewUploadedPhoto(this, 'edit_photo_preview_box')"
                                           class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                    <label class="inline-flex items-center gap-1.5 text-[11px] text-rose-600 font-medium mt-1.5 cursor-pointer">
                                        <input type="checkbox" id="edit_remove_photo" name="remove_photo" value="1" class="rounded border-slate-300 text-rose-600">
                                        <span>Hapus foto barang saat ini</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kategori</label>
                            <select id="edit_category_id" name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="">-- Tanpa Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Tipe Pengelolaan</label>
                            <select id="edit_item_type" name="item_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="asset">Barang Aset / Inventaris</option>
                                <option value="consumable">Barang Habis Pakai (Stok)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Merek</label>
                            <input type="text" id="edit_brand" name="brand" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Satuan</label>
                            <input type="text" id="edit_unit" name="unit" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Batas Minimum Stok</label>
                            <input type="number" id="edit_min_stock" name="min_stock" min="0" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Lokasi Penyimpanan</label>
                            <input type="text" id="edit_storage_location" name="storage_location" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kondisi</label>
                            <select id="edit_condition_status" name="condition_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Perlu Pengecekan">Perlu Pengecekan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Status Barang</label>
                            <select id="edit_availability_status" name="availability_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                                <option value="sedia">Sedia</option>
                                <option value="sedang_keluar">Sedang Keluar</option>
                                <option value="sedang_dipakai">Sedang Dipakai</option>
                                <option value="sedang_dipinjam">Sedang Dipinjam</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Deskripsi</label>
                            <textarea id="edit_description" name="description" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" onclick="closeModal('modal-edit-item')" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs font-semibold">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH KATEGORI -->
    <div id="modal-add-category" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal('modal-add-category')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Tambah Kategori Barang</h3>
                    <button type="button" onclick="closeModal('modal-add-category')" class="text-slate-400 hover:text-white">&times;</button>
                </div>
                <form action="{{ route('categories.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kode Kategori *</label>
                        <input type="text" name="code" placeholder="Contoh: KAT-LOG" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm font-mono-code">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Kategori *</label>
                        <input type="text" name="name" placeholder="Contoh: Peralatan Gudang" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('modal-add-category')" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.remove('hidden');
            if (id === 'modal-add-item') {
                updateLiveBarcodePreview(document.getElementById('add_barcode').value);
            }
        }
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }

    function updateLiveBarcodePreview(val) {
        var svg = document.getElementById('live-barcode-preview');
        if (!svg || !val || typeof JsBarcode === 'undefined') return;
        try {
            JsBarcode(svg, val.trim(), {
                format: 'CODE128',
                lineColor: '#0f172a',
                width: 1.5,
                height: 36,
                displayValue: true,
                fontSize: 12,
                margin: 4
            });
        } catch (e) {}
    }

    function regenerateBarcodeField() {
        fetch('{{ route('items.generate-code') }}')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.barcode) {
                    var input = document.getElementById('add_barcode');
                    input.value = data.barcode;
                    updateLiveBarcodePreview(data.barcode);
                }
            })
            .catch(function () {
                var randomCode = '899' + Math.floor(100000000 + Math.random() * 900000000);
                document.getElementById('add_barcode').value = randomCode;
                updateLiveBarcodePreview(randomCode);
            });
    }

    function previewUploadedPhoto(input, boxId) {
        var box = document.getElementById(boxId);
        if (!box) return;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                box.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openEditItemModal(btn) {
        var item = JSON.parse(btn.getAttribute('data-item'));
        var form = document.getElementById('form-edit-item');
        form.action = '/items/' + item.id;
        document.getElementById('edit_item_code').value = item.item_code || '';
        document.getElementById('edit_barcode').value = item.barcode || '';
        document.getElementById('edit_name').value = item.name || '';
        document.getElementById('edit_category_id').value = item.category_id || '';
        document.getElementById('edit_item_type').value = item.item_type || 'asset';
        document.getElementById('edit_brand').value = item.brand || '';
        document.getElementById('edit_unit').value = item.unit || 'Unit';
        document.getElementById('edit_min_stock').value = item.min_stock || 0;
        document.getElementById('edit_storage_location').value = item.storage_location || '';
        document.getElementById('edit_condition_status').value = item.condition_status || 'Baik';
        var editAv = document.getElementById('edit_availability_status');
        if (editAv) editAv.value = item.availability_status || 'sedia';
        document.getElementById('edit_description').value = item.description || '';
        var rmCheck = document.getElementById('edit_remove_photo');
        if (rmCheck) rmCheck.checked = false;
        var editBox = document.getElementById('edit_photo_preview_box');
        if (editBox) {
            if (item.photo_url) {
                editBox.innerHTML = '<img src="' + item.photo_url + '" class="w-full h-full object-cover">';
            } else {
                editBox.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
            }
        }
        openModal('modal-edit-item');
    }
</script>
@endpush
