@extends('layouts.app')

@section('title', 'Mode Pembuat & Studio Cetak Barcode')
@section('page_title', 'Mode Pembuat & Studio Cetak Barcode')
@section('page_subtitle', 'Buat kode barcode untuk barang baru maupun cetak label stiker barcode siap tempel')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Panel (5 Cols): Generator & Sticker Configuration -->
        <div class="no-print lg:col-span-5 space-y-6">
            <!-- Card 1: Buat Barang Sekaligus Barcode Baru -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 bg-slate-900 text-white">
                    <h3 class="text-sm sm:text-base font-bold">1. Buat Barcode untuk Barang Baru</h3>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Otomatis menghasilkan Barcode unik & menyimpan barang ke database atas nama <strong>{{ auth()->user()->name }}</strong>
                    </p>
                </div>

                <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-5 space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Kode Barang</label>
                            <input type="text" name="item_code" value="{{ $nextItemCode }}" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono-code text-xs bg-slate-50">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-semibold uppercase text-slate-600">Kode Barcode</label>
                                <button type="button" onclick="generateFreshStudioBarcode()" class="text-[11px] font-semibold text-emerald-600 hover:underline">
                                    Buat Kode Baru
                                </button>
                            </div>
                            <input type="text" id="studio_new_barcode" name="barcode" value="{{ $nextBarcode }}" required
                                   oninput="renderSingleStudioPreview()"
                                   class="w-full px-3 py-2 rounded-xl border border-emerald-500 font-mono-code text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Nama Barang *</label>
                        <input type="text" id="studio_new_name" name="name" required
                               oninput="renderSingleStudioPreview()"
                               placeholder="Ketik nama barang yang akan diberi barcode..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Upload Foto Barang (Opsional)</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-xs text-slate-600 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer border border-slate-200 rounded-xl p-1.5">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Kategori</label>
                            <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                <option value="">-- Umum --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Tipe Barang</label>
                            <select name="item_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                <option value="asset">Aset / Inventaris</option>
                                <option value="consumable">Habis Pakai (Stok)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Stok Awal</label>
                            <input type="number" name="stock" value="5" min="0" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Satuan</label>
                            <input type="text" name="unit" value="Unit" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Lokasi Rak</label>
                            <input type="text" id="studio_new_loc" name="storage_location" value="Rak A-01"
                                   oninput="renderSingleStudioPreview()"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>

                    <input type="hidden" name="min_stock" value="2">
                    <input type="hidden" name="condition_status" value="Baik">

                    <!-- Live Single Sticker Preview -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-dashed border-slate-300 text-center">
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Pratinjau Stiker Barcode Baru</div>
                        <div class="keep-light inline-block bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-2xs">
                            <div id="preview_new_title" class="text-xs font-bold text-slate-900">NAMA BARANG BARU</div>
                            <svg id="preview_new_svg" class="mx-auto my-1"></svg>
                            <div id="preview_new_meta" class="text-[10px] text-slate-500 font-mono-code">{{ $nextItemCode }} • Rak A-01</div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-xs transition-all">
                        Simpan Barang & Daftarkan Barcode ke Database
                    </button>
                </form>
            </div>

            <!-- Card 2: Pengaturan Cetak Label Masal -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">2. Pilih Barang untuk Cetak Stiker Barcode</h3>
                        <p class="text-xs text-slate-500">Centang sendiri barang mana yang akan di-print barcodenya</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="toggleAllStickers(true)" class="px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-[11px] font-semibold text-slate-700">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllStickers(false)" class="px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-[11px] font-semibold text-slate-500">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Ukuran Stiker</label>
                        <select id="sticker_size" onchange="rebuildPrintSheet()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="medium" selected>Sedang (3 Kolom / Standar)</option>
                            <option value="small">Kecil (4 Kolom / Padat)</option>
                            <option value="large">Besar (2 Kolom / Box Gudang)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Jumlah Default / Barang</label>
                        <input type="number" id="default_copies" value="3" min="1" max="50" onchange="applyDefaultCopies(this.value)"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-xl">
                    @foreach ($items as $item)
                        @php
                            $isChecked = $selectedId ? ((int) $selectedId === (int) $item->id) : false;
                        @endphp
                        <div class="p-2.5 flex items-center justify-between gap-2 hover:bg-slate-50">
                            <label class="flex items-center gap-2.5 cursor-pointer flex-1 min-w-0">
                                <input type="checkbox"
                                       class="item-sticker-check rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                       data-id="{{ $item->id }}"
                                       data-name="{{ $item->name }}"
                                       data-code="{{ $item->item_code }}"
                                       data-barcode="{{ $item->barcode }}"
                                       data-location="{{ $item->storage_location ?: 'Gudang Utama' }}"
                                       data-creator="{{ $item->creator?->name ?? 'Admin' }}"
                                       onchange="rebuildPrintSheet()"
                                       {{ $isChecked ? 'checked' : '' }}>
                                @if ($item->photo_url)
                                    <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ $item->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono-code">{{ $item->barcode }} • {{ $item->item_code }}</div>
                                </div>
                            </label>
                            <div class="flex items-center gap-1 shrink-0">
                                <span class="text-[11px] text-slate-400">Qty:</span>
                                <input type="number"
                                       id="copies_{{ $item->id }}"
                                       value="3"
                                       min="1"
                                       max="60"
                                       onchange="rebuildPrintSheet()"
                                       class="sticker-copies-input w-14 px-2 py-1 rounded-lg border border-slate-300 text-xs text-center">
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="window.print()"
                        class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Lembar Stiker Barcode Sekarang</span>
                </button>
            </div>
        </div>

        <!-- Right Panel (7 Cols): Printable Barcode Sheet -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6">
                <div class="no-print pb-4 mb-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">Lembar Siap Cetak Label Barcode</h3>
                        <p class="text-xs text-slate-500">Gunakan kertas label stiker atau kertas A4 biasa lalu tempelkan pada fisik barang</p>
                    </div>
                    <span id="total-stickers-badge" class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                        0 Stiker
                    </span>
                </div>

                <div id="printable-sticker-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <!-- Rendered dynamically via JS -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function renderSingleStudioPreview() {
        var barcode = document.getElementById('studio_new_barcode').value.trim();
        var name = document.getElementById('studio_new_name').value.trim() || 'NAMA BARANG BARU';
        var loc = document.getElementById('studio_new_loc').value.trim() || 'Gudang Utama';
        document.getElementById('preview_new_title').textContent = name;
        document.getElementById('preview_new_meta').textContent = '{{ $nextItemCode }} • ' + loc;

        var svg = document.getElementById('preview_new_svg');
        if (barcode && typeof JsBarcode !== 'undefined') {
            try {
                JsBarcode(svg, barcode, {
                    format: 'CODE128',
                    lineColor: '#0f172a',
                    width: 1.5,
                    height: 36,
                    displayValue: true,
                    fontSize: 11,
                    margin: 2
                });
            } catch (e) {}
        }
    }

    function generateFreshStudioBarcode() {
        var randomCode = '899' + Math.floor(100000000 + Math.random() * 900000000);
        document.getElementById('studio_new_barcode').value = randomCode;
        renderSingleStudioPreview();
    }

    function toggleAllStickers(checked) {
        document.querySelectorAll('.item-sticker-check').forEach(function (chk) {
            chk.checked = checked;
        });
        rebuildPrintSheet();
    }

    function applyDefaultCopies(val) {
        var inputs = document.querySelectorAll('.sticker-copies-input');
        inputs.forEach(function (inp) {
            inp.value = val;
        });
        rebuildPrintSheet();
    }

    function rebuildPrintSheet() {
        var grid = document.getElementById('printable-sticker-grid');
        var size = document.getElementById('sticker_size').value;
        var checks = document.querySelectorAll('.item-sticker-check:checked');

        if (size === 'small') {
            grid.className = 'grid grid-cols-2 sm:grid-cols-4 gap-2.5';
        } else if (size === 'large') {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4';
        } else {
            grid.className = 'grid grid-cols-2 sm:grid-cols-3 gap-3';
        }

        grid.innerHTML = '';
        var totalStickers = 0;

        checks.forEach(function (chk) {
            var id = chk.getAttribute('data-id');
            var name = chk.getAttribute('data-name');
            var code = chk.getAttribute('data-code');
            var barcode = chk.getAttribute('data-barcode');
            var location = chk.getAttribute('data-location');
            var copiesInput = document.getElementById('copies_' + id);
            var copies = parseInt(copiesInput ? copiesInput.value : '1', 10) || 1;

            for (var i = 0; i < copies; i++) {
                totalStickers++;
                var card = document.createElement('div');
                card.className = 'keep-light p-3.5 rounded-2xl border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-white flex flex-col items-center justify-between text-center break-inside-avoid shadow-2xs transition-all';

                var header = document.createElement('div');
                header.className = 'w-full flex items-center justify-between text-[9px] font-extrabold uppercase tracking-wider text-slate-600 bg-slate-100 rounded-md px-2 py-1 mb-1.5';
                header.innerHTML = '<span class="text-emerald-700">LOGISCAN PRO</span><span class="font-mono-code">' + code + '</span>';

                var title = document.createElement('div');
                title.className = 'text-xs font-extrabold text-slate-900 leading-snug line-clamp-2';
                title.textContent = name;

                var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('class', 'my-1 max-w-full');

                var footer = document.createElement('div');
                footer.className = 'w-full text-[10px] text-slate-600 border-t border-dashed border-slate-200 pt-1.5 mt-1 font-semibold flex items-center justify-between';
                footer.innerHTML = '<span>Rak: ' + location + '</span><span class="text-slate-400">VERIFIED</span>';

                card.appendChild(header);
                card.appendChild(title);
                card.appendChild(svg);
                card.appendChild(footer);
                grid.appendChild(card);

                if (typeof JsBarcode !== 'undefined') {
                    try {
                        JsBarcode(svg, barcode, {
                            format: 'CODE128',
                            lineColor: '#0f172a',
                            width: size === 'small' ? 1.15 : (size === 'large' ? 1.7 : 1.35),
                            height: size === 'small' ? 28 : (size === 'large' ? 46 : 34),
                            displayValue: true,
                            fontSize: 11,
                            margin: 2
                        });
                    } catch (e) {}
                }
            }
        });

        if (totalStickers === 0) {
            grid.className = 'py-14 text-center border-2 border-dashed border-slate-200 rounded-2xl';
            grid.innerHTML = '<div class="max-w-sm mx-auto px-4"><div class="text-sm font-bold text-slate-700">Belum Ada Barang yang Dipilih</div><p class="text-xs text-slate-500 mt-1">Silakan centang barang pada daftar <strong>"2. Pilih Barang untuk Cetak Stiker Barcode"</strong> di sebelah kiri untuk menampilkan stiker yang ingin di-print.</p></div>';
        }

        document.getElementById('total-stickers-badge').textContent = totalStickers + ' Stiker Siap Cetak';
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderSingleStudioPreview();
        rebuildPrintSheet();
    });
</script>
@endpush
