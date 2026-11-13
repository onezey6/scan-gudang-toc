<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak {{ $handover->document_no }} — Berita Acara Resmi {{ strtoupper($handover->document_type) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 15mm 14mm 15mm;
        }

        @media screen {
            body {
                background-color: #f1f5f9;
                padding: 1.5rem 1rem;
            }
            .a4-sheet {
                max-width: 210mm;
                margin: 0 auto;
                background: #ffffff;
                padding: 18mm 20mm;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
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
                font-size: 10.5pt !important;
                line-height: 1.45 !important;
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
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            table {
                page-break-inside: auto !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            thead {
                display: table-header-group !important;
            }
            tfoot {
                display: table-footer-group !important;
            }
        }
    </style>
</head>
<body class="text-slate-900 font-sans">

    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print max-w-[210mm] mx-auto mb-5 flex flex-wrap items-center justify-between gap-3 p-3.5 bg-slate-900 text-white rounded-xl shadow-md">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('handovers.show', $handover) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition-colors">
                &larr; Kembali ke Detail
            </a>
            <span class="text-xs text-slate-300 font-mono hidden sm:inline">{{ $handover->document_no }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] text-slate-400 hidden md:inline">Siap cetak format A4 presisi (Multi-Halaman)</span>
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold flex items-center gap-1.5 shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- MAIN A4 DOCUMENT CONTAINER -->
    <div class="a4-sheet">

        <!-- KOP SURAT RESMI KORPS BRIMOB POLRI TOC -->
        <div class="border-b-4 border-double border-slate-900 pb-3 mb-4 flex items-center justify-between gap-3 avoid-break">
            <div class="flex items-center gap-3">
                @if (file_exists(public_path('images/logo-toc.jpg')))
                    <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo TOC Gegana" class="w-16 h-16 rounded-full object-cover border border-slate-400 shrink-0">
                @endif
                <div>
                    <div class="text-[12px] font-extrabold uppercase tracking-wider text-slate-800">KORPS BRIMOB POLRI — TACTICAL OPERATION CENTRE (TOC)</div>
                    <div class="text-base sm:text-lg font-extrabold uppercase tracking-wide text-slate-950">DIVISI LOGISTIK & MANAJEMEN INVENTARIS PERALATAN</div>
                    <div class="text-[10px] text-slate-600">Mako Korps Brimob Polri • Sistem Informasi Manajemen Logistik Pro • Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
                </div>
            </div>
            <div class="text-right shrink-0">
                <svg id="doc-barcode" class="max-w-[130px] h-auto"></svg>
            </div>
        </div>

        <!-- JUDUL SURAT -->
        <div class="text-center mb-4 avoid-break">
            <h1 class="text-sm sm:text-base font-extrabold uppercase tracking-wide underline decoration-slate-900">
                @if ($handover->document_type === 'bast')
                    BERITA ACARA SERAH TERIMA & PINJAM PAKAI BARANG LOGISTIK (BAST)
                @else
                    BERITA ACARA PENGEMBALIAN PINJAM PAKAI BARANG LOGISTIK (BAP)
                @endif
            </h1>
            <div class="text-xs font-bold mt-1 font-mono">Nomor Dokumen: {{ $handover->document_no }}</div>
            @if ($handover->reference_letter_no)
                <div class="text-[11px] text-slate-700 mt-0.5">Dasar Nota Dinas / Sprin: <strong>{{ $handover->reference_letter_no }}</strong></div>
            @endif
            @if ($handover->relatedBast)
                <div class="text-[11px] text-slate-600 mt-0.5">Merujuk pada BAST Nomor: <strong>{{ $handover->relatedBast->document_no }}</strong></div>
            @endif
        </div>

        <!-- NARASI PEMBUKA & KEDUA BELAH PIHAK -->
        <p class="text-xs sm:text-[13px] leading-relaxed mb-3">
            Pada hari ini, tanggal <strong>{{ $handover->document_date?->format('d-m-Y') }}</strong>, bertempat di <strong>{{ $handover->operation_location ?: 'Mako Tactical Operation Centre (TOC)' }}</strong>, kami yang bertanda tangan di bawah ini:
        </p>

        <div class="space-y-2 mb-3 text-xs sm:text-[12.5px] pl-1 avoid-break">
            <!-- PIHAK PERTAMA -->
            <div class="grid grid-cols-12 gap-1 pb-1">
                <div class="col-span-3 font-semibold">1. Nama Lengkap</div>
                <div class="col-span-9">: <strong>{{ $handover->first_party_name }}</strong> (NRP/NIP: {{ $handover->first_party_nip ?: '-' }})</div>
                <div class="col-span-3 font-semibold pl-3">Jabatan / Satuan</div>
                <div class="col-span-9">: {{ $handover->first_party_title ?: '-' }} — {{ $handover->first_party_unit ?: '-' }}</div>
                <div class="col-span-12 pl-3 text-[11px] text-slate-600 italic">
                    Selanjutnya disebut sebagai <strong>PIHAK PERTAMA ({{ $handover->document_type === 'bast' ? 'YANG MENYERAHKAN / MEMINJAMKAN' : 'YANG MENGEMBALIKAN' }})</strong>.
                </div>
            </div>

            <!-- PIHAK KEDUA -->
            <div class="grid grid-cols-12 gap-1 pt-1 border-t border-slate-200">
                <div class="col-span-3 font-semibold">2. Nama Lengkap</div>
                <div class="col-span-9">: <strong>{{ $handover->second_party_name }}</strong> (NRP/NIP: {{ $handover->second_party_nip ?: '-' }})</div>
                <div class="col-span-3 font-semibold pl-3">Jabatan / Satuan</div>
                <div class="col-span-9">: {{ $handover->second_party_title ?: '-' }} — {{ $handover->second_party_unit ?: '-' }} (Telp: {{ $handover->second_party_phone ?: '-' }})</div>
                <div class="col-span-12 pl-3 text-[11px] text-slate-600 italic">
                    Selanjutnya disebut sebagai <strong>PIHAK KEDUA ({{ $handover->document_type === 'bast' ? 'YANG MENERIMA / PEMINJAM PAKAI' : 'YANG MENERIMA KEMBALI' }})</strong>.
                </div>
            </div>
        </div>

        <!-- KOTAK RINCIAN KEPERLUAN & BATAS WAKTU -->
        <div class="mb-3.5 p-2.5 rounded border border-slate-400 bg-slate-50 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs avoid-break">
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Jenis Serah Terima:</span>
                <strong>{{ $handover->loan_type_label }}</strong>
            </div>
            <div class="sm:col-span-2">
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Keperluan / Operasi:</span>
                <strong>{{ $handover->purpose ?: 'Dukungan Operasional Dinas' }}</strong>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Batas Pengembalian:</span>
                <strong>{{ $handover->expected_return_date ? $handover->expected_return_date->format('d-m-Y') : '-' }}</strong>
            </div>
        </div>

        <p class="text-xs leading-relaxed mb-2">
            PIHAK PERTAMA telah {{ $handover->document_type === 'bast' ? 'menyerahkan/meminjamkan kepada PIHAK KEDUA' : 'mengembalikan kepada PIHAK KEDUA' }}, dan PIHAK KEDUA menyatakan telah menerima barang inventaris logistik dalam keadaan lengkap dan baik dengan rincian:
        </p>

        <!-- TABEL RINCIAN BARANG INVENTARIS -->
        <table class="w-full text-left border-collapse border border-slate-400 text-xs mb-3.5">
            <thead>
                <tr class="bg-slate-100 text-slate-900">
                    <th class="border border-slate-400 py-1.5 px-2 text-center w-8">No</th>
                    <th class="border border-slate-400 py-1.5 px-2">Kode & Nama Barang</th>
                    <th class="border border-slate-400 py-1.5 px-2">Barcode & No. Seri (S/N)</th>
                    <th class="border border-slate-400 py-1.5 px-2 text-center">Jumlah</th>
                    <th class="border border-slate-400 py-1.5 px-2">Kelengkapan / Aksesoris</th>
                    <th class="border border-slate-400 py-1.5 px-2 text-center">Kondisi & Status</th>
                    <th class="border border-slate-400 py-1.5 px-2">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($handover->items as $idx => $hi)
                    <tr class="avoid-break">
                        <td class="border border-slate-400 py-1.5 px-2 text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="border border-slate-400 py-1.5 px-2">
                            <div class="font-bold text-slate-900">{{ $hi->item?->name }}</div>
                            <div class="text-[10px] text-slate-600 font-mono">{{ $hi->item?->item_code }}</div>
                        </td>
                        <td class="border border-slate-400 py-1.5 px-2 font-mono text-[11px]">
                            <div class="font-semibold">{{ $hi->item?->barcode }}</div>
                            @if ($hi->serial_number)
                                <div class="text-[10px] text-slate-600">S/N: {{ $hi->serial_number }}</div>
                            @endif
                        </td>
                        <td class="border border-slate-400 py-1.5 px-2 text-center font-bold whitespace-nowrap">{{ $hi->quantity }} {{ $hi->item?->unit }}</td>
                        <td class="border border-slate-400 py-1.5 px-2">{{ $hi->accessories ?: 'Lengkap Standar' }}</td>
                        <td class="border border-slate-400 py-1.5 px-2 text-center">
                            <div class="font-semibold">{{ $hi->condition_status }}</div>
                            <div class="text-[10px] text-slate-600">({{ $hi->availability_label }})</div>
                        </td>
                        <td class="border border-slate-400 py-1.5 px-2 text-[11px]">{{ $hi->notes ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- KLAUSUL PASAL KETENTUAN KEDINASAN PINJAM PAKAI / PENGEMBALIAN -->
        @if ($handover->terms_conditions)
            <div class="mb-3.5 p-2.5 rounded border border-slate-400 bg-slate-50/70 text-[11px] leading-relaxed avoid-break">
                <div class="font-bold uppercase underline mb-1 text-slate-900">
                    {{ $handover->document_type === 'bap' ? 'KETENTUAN & SYARAT PENGEMBALIAN BARANG INVENTARIS (BAP):' : 'KETENTUAN & KEWAJIBAN PINJAM PAKAI BARANG INVENTARIS (BAST):' }}
                </div>
                <div class="whitespace-pre-line text-slate-800">{{ $handover->terms_conditions }}</div>
            </div>
        @endif

        @if ($handover->notes)
            <div class="mb-3 p-2 rounded border border-slate-300 bg-slate-50 text-xs avoid-break">
                <strong>Catatan Pemeriksaan Fisik:</strong> {{ $handover->notes }}
            </div>
        @endif

        <p class="text-xs leading-relaxed mb-4 avoid-break">
            Demikian Berita Acara ini dibuat dengan sebenarnya dalam rangkap secukupnya dan ditandatangani oleh kedua belah pihak untuk dapat dipergunakan sebagaimana mestinya.
        </p>

        <!-- BLOK TANDA TANGAN (KEDUA PIHAK & PEJABAT MENGETAHUI) -->
        <div class="avoid-break pt-2">
            <div class="grid grid-cols-2 gap-8 text-center text-xs">
                <!-- Pihak Kedua -->
                <div>
                    <div class="font-bold uppercase text-slate-900">PIHAK KEDUA</div>
                    <div class="text-[11px] text-slate-600 mb-1">({{ $handover->document_type === 'bast' ? 'Yang Menerima / Peminjam' : 'Yang Menerima Kembali' }})</div>
                    <div class="h-16 flex items-center justify-center my-1">
                        @if ($handover->second_party_signature)
                            <img src="{{ $handover->second_party_signature }}" alt="Tanda Tangan Pihak Kedua" class="max-h-14 mx-auto">
                        @else
                            <div class="border-b border-dashed border-slate-400 w-36"></div>
                        @endif
                    </div>
                    <div class="font-bold underline text-slate-950">{{ $handover->second_party_name }}</div>
                    <div class="text-[11px] text-slate-600">NRP/NIP: {{ $handover->second_party_nip ?: '-' }}</div>
                </div>

                <!-- Pihak Pertama -->
                <div>
                    <div class="font-bold uppercase text-slate-900">PIHAK PERTAMA</div>
                    <div class="text-[11px] text-slate-600 mb-1">({{ $handover->document_type === 'bast' ? 'Yang Menyerahkan' : 'Yang Mengembalikan' }})</div>
                    <div class="h-16 flex items-center justify-center my-1">
                        @if ($handover->first_party_signature)
                            <img src="{{ $handover->first_party_signature }}" alt="Tanda Tangan Pihak Pertama" class="max-h-14 mx-auto">
                        @else
                            <div class="border-b border-dashed border-slate-400 w-36"></div>
                        @endif
                    </div>
                    <div class="font-bold underline text-slate-950">{{ $handover->first_party_name }}</div>
                    <div class="text-[11px] text-slate-600">NRP/NIP: {{ $handover->first_party_nip ?: '-' }}</div>
                </div>
            </div>

            @if ($handover->approver_name)
                <div class="mt-4 pt-2 text-center text-xs">
                    <div class="text-[11px] uppercase font-bold text-slate-600">MENGETAHUI / MENYETUJUI:</div>
                    <div class="font-bold uppercase text-slate-900">{{ $handover->approver_title ?: 'KEPALA LOGISTIK TOC' }}</div>
                    <div class="h-14 flex items-center justify-center my-1">
                        <div class="border-b border-dashed border-slate-400 w-44"></div>
                    </div>
                    <div class="font-bold underline text-slate-950">{{ $handover->approver_name }}</div>
                    <div class="text-[11px] text-slate-600">NRP/NIP: {{ $handover->approver_nip ?: '-' }}</div>
                </div>
            @endif
        </div>

        <!-- LAMPIRAN FOTO BUKTI DOKUMENTASI (HALAMAN LANJUTAN / MULTI-HALAMAN RAPI) -->
        @if ($handover->photos->count() > 0)
            <div class="page-break pt-6">
                <!-- Header Lampiran Halaman Lanjutan -->
                <div class="border-b-2 border-slate-900 pb-2.5 mb-4 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">LAMPIRAN DOKUMEN RESMI BAST/BAP</div>
                        <h2 class="text-sm font-extrabold uppercase text-slate-900">
                            DOKUMENTASI FOTO BUKTI FISIK BARANG ({{ $handover->photos->count() }} FOTO)
                        </h2>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-mono font-bold">{{ $handover->document_no }}</div>
                        <div class="text-[10px] text-slate-500">{{ $handover->document_date?->format('d/m/Y') }}</div>
                    </div>
                </div>

                <!-- Grid Lampiran Foto Anti-Break -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($handover->photos as $pIdx => $photo)
                        <div class="avoid-break p-2 rounded-lg border border-slate-300 bg-white text-center shadow-2xs">
                            <div class="w-full h-40 bg-slate-100 rounded overflow-hidden mb-1.5 flex items-center justify-center">
                                <img src="{{ $photo->photo_url }}" alt="Foto Bukti" class="w-full h-full object-cover">
                            </div>
                            <div class="text-[11px] font-bold text-slate-900 truncate">
                                {{ $photo->caption ?: 'Dokumentasi Bukti #' . ($pIdx + 1) }}
                            </div>
                            <div class="text-[9px] text-slate-400">
                                Diunggah: {{ $photo->created_at?->format('d/m/Y H:i') }} WIB
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Print Footer Notes -->
        <div class="mt-6 pt-3 border-t border-slate-300 text-[9px] text-slate-500 flex items-center justify-between avoid-break">
            <span>Sistem Informasi Logistik Pro • Korps Brimob Polri Tactical Operation Centre</span>
            <span>Dokumen Terverifikasi Barcode • {{ $handover->document_no }}</span>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode('#doc-barcode', '{{ $handover->document_no }}', {
                    format: 'CODE128',
                    width: 1.2,
                    height: 32,
                    fontSize: 9,
                    margin: 0
                });
            }
        });
    </script>
</body>
</html>
