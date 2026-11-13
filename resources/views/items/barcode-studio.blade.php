@extends('layouts.app')

@section('title', 'Mode Pembuat & Studio Cetak Barcode')
@section('page_title', 'Mode Pembuat & Studio Cetak Barcode')
@section('page_subtitle', 'Buat kode barcode barang baru dan cetak label stiker siap tempel sesuai format Blueprint GL-06 & GL-22')

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 7.5mm 8.25mm;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .no-print, header, footer, nav, aside {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
        }
        .lg\:pl-\[272px\] {
            padding-left: 0 !important;
        }

        /* Format 1: BLUEPRINT GL-22 (64.5 x 47 mm — 3 Kolom x 6 Baris = 18 Label / A4) */
        .print-format-gl22 {
            display: grid !important;
            grid-template-columns: repeat(3, 64.5mm) !important;
            grid-auto-rows: 47mm !important;
            width: 193.5mm !important;
            margin: 0 auto !important;
            gap: 0 !important;
            padding: 0 !important;
        }
        .sticker-card-gl22 {
            width: 64.5mm !important;
            height: 47mm !important;
            max-width: 64.5mm !important;
            max-height: 47mm !important;
            box-sizing: border-box !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            padding: 2.2mm 2.8mm !important;
            border: 0.5px dashed #cbd5e1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: center !important;
            text-align: center !important;
            overflow: hidden !important;
            background: #ffffff !important;
        }

        /* Format 2: BLUEPRINT GL-06 (96 x 93.1 mm — 2 Kolom x 3 Baris = 6 Label / A4) */
        .print-format-gl06 {
            display: grid !important;
            grid-template-columns: repeat(2, 96mm) !important;
            grid-auto-rows: 93.1mm !important;
            width: 192mm !important;
            margin: 0 auto !important;
            gap: 0 !important;
            padding: 0 !important;
        }
        .sticker-card-gl06 {
            width: 96mm !important;
            height: 93.1mm !important;
            max-width: 96mm !important;
            max-height: 93.1mm !important;
            box-sizing: border-box !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            padding: 5mm 6mm !important;
            border: 0.5px dashed #cbd5e1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: center !important;
            text-align: center !important;
            overflow: hidden !important;
            background: #ffffff !important;
        }

        /* Format 3: Thermal Roll (50 x 30 mm) */
        .print-format-thermal {
            display: block !important;
            width: 50mm !important;
            margin: 0 auto !important;
            padding: 0 !important;
        }
        .sticker-card-thermal {
            width: 50mm !important;
            height: 30mm !important;
            max-width: 50mm !important;
            max-height: 30mm !important;
            box-sizing: border-box !important;
            page-break-after: always !important;
            break-after: page !important;
            padding: 1.5mm !important;
            border: none !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: center !important;
            text-align: center !important;
            overflow: hidden !important;
            background: #ffffff !important;
        }

        /* Format 4: Kertas A4 Standar Fleksibel */
        .print-format-free {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 3mm !important;
            width: 100% !important;
        }
        .sticker-card-free {
            box-sizing: border-box !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            padding: 3mm !important;
            border: 1px dashed #cbd5e1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: center !important;
            text-align: center !important;
            overflow: hidden !important;
            background: #ffffff !important;
        }
    }
</style>
@endpush

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

            <!-- Card 2: Pengaturan Cetak Label Masal Sesuai Format Kertas Stiker Blueprint -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">2. Pilih Format Kertas & Barang untuk Dicetak</h3>
                        <p class="text-xs text-slate-500">Pilih format stiker Blueprint (GL-22 / GL-06) lalu tentukan barang yang ingin dicetak</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="toggleAllStickers(true)" class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-[11px] font-bold text-slate-700 transition-colors">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllStickers(false)" class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-[11px] font-bold text-slate-500 transition-colors">
                            Reset (0)
                        </button>
                    </div>
                </div>

                <!-- Pilihan Format Kertas Label Stiker -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700">
                        Format Kertas Stiker Label Barcode *
                    </label>
                    <select id="sticker_size" onchange="rebuildPrintSheet()"
                            class="w-full px-3.5 py-2.5 rounded-xl border-2 border-emerald-500/80 font-bold text-xs sm:text-sm text-slate-900 bg-white focus:outline-none focus:border-emerald-600">
                        <option value="blueprint-gl22" selected>
                            🏷️ BLUEPRINT GL-22 (64.5 x 47 mm) — 18 Label / Lembar A4 (3 Kolom x 6 Baris) [Standar Barang]
                        </option>
                        <option value="blueprint-gl06">
                            📦 BLUEPRINT GL-06 (96 x 93.1 mm) — 6 Label / Lembar A4 (2 Kolom x 3 Baris) [Kotak / Dus Gudang]
                        </option>
                        <option value="thermal-roll">
                            🧾 LABEL THERMAL ROLL (50 x 30 mm) — Printer Kasir / Thermal Satuan
                        </option>
                        <option value="a4-free">
                            📄 KERTAS HVS A4 BIASA (3 Kolom Fleksibel Gunting Manual)
                        </option>
                    </select>
                </div>

                <!-- Banner Informasi Kapasitas Format Kertas -->
                <div id="format-info-card" class="p-3.5 rounded-xl bg-slate-900 text-white border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span id="format-badge-title" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                            BLUEPRINT GL-22
                        </span>
                        <span id="format-sheet-calc" class="text-xs font-mono-code font-bold text-amber-300">
                            0 Stiker (0 Lembar A4)
                        </span>
                    </div>
                    <p id="format-desc" class="text-[11px] text-slate-300 leading-relaxed">
                        Ukuran 64.5 x 47 mm (18 label per lembar A4). Sangat pas ditempelkan pada bodi peralatan, handy talky, helm, laptop, dan unit logistik.
                    </p>
                    <div class="flex items-center gap-2 pt-1 border-t border-slate-800">
                        <button type="button" id="btn-quick-fill-sheet" onclick="quickFillFullSheet()"
                                class="text-[11px] font-bold text-cyan-400 hover:text-cyan-300 underline">
                            ⚡ Paskan Otomatis ke 1 Lembar Penuh A4 (18 Stiker)
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Jumlah Default / Barang</label>
                        <input type="number" id="default_copies" value="3" min="1" max="60" onchange="applyDefaultCopies(this.value)"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase text-slate-600 mb-1">Cari Barang Cepat</label>
                        <input type="text" id="search_sticker_item" oninput="filterStickerItemList(this.value)"
                               placeholder="Ketik nama / barcode..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div id="sticker-item-list" class="max-h-80 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-xl">
                    @foreach ($items as $item)
                        @php
                            $isChecked = $selectedId ? ((int) $selectedId === (int) $item->id) : false;
                        @endphp
                        <div class="sticker-item-row p-2.5 flex items-center justify-between gap-2 hover:bg-slate-50 transition-colors"
                             data-keyword="{{ strtolower($item->name . ' ' . $item->barcode . ' ' . $item->item_code) }}">
                            <label class="flex items-center gap-2.5 cursor-pointer flex-1 min-w-0">
                                <input type="checkbox"
                                       class="item-sticker-check rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4"
                                       data-id="{{ $item->id }}"
                                       data-name="{{ $item->name }}"
                                       data-code="{{ $item->item_code }}"
                                       data-barcode="{{ $item->barcode }}"
                                       data-location="{{ $item->storage_location ?: 'Gudang TOC' }}"
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
                                    <div class="text-[11px] text-slate-500 font-mono-code">
                                        <span class="text-emerald-700 font-bold">{{ $item->barcode }}</span> • {{ $item->item_code }}
                                    </div>
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
                                       class="sticker-copies-input w-13 px-2 py-1 rounded-lg border border-slate-300 text-xs text-center font-bold">
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="window.print()"
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20 active:scale-98 transition-all">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>CETAK LEMBAR STIKER BARCODE SEKARANG</span>
                </button>
            </div>
        </div>

        <!-- Right Panel (7 Cols): Printable Barcode Sheet -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6">
                <div class="no-print pb-4 mb-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Lembar Pratinjau Siap Cetak Label Barcode</h3>
                        </div>
                        <p id="preview-sheet-desc" class="text-xs text-slate-500 mt-0.5">
                            Format terpilih: <strong>BLUEPRINT GL-22 (64.5 x 47 mm)</strong> — 18 label per lembar A4
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="total-stickers-badge" class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                            0 Stiker Siap Cetak
                        </span>
                        <button type="button" onclick="window.print()"
                                class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs font-bold transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Print</span>
                        </button>
                    </div>
                </div>

                <!-- Wadah Cetak Dinamis (Mendukung Presisi BLUEPRINT GL-22, GL-06 & Thermal Roll) -->
                <div id="printable-sheet-wrapper" class="w-full">
                    <div id="printable-sticker-grid" class="w-full">
                        <!-- Rendered dynamically via JS -->
                    </div>
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
        var loc = document.getElementById('studio_new_loc').value.trim() || 'Gudang TOC';
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
        var num = parseInt(val, 10) || 1;
        var inputs = document.querySelectorAll('.sticker-copies-input');
        inputs.forEach(function (inp) {
            inp.value = num;
        });
        rebuildPrintSheet();
    }

    function filterStickerItemList(keyword) {
        var clean = (keyword || '').toLowerCase().trim();
        var rows = document.querySelectorAll('.sticker-item-row');
        rows.forEach(function (row) {
            var text = row.getAttribute('data-keyword') || '';
            if (!clean || text.indexOf(clean) !== -1) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    }

    function quickFillFullSheet() {
        var format = document.getElementById('sticker_size').value;
        var sheetCapacity = 18;
        if (format === 'blueprint-gl06') sheetCapacity = 6;
        else if (format === 'thermal-roll') sheetCapacity = 10;
        else if (format === 'a4-free') sheetCapacity = 15;

        var checks = Array.from(document.querySelectorAll('.item-sticker-check'));
        if (checks.length === 0) return;

        // Reset semua lalu centang barang pertama sebanyak kapasitas lembar
        checks.forEach(function (chk) { chk.checked = false; });
        checks[0].checked = true;

        var copiesInput = document.getElementById('copies_' + checks[0].getAttribute('data-id'));
        if (copiesInput) copiesInput.value = sheetCapacity;

        rebuildPrintSheet();
    }

    function rebuildPrintSheet() {
        var grid = document.getElementById('printable-sticker-grid');
        var format = document.getElementById('sticker_size').value;
        var checks = document.querySelectorAll('.item-sticker-check:checked');

        var badgeTitle = document.getElementById('format-badge-title');
        var sheetCalc = document.getElementById('format-sheet-calc');
        var formatDesc = document.getElementById('format-desc');
        var previewSheetDesc = document.getElementById('preview-sheet-desc');
        var quickBtn = document.getElementById('btn-quick-fill-sheet');

        var sheetCapacity = 18;
        var formatName = 'BLUEPRINT GL-22 (64.5 x 47 mm)';

        if (format === 'blueprint-gl06') {
            sheetCapacity = 6;
            formatName = 'BLUEPRINT GL-06 (96 x 93.1 mm)';
            grid.className = 'print-format-gl06 grid grid-cols-1 sm:grid-cols-2 gap-4';
            if (badgeTitle) badgeTitle.textContent = 'BLUEPRINT GL-06';
            if (formatDesc) formatDesc.textContent = 'Ukuran 96 x 93.1 mm (6 label per lembar A4 - 2 Kolom x 3 Baris). Ukuran besar kotak, sangat cocok untuk ditempel pada dus, box kontainer, pallet, atau rak penyimpanan.';
            if (previewSheetDesc) previewSheetDesc.innerHTML = 'Format terpilih: <strong>BLUEPRINT GL-06 (96 x 93.1 mm)</strong> — 6 label per lembar A4 (2 Kolom x 3 Baris)';
            if (quickBtn) quickBtn.textContent = '⚡ Paskan Otomatis ke 1 Lembar Penuh A4 (6 Stiker)';
        } else if (format === 'thermal-roll') {
            sheetCapacity = 1;
            formatName = 'LABEL THERMAL ROLL (50 x 30 mm)';
            grid.className = 'print-format-thermal grid grid-cols-2 sm:grid-cols-3 gap-3';
            if (badgeTitle) badgeTitle.textContent = 'THERMAL ROLL 50x30mm';
            if (formatDesc) formatDesc.textContent = 'Ukuran 50 x 30 mm untuk mesin cetak printer thermal roll satuan. Setiap stiker terpotong otomatis per halaman.';
            if (previewSheetDesc) previewSheetDesc.innerHTML = 'Format terpilih: <strong>Label Thermal Roll (50 x 30 mm)</strong> — Cetak Printer Thermal Roll';
            if (quickBtn) quickBtn.textContent = '⚡ Setel 10 Stiker Thermal Satuan';
        } else if (format === 'a4-free') {
            sheetCapacity = 15;
            formatName = 'KERTAS A4 FLEKSIBEL (3 Kolom)';
            grid.className = 'print-format-free grid grid-cols-2 sm:grid-cols-3 gap-3';
            if (badgeTitle) badgeTitle.textContent = 'KERTAS HVS A4 STANDAR';
            if (formatDesc) formatDesc.textContent = 'Format standar 3 kolom fleksibel untuk dicetak pada kertas HVS A4 biasa lalu dipotong manual dengan gunting/cutter.';
            if (previewSheetDesc) previewSheetDesc.innerHTML = 'Format terpilih: <strong>Kertas HVS A4 Fleksibel</strong> — 3 Kolom per Halaman';
            if (quickBtn) quickBtn.textContent = '⚡ Paskan ke 15 Stiker A4 Standar';
        } else {
            // Default: blueprint-gl22
            sheetCapacity = 18;
            formatName = 'BLUEPRINT GL-22 (64.5 x 47 mm)';
            grid.className = 'print-format-gl22 grid grid-cols-2 sm:grid-cols-3 gap-2.5';
            if (badgeTitle) badgeTitle.textContent = 'BLUEPRINT GL-22';
            if (formatDesc) formatDesc.textContent = 'Ukuran 64.5 x 47 mm (18 label per lembar A4 - 3 Kolom x 6 Baris). Ukuran stiker sedang proporsional, sangat pas untuk bodi barang inventaris, peralatan, dan kemasan.';
            if (previewSheetDesc) previewSheetDesc.innerHTML = 'Format terpilih: <strong>BLUEPRINT GL-22 (64.5 x 47 mm)</strong> — 18 label per lembar A4 (3 Kolom x 6 Baris)';
            if (quickBtn) quickBtn.textContent = '⚡ Paskan Otomatis ke 1 Lembar Penuh A4 (18 Stiker)';
        }

        grid.innerHTML = '';
        var totalStickers = 0;

        checks.forEach(function (chk) {
            var id = chk.getAttribute('data-id');
            var name = chk.getAttribute('data-name');
            var code = chk.getAttribute('data-code');
            var barcode = chk.getAttribute('data-barcode');
            var location = chk.getAttribute('data-location') || 'Gudang TOC';
            var copiesInput = document.getElementById('copies_' + id);
            var copies = parseInt(copiesInput ? copiesInput.value : '1', 10) || 1;

            for (var i = 0; i < copies; i++) {
                totalStickers++;
                var card = document.createElement('div');

                if (format === 'blueprint-gl06') {
                    // Stiker Besar Kotak 96 x 93.1 mm
                    card.className = 'sticker-card-gl06 keep-light p-4 rounded-xl border border-dashed border-slate-300 bg-white flex flex-col justify-between items-center text-center shadow-2xs';

                    var header = document.createElement('div');
                    header.className = 'w-full flex items-center justify-between text-[11px] font-extrabold tracking-wider text-slate-800 pb-1.5 border-b border-slate-200';
                    header.innerHTML = '<span class="text-cyan-700 font-mono-code font-black">LOGISTIK.TOC</span><span class="font-mono-code text-slate-600 font-bold">' + code + '</span>';

                    var title = document.createElement('div');
                    title.className = 'text-sm font-extrabold text-slate-900 leading-snug line-clamp-2 my-1 px-1';
                    title.textContent = name;

                    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.setAttribute('class', 'my-1.5 max-w-full');

                    var footer = document.createElement('div');
                    footer.className = 'w-full text-[10px] text-slate-600 border-t border-slate-200 pt-1.5 font-bold flex items-center justify-between';
                    footer.innerHTML = '<span class="truncate">Rak: ' + location + '</span><span class="text-emerald-700 font-extrabold uppercase">TOC GEGANA</span>';

                    card.appendChild(header);
                    card.appendChild(title);
                    card.appendChild(svg);
                    card.appendChild(footer);
                    grid.appendChild(card);

                    if (typeof JsBarcode !== 'undefined') {
                        try {
                            JsBarcode(svg, barcode, {
                                format: 'CODE128',
                                lineColor: '#000000',
                                width: 1.65,
                                height: 46,
                                displayValue: true,
                                fontSize: 12,
                                margin: 2
                            });
                        } catch (e) {}
                    }
                } else if (format === 'thermal-roll') {
                    // Stiker Thermal Roll 50 x 30 mm
                    card.className = 'sticker-card-thermal keep-light p-2 rounded-lg border border-slate-300 bg-white flex flex-col justify-between items-center text-center shadow-2xs';

                    var header = document.createElement('div');
                    header.className = 'w-full flex items-center justify-between text-[9px] font-extrabold tracking-tight text-slate-800';
                    header.innerHTML = '<span class="font-black text-slate-900">LOGISTIK.TOC</span><span class="font-mono-code">' + code + '</span>';

                    var title = document.createElement('div');
                    title.className = 'text-[10px] font-extrabold text-slate-900 truncate w-full px-0.5 leading-tight';
                    title.textContent = name;

                    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.setAttribute('class', 'my-0.5 max-w-full');

                    var footer = document.createElement('div');
                    footer.className = 'w-full text-[8px] text-slate-500 font-bold flex items-center justify-between leading-none';
                    footer.innerHTML = '<span>' + location + '</span><span>TOC</span>';

                    card.appendChild(header);
                    card.appendChild(title);
                    card.appendChild(svg);
                    card.appendChild(footer);
                    grid.appendChild(card);

                    if (typeof JsBarcode !== 'undefined') {
                        try {
                            JsBarcode(svg, barcode, {
                                format: 'CODE128',
                                lineColor: '#000000',
                                width: 1.15,
                                height: 24,
                                displayValue: true,
                                fontSize: 9,
                                margin: 1
                            });
                        } catch (e) {}
                    }
                } else {
                    // Stiker BLUEPRINT GL-22 (64.5 x 47 mm) & A4 Fleksibel
                    card.className = (format === 'blueprint-gl22' ? 'sticker-card-gl22 ' : 'sticker-card-free ') + 'keep-light p-2.5 rounded-xl border border-dashed border-slate-300 bg-white flex flex-col justify-between items-center text-center shadow-2xs';

                    var header = document.createElement('div');
                    header.className = 'w-full flex items-center justify-between text-[9px] font-extrabold uppercase tracking-tight text-slate-800 pb-1 border-b border-slate-100';
                    header.innerHTML = '<span class="text-cyan-800 font-black">LOGISTIK.TOC</span><span class="font-mono-code text-slate-600">' + code + '</span>';

                    var title = document.createElement('div');
                    title.className = 'text-[11px] font-extrabold text-slate-900 leading-tight line-clamp-1 my-0.5 px-0.5';
                    title.textContent = name;

                    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.setAttribute('class', 'my-0.5 max-w-full');

                    var footer = document.createElement('div');
                    footer.className = 'w-full text-[9px] text-slate-600 border-t border-slate-100 pt-1 font-bold flex items-center justify-between leading-none';
                    footer.innerHTML = '<span class="truncate">Rak: ' + location + '</span><span class="text-emerald-700 font-extrabold">TOC</span>';

                    card.appendChild(header);
                    card.appendChild(title);
                    card.appendChild(svg);
                    card.appendChild(footer);
                    grid.appendChild(card);

                    if (typeof JsBarcode !== 'undefined') {
                        try {
                            JsBarcode(svg, barcode, {
                                format: 'CODE128',
                                lineColor: '#000000',
                                width: 1.25,
                                height: 30,
                                displayValue: true,
                                fontSize: 10,
                                margin: 2
                            });
                        } catch (e) {}
                    }
                }
            }
        });

        if (totalStickers === 0) {
            grid.className = 'py-16 text-center border-2 border-dashed border-slate-200 rounded-2xl';
            grid.innerHTML = '<div class="max-w-sm mx-auto px-4"><div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg></div><div class="text-sm font-bold text-slate-800">Belum Ada Barang yang Dipilih</div><p class="text-xs text-slate-500 mt-1">Centang barang pada daftar di sebelah kiri atau klik <strong>"Pilih Semua"</strong> untuk memuat stiker siap cetak.</p></div>';
        }

        // Kalkulasi lembar A4
        var sheetsNeeded = Math.ceil(totalStickers / sheetCapacity);
        if (sheetCalc) {
            sheetCalc.textContent = totalStickers + ' Stiker (' + (totalStickers > 0 ? sheetsNeeded + ' Lembar A4' : '0 Lembar') + ')';
        }
        document.getElementById('total-stickers-badge').textContent = totalStickers + ' Stiker (' + (totalStickers > 0 ? sheetsNeeded + ' Lembar A4' : '0 Lembar') + ')';
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderSingleStudioPreview();
        rebuildPrintSheet();
    });
</script>
@endpush
