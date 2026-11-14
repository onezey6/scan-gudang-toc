<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Kendali Stok — {{ $item->name }} ({{ $item->barcode }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm 12mm 14mm;
        }

        @media screen {
            body {
                background-color: #0B1120;
                padding: 1.5rem 1rem;
                color: #0f172a;
            }
            .a4-sheet {
                max-width: 210mm;
                margin: 0 auto;
                background: #ffffff;
                padding: 14mm 16mm;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
                border: 1px solid #cbd5e1;
                border-radius: 0.75rem;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                font-size: 10pt !important;
                line-height: 1.4 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .a4-sheet {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: transparent !important;
            }
        }

        .font-mono-code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    </style>
</head>
<body>
    <!-- Floating Toolbar (Screen Only) -->
    <div class="no-print max-w-[210mm] mx-auto mb-4 flex items-center justify-between gap-3 bg-slate-900/90 backdrop-blur p-3 rounded-2xl border border-slate-800 text-white shadow-xl">
        <a href="{{ route('items.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800">
            &larr; Kembali ke Master Barang
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Official A4 Sheet -->
    <div class="a4-sheet text-slate-900">
        <!-- Header KOP Kedinasan -->
        <div class="flex items-center gap-4 pb-3 border-b-2 border-slate-900">
            <div class="w-16 h-16 shrink-0 rounded-full border border-slate-400 p-0.5 overflow-hidden flex items-center justify-center bg-white">
                <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo TOC" class="w-full h-full object-cover rounded-full">
            </div>
            <div class="flex-1 text-center pr-16">
                <h2 class="text-base font-extrabold tracking-wider uppercase text-slate-900">TIM OPERASIONAL KHUSUS (TOC) — LOGISTIK GUDANG</h2>
                <h3 class="text-xs font-bold uppercase tracking-wide text-slate-700 mt-0.5">SISTEM KONTROL PENYIMPANAN, SCAN BARCODE & MUTASI INVENTARIS</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Markas Logistik TOC • Portal: logistiktoc.my.id • Rekapitulasi Audit Kedinasan</p>
            </div>
        </div>

        <!-- Judul Dokumen -->
        <div class="text-center my-3.5">
            <h1 class="text-sm font-extrabold uppercase tracking-widest text-slate-900 border-b-2 border-slate-900 inline-block pb-0.5">
                KARTU KENDALI PERPUTARAN STOK BARANG
            </h1>
            <div class="text-[11px] font-mono-code font-bold text-slate-600 mt-1">
                KODE KARTU: KK-{{ $item->item_code }} • TANGGAL CETAK: {{ date('d/m/Y H:i') }} WIB
            </div>
        </div>

        <!-- Profil Barang & Barcode Visual Box -->
        <div class="p-3 rounded-xl border border-slate-300 bg-slate-50 mb-3.5 flex items-start justify-between gap-4">
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs flex-1">
                <div>
                    <span class="text-slate-500 text-[10px] uppercase font-bold block">Nama Lengkap Barang:</span>
                    <strong class="text-slate-900 text-sm font-extrabold">{{ $item->name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 text-[10px] uppercase font-bold block">Kategori & Tipe:</span>
                    <strong class="text-slate-900">{{ $item->category?->name ?: 'Umum' }} ({{ strtoupper($item->item_type) }})</strong>
                </div>
                <div>
                    <span class="text-slate-500 text-[10px] uppercase font-bold block">Kode & Barcode:</span>
                    <span class="font-mono-code font-bold text-slate-800">{{ $item->item_code }} / {{ $item->barcode }}</span>
                </div>
                <div>
                    <span class="text-slate-500 text-[10px] uppercase font-bold block">Merek / Lokasi Rak:</span>
                    <span class="font-semibold text-slate-800">{{ $item->brand ?: '-' }} • {{ $item->storage_location ?: 'Gudang Utama' }}</span>
                </div>
            </div>

            <div class="text-right shrink-0 flex flex-col items-end">
                <svg id="stock_card_barcode_svg" class="max-w-[140px] h-8"></svg>
                <div class="mt-1 flex items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        SALDO STOK: {{ $item->stock }} {{ $item->unit }}
                    </span>
                    <span class="text-[10px] text-slate-500">Min: {{ $item->min_stock }}</span>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Kronologis Mutasi -->
        <div class="mb-4 overflow-hidden border border-slate-300 rounded-lg">
            <table class="w-full text-left border-collapse text-[11px]">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300 font-bold uppercase text-slate-700 text-[10px]">
                        <th class="py-2 px-2.5 w-8 text-center border-r border-slate-300">No</th>
                        <th class="py-2 px-2.5 w-24 border-r border-slate-300">Waktu & Tgl</th>
                        <th class="py-2 px-2.5 w-32 border-r border-slate-300">No. Referensi</th>
                        <th class="py-2 px-2.5 w-24 border-r border-slate-300">Aktivitas</th>
                        <th class="py-2 px-2.5 border-r border-slate-300">Sumber / Tujuan / Partner</th>
                        <th class="py-2 px-2.5 w-20 border-r border-slate-300">Petugas</th>
                        <th class="py-2 px-2 w-14 text-center border-r border-slate-300 text-emerald-700 font-extrabold">Masuk (+)</th>
                        <th class="py-2 px-2 w-14 text-center border-r border-slate-300 text-amber-700 font-extrabold">Keluar (-)</th>
                        <th class="py-2 px-2.5 w-16 text-center font-extrabold bg-slate-200 text-slate-900">Saldo Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($movements as $idx => $m)
                        <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/70' }}">
                            <td class="py-1.5 px-2.5 text-center border-r border-slate-200">{{ $idx + 1 }}</td>
                            <td class="py-1.5 px-2.5 font-mono-code text-[10px] border-r border-slate-200">{{ $m->scanned_at?->format('d/m/y H:i') }}</td>
                            <td class="py-1.5 px-2.5 font-mono-code text-[10px] font-bold border-r border-slate-200">{{ $m->reference_no }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-200">
                                @if ($m->movement_type === 'in')
                                    <span class="font-bold text-emerald-700">Masuk</span>
                                @else
                                    <span class="font-bold text-amber-700">Keluar</span>
                                @endif
                                <span class="text-[9px] text-slate-400 block">({{ $m->scan_method }})</span>
                            </td>
                            <td class="py-1.5 px-2.5 border-r border-slate-200">
                                <div class="font-semibold text-slate-800">{{ $m->source_destination ?: '-' }}</div>
                                @if ($m->partner_name)
                                    <div class="text-[9px] text-slate-500">Pihak: {{ $m->partner_name }}</div>
                                @endif
                            </td>
                            <td class="py-1.5 px-2.5 border-r border-slate-200 text-[10px]">{{ $m->user?->name ?: 'Sistem' }}</td>
                            <td class="py-1.5 px-2 text-center font-bold text-emerald-700 border-r border-slate-200">
                                {{ $m->movement_type === 'in' ? '+' . $m->quantity : '-' }}
                            </td>
                            <td class="py-1.5 px-2 text-center font-bold text-amber-700 border-r border-slate-200">
                                {{ $m->movement_type === 'out' ? '-' . $m->quantity : '-' }}
                            </td>
                            <td class="py-1.5 px-2.5 text-center font-extrabold bg-slate-100 font-mono-code text-slate-900">
                                {{ $m->stock_after }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-400 italic">Belum ada riwayat mutasi scan untuk barang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold border-t border-slate-300 text-[10px]">
                        <td colspan="6" class="py-2 px-3 text-right uppercase text-slate-600 border-r border-slate-300">Total Akumulasi Mutasi:</td>
                        <td class="py-2 px-2 text-center text-emerald-800 border-r border-slate-300 font-extrabold">+{{ $totalIn }}</td>
                        <td class="py-2 px-2 text-center text-amber-800 border-r border-slate-300 font-extrabold">-{{ $totalOut }}</td>
                        <td class="py-2 px-2.5 text-center bg-slate-200 text-slate-900 font-extrabold text-xs font-mono-code">{{ $item->stock }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Kolom Pengesahan Tanda Tangan Kedinasan -->
        <div class="grid grid-cols-2 gap-8 pt-4 text-xs">
            <div class="text-center">
                <p class="text-[11px] text-slate-600">Mengetahui,</p>
                <p class="font-bold text-slate-900 uppercase text-xs">Kepala Seksi Logistik TOC</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-slate-900 underline">( .................................................... )</p>
                <p class="text-[10px] text-slate-500 font-mono-code">NRP / NIP. .................................</p>
            </div>

            <div class="text-center">
                <p class="text-[11px] text-slate-600">Dicetak & Diperiksa oleh,</p>
                <p class="font-bold text-slate-900 uppercase text-xs">Petugas Pengelola Gudang</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-slate-900 underline">( {{ auth()->user()->name }} )</p>
                <p class="text-[10px] text-slate-500 font-mono-code">ID / Jabatan: {{ auth()->user()->job_title ?: 'Petugas Logistik' }}</p>
            </div>
        </div>

        <!-- Footer Audit Note -->
        <div class="mt-6 pt-2 border-t border-slate-200 text-[9px] text-slate-400 flex items-center justify-between font-mono-code">
            <span>Sistem LogiScan Pro v12 • LOGISTIK.TOC (Dokumen Resmi Kedinasan)</span>
            <span>Autentikasi: SHA256-{{ substr(md5($item->id . $item->barcode . now()), 0, 12) }}</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode('#stock_card_barcode_svg', '{{ $item->barcode }}', {
                    format: 'CODE128',
                    lineColor: '#0f172a',
                    width: 1.2,
                    height: 28,
                    displayValue: false,
                    margin: 0
                });
            }
        });
    </script>
</body>
</html>
