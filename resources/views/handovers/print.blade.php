<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak {{ $handover->document_no }} — Berita Acara Resmi Pinjam Pakai & Serah Terima</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 sm:p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-11 rounded-xl border border-slate-300 shadow-sm">
        <div class="no-print mb-6 pb-4 border-b border-slate-200 flex items-center justify-between">
            <a href="{{ route('handovers.show', $handover) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                &larr; Kembali ke Detail Dokumen
            </a>
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold">
                Cetak / Simpan PDF Surat Berita Acara
            </button>
        </div>

        <!-- KOP SURAT RESMI -->
        <div class="border-b-4 border-double border-slate-900 pb-4 mb-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                @if (file_exists(public_path('images/logo-toc.jpg')))
                    <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo Korps Brimob Polri TOC" class="w-16 h-16 rounded-full object-cover border border-slate-300 shrink-0">
                @endif
                <div>
                    <div class="text-sm font-extrabold uppercase tracking-wider text-slate-800">KORPS BRIMOB POLRI — TACTICAL OPERATION CENTRE (TOC)</div>
                    <div class="text-base sm:text-lg font-extrabold uppercase tracking-wide text-slate-950">DIVISI LOGISTIK & MANAJEMEN INVENTARIS PERALATAN</div>
                    <div class="text-[11px] text-slate-600">Dokumen Resmi Terverifikasi Barcode Sistem • Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
                </div>
            </div>
            <div class="text-right shrink-0">
                <svg id="doc-barcode"></svg>
            </div>
        </div>

        <!-- JUDUL SURAT -->
        <div class="text-center mb-5">
            <h1 class="text-base sm:text-lg font-extrabold uppercase underline tracking-wide">
                @if ($handover->document_type === 'bast')
                    BERITA ACARA SERAH TERIMA & PINJAM PAKAI BARANG LOGISTIK (BAST)
                @else
                    BERITA ACARA PENGEMBALIAN PINJAM PAKAI BARANG LOGISTIK (BAP)
                @endif
            </h1>
            <div class="text-xs font-bold mt-1">Nomor Dokumen: {{ $handover->document_no }}</div>
            @if ($handover->reference_letter_no)
                <div class="text-[11px] text-slate-700 mt-0.5">Dasar Surat Perintah / Nota Dinas: <strong>{{ $handover->reference_letter_no }}</strong></div>
            @endif
            @if ($handover->relatedBast)
                <div class="text-[11px] text-slate-600 mt-0.5">Merujuk pada BAST Nomor: <strong>{{ $handover->relatedBast->document_no }}</strong></div>
            @endif
        </div>

        <!-- NARASI PEMBUKA -->
        <p class="text-xs sm:text-sm leading-relaxed mb-3">
            Pada hari ini, tanggal <strong>{{ $handover->document_date?->format('d-m-Y') }}</strong>, bertempat di <strong>{{ $handover->operation_location ?: 'Mako Tactical Operation Centre (TOC)' }}</strong>, kami yang bertanda tangan di bawah ini:
        </p>

        <div class="space-y-2.5 mb-4 text-xs sm:text-sm pl-2">
            <div class="grid grid-cols-12 gap-1">
                <div class="col-span-3 font-semibold">1. Nama Lengkap</div>
                <div class="col-span-9">: <strong>{{ $handover->first_party_name }}</strong> (NRP/NIP: {{ $handover->first_party_nip ?: '-' }})</div>
                <div class="col-span-3 font-semibold pl-3">Jabatan / Satuan</div>
                <div class="col-span-9">: {{ $handover->first_party_title ?: '-' }} — {{ $handover->first_party_unit ?: '-' }}</div>
                <div class="col-span-12 pl-3 text-xs text-slate-600">
                    Selanjutnya disebut sebagai <strong>PIHAK PERTAMA ({{ $handover->document_type === 'bast' ? 'YANG MENYERAHKAN / MEMINJAMKAN' : 'YANG MENGEMBALIKAN' }})</strong>.
                </div>
            </div>

            <div class="grid grid-cols-12 gap-1 pt-1.5">
                <div class="col-span-3 font-semibold">2. Nama Lengkap</div>
                <div class="col-span-9">: <strong>{{ $handover->second_party_name }}</strong> (NRP/NIP: {{ $handover->second_party_nip ?: '-' }})</div>
                <div class="col-span-3 font-semibold pl-3">Jabatan / Satuan</div>
                <div class="col-span-9">: {{ $handover->second_party_title ?: '-' }} — {{ $handover->second_party_unit ?: '-' }} (Telp: {{ $handover->second_party_phone ?: '-' }})</div>
                <div class="col-span-12 pl-3 text-xs text-slate-600">
                    Selanjutnya disebut sebagai <strong>PIHAK KEDUA ({{ $handover->document_type === 'bast' ? 'YANG MENERIMA / PEMINJAM PAKAI' : 'YANG MENERIMA KEMBALI' }})</strong>.
                </div>
            </div>
        </div>

        <!-- KOTAK RINCIAN KEPERLUAN & BATAS WAKTU PINJAM PAKAI -->
        <div class="mb-4 p-3 rounded border border-slate-400 bg-slate-50 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Jenis Serah Terima:</span>
                <strong>{{ $handover->loan_type_label }}</strong>
            </div>
            <div class="sm:col-span-2">
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Keperluan / Kegiatan Operasi:</span>
                <strong>{{ $handover->purpose ?: 'Dukungan Operasional Dinas' }}</strong>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Batas Pengembalian:</span>
                <strong>{{ $handover->expected_return_date ? $handover->expected_return_date->format('d-m-Y') : '-' }}</strong>
            </div>
        </div>

        <p class="text-xs sm:text-sm leading-relaxed mb-3">
            PIHAK PERTAMA telah {{ $handover->document_type === 'bast' ? 'menyerahkan/meminjamkan kepada PIHAK KEDUA' : 'mengembalikan kepada PIHAK KEDUA' }}, dan PIHAK KEDUA menyatakan telah menerima barang inventaris logistik dengan rincian sebagai berikut:
        </p>

        <!-- TABEL BARANG -->
        <table class="w-full text-left border-collapse border border-slate-400 text-xs mb-4">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-400 py-2 px-2 text-center w-8">No</th>
                    <th class="border border-slate-400 py-2 px-2">Kode & Nama Barang</th>
                    <th class="border border-slate-400 py-2 px-2">Barcode & No. Seri (S/N)</th>
                    <th class="border border-slate-400 py-2 px-2 text-center">Jumlah</th>
                    <th class="border border-slate-400 py-2 px-2">Kelengkapan / Aksesoris</th>
                    <th class="border border-slate-400 py-2 px-2 text-center">Kondisi & Status</th>
                    <th class="border border-slate-400 py-2 px-2">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($handover->items as $idx => $hi)
                    <tr>
                        <td class="border border-slate-400 py-2 px-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-slate-400 py-2 px-2">
                            <div class="font-bold">{{ $hi->item?->name }}</div>
                            <div class="text-[10px] text-slate-600">{{ $hi->item?->item_code }}</div>
                        </td>
                        <td class="border border-slate-400 py-2 px-2 font-mono text-[11px]">
                            <div class="font-semibold">{{ $hi->item?->barcode }}</div>
                            @if ($hi->serial_number)
                                <div class="text-[10px] text-slate-600">S/N: {{ $hi->serial_number }}</div>
                            @endif
                        </td>
                        <td class="border border-slate-400 py-2 px-2 text-center font-bold">{{ $hi->quantity }} {{ $hi->item?->unit }}</td>
                        <td class="border border-slate-400 py-2 px-2">{{ $hi->accessories ?: 'Lengkap Standar' }}</td>
                        <td class="border border-slate-400 py-2 px-2 text-center">
                            <div class="font-semibold">{{ $hi->condition_status }}</div>
                            <div class="text-[10px] text-slate-600">({{ $hi->availability_label }})</div>
                        </td>
                        <td class="border border-slate-400 py-2 px-2">{{ $hi->notes ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- PASAL KETENTUAN & KEWAJIBAN PINJAM PAKAI -->
        @if ($handover->terms_conditions)
            <div class="mb-4 p-3 rounded border border-slate-400 bg-slate-50/70 text-[11px] leading-relaxed">
                <div class="font-bold uppercase underline mb-1">KETENTUAN & KEWAJIBAN PINJAM PAKAI BARANG INVENTARIS:</div>
                <div class="whitespace-pre-line">{{ $handover->terms_conditions }}</div>
            </div>
        @endif

        @if ($handover->notes)
            <div class="mb-4 p-2.5 rounded border border-slate-300 bg-slate-50 text-xs">
                <strong>Catatan Pemeriksaan Fisik:</strong> {{ $handover->notes }}
            </div>
        @endif

        <p class="text-xs leading-relaxed mb-6">
            Demikian Berita Acara ini dibuat dengan sebenarnya dalam rangkap secukupnya dan ditandatangani oleh kedua belah pihak untuk dapat dipergunakan sebagaimana mestinya.
        </p>

        <!-- BLOK TANDA TANGAN KEDUA PIHAK -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs sm:text-sm mt-4">
            <div>
                <div class="font-bold uppercase">PIHAK KEDUA</div>
                <div class="text-xs text-slate-600 mb-2">({{ $handover->document_type === 'bast' ? 'Yang Menerima / Peminjam' : 'Yang Menerima Kembali' }})</div>
                <div class="h-20 flex items-center justify-center">
                    @if ($handover->second_party_signature)
                        <img src="{{ $handover->second_party_signature }}" alt="Tanda Tangan Pihak Kedua" class="max-h-16 mx-auto">
                    @endif
                </div>
                <div class="font-bold underline">{{ $handover->second_party_name }}</div>
                <div class="text-xs text-slate-600">NRP/NIP: {{ $handover->second_party_nip ?: '-' }}</div>
            </div>

            <div>
                <div class="font-bold uppercase">PIHAK PERTAMA</div>
                <div class="text-xs text-slate-600 mb-2">({{ $handover->document_type === 'bast' ? 'Yang Menyerahkan' : 'Yang Mengembalikan' }})</div>
                <div class="h-20 flex items-center justify-center">
                    @if ($handover->first_party_signature)
                        <img src="{{ $handover->first_party_signature }}" alt="Tanda Tangan Pihak Pertama" class="max-h-16 mx-auto">
                    @endif
                </div>
                <div class="font-bold underline">{{ $handover->first_party_name }}</div>
                <div class="text-xs text-slate-600">NRP/NIP: {{ $handover->first_party_nip ?: '-' }}</div>
            </div>
        </div>

        @if ($handover->approver_name)
            <div class="mt-6 pt-4 text-center text-xs sm:text-sm">
                <div class="text-xs uppercase font-semibold text-slate-600">MENGETAHUI / MENYETUJUI:</div>
                <div class="font-bold uppercase">{{ $handover->approver_title ?: 'KEPALA LOGISTIK TOC' }}</div>
                <div class="h-16"></div>
                <div class="font-bold underline">{{ $handover->approver_name }}</div>
                <div class="text-xs text-slate-600">NRP/NIP: {{ $handover->approver_nip ?: '-' }}</div>
            </div>
        @endif

        <!-- LAMPIRAN FOTO BUKTI DOKUMENTASI (JIKA ADA) -->
        @if ($handover->photos->count() > 0)
            <div class="mt-8 pt-6 border-t-2 border-dashed border-slate-300 break-inside-avoid">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-3">
                    LAMPIRAN DOKUMENTASI FOTO BUKTI ({{ $handover->photos->count() }} FOTO) — {{ $handover->document_no }}
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($handover->photos as $photo)
                        <div class="p-2 rounded border border-slate-300 text-center break-inside-avoid">
                            <img src="{{ $photo->photo_url }}" alt="Foto Bukti" class="w-full h-32 object-cover rounded mb-1">
                            <div class="text-[10px] font-semibold text-slate-700">{{ $photo->caption ?: 'Bukti Fisik Barang' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode('#doc-barcode', '{{ $handover->document_no }}', {
                    format: 'CODE128',
                    width: 1.3,
                    height: 34,
                    fontSize: 10,
                    margin: 0
                });
            }
        });
    </script>
</body>
</html>
