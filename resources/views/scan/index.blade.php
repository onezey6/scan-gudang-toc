@extends('layouts.app')

@section('title', 'Terminal Scan Barcode')
@section('page_title', 'Terminal Scan Barcode (Barang Masuk & Keluar)')
@section('page_subtitle', 'Pindai barcode barang via Kamera HP/Webcam atau Alat Scanner USB untuk menyimpan mutasi ke database penyimpanan')

@push('styles')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@section('content')
    <!-- Summary Metrics Today -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5 mb-6">
        <div class="card-elevated bg-white p-4 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-emerald-500"></div>
            <div class="text-[11px] font-bold uppercase text-emerald-700">Scan Masuk Hari Ini</div>
            <div class="mt-1 text-2xl font-extrabold text-emerald-600">+{{ number_format($todaySummary['in_qty']) }} <span class="text-xs font-semibold text-slate-400">unit</span></div>
            <div class="text-xs text-slate-500 mt-0.5">{{ $todaySummary['in_count'] }} transaksi masuk</div>
        </div>
        <div class="card-elevated bg-white p-4 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-amber-500"></div>
            <div class="text-[11px] font-bold uppercase text-amber-700">Scan Keluar Hari Ini</div>
            <div class="mt-1 text-2xl font-extrabold text-amber-600">-{{ number_format($todaySummary['out_qty']) }} <span class="text-xs font-semibold text-slate-400">unit</span></div>
            <div class="text-xs text-slate-500 mt-0.5">{{ $todaySummary['out_count'] }} transaksi keluar</div>
        </div>
        <div class="card-elevated bg-gradient-to-r from-[#0B1120] to-slate-900 text-white p-4 rounded-2xl border border-slate-800 shadow-xs col-span-2 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 flex items-center justify-center font-extrabold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Petugas Operator Scanner Aktif</div>
                    <div class="text-sm sm:text-base font-extrabold text-white">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-300">{{ auth()->user()->job_title ?: 'Petugas Logistik' }} • Tercatat otomatis di database</div>
                </div>
            </div>
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                READY
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 5 Cols: Laser Viewfinder Scanner + Quick Catalog Picker -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 bg-[#0B1120] text-white flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <div>
                            <h3 class="text-sm font-extrabold">Pemindai Kamera & Laser Scanner</h3>
                            <p class="text-[11px] text-slate-400">Kamera HP/Webcam atau Alat Scanner USB/Bluetooth</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <!-- Jalur 1: Kamera Native HP (Kebal batasan HTTP) -->
                        <input type="file" id="barcode_camera_file" accept="image/*" capture="environment" class="hidden" onchange="handleBarcodeFileScan(this)">
                        <label for="barcode_camera_file"
                               class="cursor-pointer px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 active:scale-95"
                               title="Buka Kamera HP untuk foto stiker barcode (bekerja 100% di semua HP)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Foto Barcode</span>
                        </label>

                        <!-- Jalur 2: Live Video Stream -->
                        <button type="button" id="btn-toggle-camera" onclick="toggleCameraScanner()"
                                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span id="btn-toggle-camera-text">Live Stream</span>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div id="camera-container" class="hidden rounded-2xl overflow-hidden border-2 border-emerald-500 bg-slate-950">
                        <div id="reader" class="w-full"></div>
                    </div>

                    <!-- Interactive Viewfinder with Animated Laser Line -->
                    <div id="camera-placeholder" class="relative p-6 rounded-2xl bg-[#0F172A] text-white border border-slate-800 text-center overflow-hidden">
                        <!-- Viewfinder Corners -->
                        <div class="pointer-events-none absolute top-3 left-3 w-5 h-5 border-t-2 border-l-2 border-emerald-400 rounded-tl"></div>
                        <div class="pointer-events-none absolute top-3 right-3 w-5 h-5 border-t-2 border-r-2 border-emerald-400 rounded-tr"></div>
                        <div class="pointer-events-none absolute bottom-3 left-3 w-5 h-5 border-b-2 border-l-2 border-emerald-400 rounded-bl"></div>
                        <div class="pointer-events-none absolute bottom-3 right-3 w-5 h-5 border-b-2 border-r-2 border-emerald-400 rounded-br"></div>
                        <!-- Animated Green Laser -->
                        <div class="laser-line"></div>

                        <div class="relative z-10 py-2">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div id="camera-status-text" class="mt-2.5 text-xs font-extrabold uppercase tracking-wider text-emerald-400">Sensor Alat Scanner USB Aktif</div>
                            <p class="text-[11px] text-slate-300 mt-1 max-w-xs mx-auto leading-relaxed">
                                Tembakkan alat scanner fisik, gunakan <strong>"Foto Barcode"</strong> untuk kamera HP, atau klik <strong>"Live Stream"</strong> untuk video langsung.
                            </p>
                        </div>
                    </div>

                    <!-- Daftar Simulasi / Klik Cepat Barcode Barang Gudang -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-600">Klik Cepat Simulasi Scan Barang:</span>
                            <span class="text-[11px] text-slate-400 font-medium">{{ $items->count() }} Barang</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-2xl">
                            @foreach ($items as $item)
                                <button type="button"
                                        data-barcode="{{ $item->barcode }}"
                                        onclick="triggerBarcodeLookup(this.getAttribute('data-barcode'), 'manual')"
                                        class="w-full p-3 text-left hover:bg-emerald-50/70 flex items-center justify-between gap-2.5 transition-colors group">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if ($item->photo_url)
                                            <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 truncate">{{ $item->name }}</div>
                                            <div class="text-[11px] font-mono-code text-slate-500 mt-0.5">
                                                <span class="text-emerald-700 font-semibold">{{ $item->barcode }}</span> • {{ $item->item_code }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold {{ $item->isLowStock() ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-800' }}">
                                            {{ $item->stock }} {{ $item->unit }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 7 Cols: Scan Processing Form & Detected Item Card -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <!-- Mode Tabs: Barang Masuk vs Barang Keluar -->
                <div class="grid grid-cols-2 border-b border-slate-200">
                    <button type="button" id="tab-mode-in" onclick="setScanMode('in')"
                            class="py-4 px-4 text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 border-b-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span>BARANG MASUK (INBOUND)</span>
                    </button>
                    <button type="button" id="tab-mode-out" onclick="setScanMode('out')"
                            class="py-4 px-4 text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 border-b-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span>BARANG KELUAR (OUTBOUND)</span>
                    </button>
                </div>

                <form id="form-scan-process" action="{{ route('scan.process') }}" method="POST" class="p-5 sm:p-6 space-y-4">
                    @csrf
                    <input type="hidden" id="movement_type" name="movement_type" value="{{ $initialMode }}">
                    <input type="hidden" id="scan_method" name="scan_method" value="scanner_device">

                    <!-- Barcode Input Field -->
                    <div>
                        <label for="barcode_input" class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                            Hasil Scan Kode Barcode Barang *
                        </label>
                        <div class="flex gap-2">
                            <input type="text" id="barcode_input" name="barcode" value="{{ $preselectedBarcode }}" required autofocus
                                   placeholder="Tembakkan scanner / kamera atau ketik kode barcode lalu Enter..."
                                   class="flex-1 px-4 py-3 rounded-xl border-2 border-slate-300 font-mono-code text-base font-extrabold text-slate-900 focus:outline-none focus:border-emerald-600">
                            <button type="button" onclick="triggerBarcodeLookup(document.getElementById('barcode_input').value, 'manual')"
                                    class="px-5 py-3 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs sm:text-sm font-bold shrink-0 transition-colors">
                                Cek Barcode
                            </button>
                        </div>
                    </div>

                    <!-- Detected Item Detail Box with Photo & Live Barcode Graphic -->
                    <div id="detected-item-card" class="hidden p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-emerald-50/40 border-2 border-emerald-500/40 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3.5 border-b border-slate-200/80">
                            <div class="flex items-start gap-3.5">
                                <div id="det_photo_box" class="w-16 h-16 rounded-xl border border-slate-200 bg-white flex items-center justify-center overflow-hidden shrink-0 text-slate-400 shadow-2xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <span id="det_type_badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800"></span>
                                    <h4 id="det_name" class="text-base sm:text-lg font-extrabold text-slate-900 mt-1">-</h4>
                                    <div class="text-xs text-slate-600 font-mono-code mt-0.5">
                                        Kode: <strong id="det_code">-</strong> • Barcode: <strong id="det_barcode" class="text-emerald-700">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="keep-light bg-white px-2.5 py-1 rounded-xl border border-slate-200">
                                    <svg id="det_barcode_svg"></svg>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Stok Gudang</div>
                                    <div id="det_stock" class="text-2xl font-extrabold text-emerald-600">0 Unit</div>
                                    <div id="det_location" class="text-[11px] font-semibold text-slate-500">Lokasi: -</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                            <span>Kategori: <strong id="det_category" class="text-slate-900">-</strong></span>
                            <span>User Pembuat Barang: <strong id="det_creator" class="text-emerald-700">-</strong></span>
                        </div>
                    </div>

                    <div id="detected-error-card" class="hidden p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800"></div>

                    <!-- Transaction Details with Quick Quantity Steppers -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-5">
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jumlah (Qty) *</label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" id="scan_qty" name="quantity" value="1" min="1" required
                                       class="w-24 px-3 py-2.5 rounded-xl border border-slate-300 text-base font-extrabold text-slate-900 text-center">
                                <button type="button" onclick="adjustScanQty(1)" class="px-2.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-extrabold text-slate-700">+1</button>
                                <button type="button" onclick="adjustScanQty(5)" class="px-2.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-extrabold text-slate-700">+5</button>
                                <button type="button" onclick="adjustScanQty(10)" class="px-2.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-extrabold text-slate-700">+10</button>
                            </div>
                        </div>

                        <div class="sm:col-span-7">
                            <label id="label_source_dest" class="block text-xs font-bold uppercase text-slate-600 mb-1">
                                Asal Barang / Pemasok (Barang Masuk)
                            </label>
                            <input type="text" id="source_destination" name="source_destination"
                                   placeholder="Contoh: Vendor PT Mitra / Pengiriman Gudang Pusat / Tim Operasional"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div class="sm:col-span-5">
                            <label id="label_partner" class="block text-xs font-bold uppercase text-slate-600 mb-1">
                                Nama Pengantar / Penerima
                            </label>
                            <input type="text" name="partner_name" placeholder="Nama orang yang menyerahkan/menerima"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div class="sm:col-span-7">
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Catatan Transaksi Scan</label>
                            <input type="text" name="notes" placeholder="Keterangan tambahan (opsional)..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>

                    <button type="submit" id="btn-submit-scan"
                            class="w-full py-3.5 px-5 rounded-xl text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="btn-submit-text">SIMPAN HASIL SCAN BARANG MASUK KE DATABASE</span>
                    </button>
                </form>
            </div>

            <!-- Riwayat Hasil Scan Terkini -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-900">Hasil Scan Tersimpan di Database Penyimpanan</h3>
                    <a href="{{ route('reports.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Buka Laporan &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fluid">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500">
                                <th class="py-3 px-4">No. Ref & Waktu</th>
                                <th class="py-3 px-4">Barang & Barcode</th>
                                <th class="py-3 px-4">Mutasi & Stok</th>
                                <th class="py-3 px-4">Petugas Scan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80 text-sm">
                            @foreach ($recentScans as $scan)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-3 px-4 align-top">
                                        <div class="font-mono-code text-xs font-bold text-slate-900">{{ $scan->reference_no }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $scan->scanned_at?->format('d/m/Y H:i:s') }}</div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <div class="flex items-start gap-2.5">
                                            @if ($scan->item?->photo_url)
                                                <button type="button" onclick="openPhotoLightbox('{{ $scan->item->photo_url }}', '{{ addslashes($scan->item->name) }}')"
                                                        class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                                    <img src="{{ $scan->item->photo_url }}" alt="{{ $scan->item->name }}" class="w-full h-full object-cover">
                                                </button>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $scan->item?->name }}</div>
                                                <div class="text-xs font-mono-code text-emerald-700 font-semibold">Barcode: {{ $scan->item?->barcode }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $scan->source_destination }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        @if ($scan->movement_type === 'in')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                                                MASUK +{{ $scan->quantity }} {{ $scan->item?->unit }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800">
                                                KELUAR -{{ $scan->quantity }} {{ $scan->item?->unit }}
                                            </span>
                                        @endif
                                        <div class="text-[11px] text-slate-500 mt-1">
                                            Stok: {{ $scan->stock_before }} &rarr; <strong>{{ $scan->stock_after }}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <div class="text-xs font-bold text-slate-900">{{ $scan->user?->name ?? 'Sistem' }}</div>
                                        <div class="text-[11px] text-slate-400">Metode: {{ strtoupper($scan->scan_method) }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Panduan Kamera HP (Koneksi Wi-Fi HTTP) -->
    <div id="modal-camera-http-guide" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-[#0B1120] text-white border-2 border-cyan-500/40 rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl relative overflow-hidden">
            <div class="flex items-start justify-between gap-3 pb-3.5 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-white">Panduan Kamera HP di Jaringan Wi-Fi</h3>
                        <p class="text-[11px] text-slate-400">Batasan Keamanan Browser HP pada Alamat HTTP</p>
                    </div>
                </div>
                <button type="button" onclick="closeCameraHttpModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-4 space-y-4 text-xs">
                <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 leading-relaxed">
                    Browser HP (Chrome/Safari) secara default <strong>membatasi live video streaming</strong> pada koneksi lokal non-HTTPS <span id="camera-error-detail" class="text-[10px] opacity-80"></span>.
                </div>

                <!-- Solusi 1: Tombol Foto Barcode (Langsung Jalan) -->
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 space-y-2">
                    <div class="flex items-center gap-2 text-emerald-400 font-extrabold text-xs uppercase tracking-wider">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-slate-950 text-[10px] font-black">SOLUSI 1 • REKOMENDASI</span>
                        Langsung Berfungsi di Semua HP
                    </div>
                    <p class="text-slate-300 text-[11px] leading-relaxed">
                        Gunakan fitur <strong>Foto Barcode</strong>. HP akan membuka kamera bawaan untuk menjepret stiker barcode dan sistem langsung membacanya secara otomatis.
                    </p>
                    <button type="button" onclick="triggerNativeCameraFromModal()"
                            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-400 hover:to-emerald-500 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>AMBIL FOTO BARCODE SEKARANG (KAMERA HP)</span>
                    </button>
                </div>

                <!-- Solusi 2: Aktifkan Live Stream di Chrome Flags -->
                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-cyan-400 text-xs">SOLUSI 2 • JIKA INGIN LIVE STREAM VIDEO MENYALA TERUS</span>
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        Cukup izinkan alamat web ini di Chrome HP sekali saja (hanya butuh 15 detik):
                    </p>
                    <ol class="list-decimal list-inside space-y-1 text-slate-300 text-[11px]">
                        <li>Buka tab baru di Chrome HP, ketik: <code class="text-amber-300 font-mono font-bold select-all">chrome://flags</code></li>
                        <li>Cari: <strong class="text-white">Insecure origins treated as secure</strong></li>
                        <li>
                            Masukkan:
                            <code class="text-cyan-300 font-mono font-bold select-all bg-slate-950 px-1.5 py-0.5 rounded border border-slate-700">http://192.168.0.114:8000</code>
                            <button type="button" onclick="copyLanOrigin()" class="ml-1 text-[10px] text-teal-400 underline font-bold hover:text-teal-300">Salin</button>
                        </li>
                        <li>Ubah pilihan dari <em>Disabled</em> menjadi <strong class="text-emerald-400">Enabled</strong> lalu klik tombol <strong class="text-white">Relaunch</strong> di pojok kanan bawah.</li>
                    </ol>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end">
                <button type="button" onclick="closeCameraHttpModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all">
                    Tutup Panduan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var html5QrCodeScanner = null;
    var cameraRunning = false;

    function adjustScanQty(delta) {
        var input = document.getElementById('scan_qty');
        var cur = parseInt(input.value || '1', 10);
        input.value = Math.max(1, cur + delta);
    }

    function playBeep() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (e) {}
    }

    function setScanMode(mode) {
        document.getElementById('movement_type').value = mode;
        var tabIn = document.getElementById('tab-mode-in');
        var tabOut = document.getElementById('tab-mode-out');
        var btnSubmit = document.getElementById('btn-submit-scan');
        var btnText = document.getElementById('btn-submit-text');
        var lblSource = document.getElementById('label_source_dest');
        var lblPartner = document.getElementById('label_partner');

        if (mode === 'in') {
            tabIn.className = 'py-4 px-4 text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 border-b-2 border-emerald-600 bg-emerald-50/80 text-emerald-800 transition-all';
            tabOut.className = 'py-4 px-4 text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 border-b-2 border-transparent text-slate-500 hover:bg-slate-50 transition-all';
            btnSubmit.className = 'w-full py-3.5 px-5 rounded-xl text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500';
            btnText.textContent = 'SIMPAN HASIL SCAN BARANG MASUK KE DATABASE';
            lblSource.textContent = 'Asal Barang / Pemasok (Barang Masuk)';
            lblPartner.textContent = 'Nama Pengantar / Ekspedisi';
        } else {
            tabOut.className = 'py-4 px-4 text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 border-b-2 border-amber-600 bg-amber-50/80 text-amber-800 transition-all';
            tabIn.className = 'py-4 px-4 text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 border-b-2 border-transparent text-slate-500 hover:bg-slate-50 transition-all';
            btnSubmit.className = 'w-full py-3.5 px-5 rounded-xl text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500';
            btnText.textContent = 'SIMPAN HASIL SCAN BARANG KELUAR KE DATABASE';
            lblSource.textContent = 'Tujuan Pengeluaran / Divisi Penerima (Barang Keluar)';
            lblPartner.textContent = 'Nama Penerima / Pemakai Barang';
        }
    }

    function triggerBarcodeLookup(code, method) {
        if (!code || !code.trim()) return;
        var cleanCode = code.trim();
        document.getElementById('barcode_input').value = cleanCode;
        if (method) {
            document.getElementById('scan_method').value = method;
        }

        fetch('{{ route('scan.lookup') }}?barcode=' + encodeURIComponent(cleanCode), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var card = document.getElementById('detected-item-card');
            var errCard = document.getElementById('detected-error-card');

            if (data.found && data.item) {
                playBeep();
                errCard.classList.add('hidden');
                card.classList.remove('hidden');

                document.getElementById('det_type_badge').textContent = data.item.item_type_label;
                document.getElementById('det_name').textContent = data.item.name;
                document.getElementById('det_code').textContent = data.item.item_code;
                document.getElementById('det_barcode').textContent = data.item.barcode;
                document.getElementById('det_stock').textContent = data.item.stock + ' ' + data.item.unit;
                document.getElementById('det_location').textContent = 'Lokasi: ' + data.item.storage_location;
                document.getElementById('det_category').textContent = data.item.category_name;
                document.getElementById('det_creator').textContent = data.item.creator_name;

                var photoBox = document.getElementById('det_photo_box');
                if (photoBox) {
                    if (data.item.photo_url) {
                        photoBox.innerHTML = '<img src="' + data.item.photo_url + '" alt="Foto Barang" class="w-full h-full object-cover cursor-pointer" onclick="openPhotoLightbox(\'' + data.item.photo_url + '\', \'' + (data.item.name || '').replace(/\'/g, '') + '\')">';
                    } else {
                        photoBox.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
                    }
                }

                if (typeof JsBarcode !== 'undefined') {
                    try {
                        JsBarcode('#det_barcode_svg', data.item.barcode, {
                            format: 'CODE128',
                            lineColor: '#0f172a',
                            width: 1.3,
                            height: 30,
                            displayValue: false,
                            margin: 0
                        });
                    } catch (e) {}
                }

                document.getElementById('scan_qty').focus();
            } else {
                card.classList.add('hidden');
                errCard.classList.remove('hidden');
                errCard.textContent = data.message || 'Barang dengan barcode tersebut belum terdaftar.';
            }
        })
        .catch(function () {});
    }

    function handleBarcodeFileScan(input) {
        if (!input.files || input.files.length === 0) return;
        var file = input.files[0];
        var statusEl = document.getElementById('camera-status-text');
        if (statusEl) {
            statusEl.innerHTML = '<span class="text-amber-400 font-bold animate-pulse">⏳ Sedang memindai barcode dari foto...</span>';
        }

        if (typeof Html5Qrcode === 'undefined') {
            alert('Modul pemindai kamera belum selesai dimuat. Silakan tunggu sesaat lalu coba lagi.');
            return;
        }

        var scanner = html5QrCodeScanner || new Html5Qrcode('reader');
        scanner.scanFile(file, true)
            .then(function (decodedText) {
                if (statusEl) {
                    statusEl.innerHTML = '<span class="text-emerald-400 font-bold">✓ Barcode Terdeteksi: ' + decodedText + '</span>';
                }
                triggerBarcodeLookup(decodedText, 'camera');
                input.value = '';
            })
            .catch(function (err) {
                if (statusEl) {
                    statusEl.textContent = 'Sensor Alat Scanner USB Aktif';
                }
                alert('Barcode tidak terbaca dari foto tersebut. Tips: Pastikan barcode terlihat jelas, fokus, tidak terpotong, dan pencahayaan cukup.');
                input.value = '';
            });
    }

    function openCameraHttpModal(errDetail) {
        var modal = document.getElementById('modal-camera-http-guide');
        if (modal) {
            if (errDetail && document.getElementById('camera-error-detail')) {
                document.getElementById('camera-error-detail').textContent = '(' + errDetail + ')';
            }
            modal.classList.remove('hidden');
        }
    }

    function closeCameraHttpModal() {
        var modal = document.getElementById('modal-camera-http-guide');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function triggerNativeCameraFromModal() {
        closeCameraHttpModal();
        var fileInput = document.getElementById('barcode_camera_file');
        if (fileInput) {
            fileInput.click();
        }
    }

    function copyLanOrigin() {
        var origin = 'http://' + window.location.host;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(origin).then(function () {
                alert('Alamat ' + origin + ' berhasil disalin!');
            });
        } else {
            prompt('Salin alamat ini untuk dimasukkan ke chrome://flags:', origin);
        }
    }

    function toggleCameraScanner() {
        var container = document.getElementById('camera-container');
        var placeholder = document.getElementById('camera-placeholder');
        var btnText = document.getElementById('btn-toggle-camera-text');

        if (cameraRunning && html5QrCodeScanner) {
            html5QrCodeScanner.stop().then(function () {
                cameraRunning = false;
                container.classList.add('hidden');
                placeholder.classList.remove('hidden');
                if (btnText) btnText.textContent = 'Live Stream';
            }).catch(function () {});
            return;
        }

        // Cek langsung jika browser di HP tidak mendukung getUserMedia karena koneksi HTTP
        var isSecure = window.isSecureContext || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        if (!isSecure && (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia)) {
            openCameraHttpModal('Koneksi HTTP lokal memerlukan izin di browser HP');
            return;
        }

        if (typeof Html5Qrcode === 'undefined') {
            alert('Modul kamera sedang dimuat, silakan coba sesaat lagi.');
            return;
        }

        container.classList.remove('hidden');
        placeholder.classList.add('hidden');
        if (btnText) btnText.textContent = 'Tutup Live';

        html5QrCodeScanner = new Html5Qrcode('reader');
        html5QrCodeScanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 120 } },
            function (decodedText) {
                triggerBarcodeLookup(decodedText, 'camera');
            },
            function () {}
        ).then(function () {
            cameraRunning = true;
        }).catch(function (err) {
            cameraRunning = false;
            container.classList.add('hidden');
            placeholder.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Live Stream';

            var errStr = String(err || '');
            if (errStr.indexOf('streaming not supported') !== -1 || errStr.indexOf('NotAllowedError') !== -1 || errStr.indexOf('mediaDevices') !== -1 || !window.isSecureContext) {
                openCameraHttpModal(errStr);
            } else {
                alert('Kamera tidak dapat diakses pada perangkat ini (' + errStr + '). Silakan gunakan tombol "Foto Barcode (HP)" atau alat scanner USB.');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setScanMode('{{ $initialMode }}');

        var barcodeInput = document.getElementById('barcode_input');
        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                triggerBarcodeLookup(barcodeInput.value, 'scanner_device');
            }
        });

        if (barcodeInput.value.trim() !== '') {
            triggerBarcodeLookup(barcodeInput.value.trim(), 'manual');
        }

        // Fitur Langsung Scan Otomatis jika dipicu dari tombol SCAN navigasi bawah (?autostart=camera / 1)
        var urlParams = new URLSearchParams(window.location.search);
        var auto = urlParams.get('autostart');
        if (auto === 'camera' || auto === 'live' || auto === '1' || auto === 'scan' || auto === 'photo') {
            setTimeout(function () {
                if (!cameraRunning) {
                    toggleCameraScanner();
                }
            }, 300);
        }
    });
</script>
@endpush
