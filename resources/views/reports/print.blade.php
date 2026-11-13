<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Barang Masuk & Keluar — LogiScan Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 p-4 sm:p-8">
    <div class="max-w-6xl mx-auto bg-white p-6 sm:p-10 rounded-xl shadow-sm border border-slate-200">
        <div class="no-print mb-6 flex items-center justify-between pb-4 border-b border-slate-200">
            <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali ke Laporan</a>
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold">
                Cetak / Simpan PDF Sekarang
            </button>
        </div>

        <!-- Kop Laporan -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold uppercase tracking-tight">LAPORAN MUTASI BARANG MASUK & KELUAR (SCAN BARCODE)</h1>
                <p class="text-xs text-slate-600 mt-1">Sistem Manajemen Gudang Penyimpanan & Logistik — <strong>LogiScan Pro</strong></p>
            </div>
            <div class="text-right text-xs text-slate-600">
                <div>Tanggal Cetak: <strong>{{ now()->format('d/m/Y H:i') }} WIB</strong></div>
                <div>Dicetak Oleh: <strong>{{ auth()->user()->name }}</strong></div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="grid grid-cols-3 gap-4 mb-6 text-xs">
            <div class="p-3 rounded-lg border border-slate-300">
                <div class="text-slate-500 uppercase font-semibold">Total Transaksi Scan</div>
                <div class="text-lg font-bold mt-0.5">{{ number_format($summary['total_transactions']) }} Transaksi</div>
            </div>
            <div class="p-3 rounded-lg border border-slate-300">
                <div class="text-emerald-700 uppercase font-semibold">Total Barang Masuk (IN)</div>
                <div class="text-lg font-bold text-emerald-700 mt-0.5">+{{ number_format($summary['in_qty']) }} Unit ({{ $summary['in_transactions'] }}x)</div>
            </div>
            <div class="p-3 rounded-lg border border-slate-300">
                <div class="text-amber-700 uppercase font-semibold">Total Barang Keluar (OUT)</div>
                <div class="text-lg font-bold text-amber-700 mt-0.5">-{{ number_format($summary['out_qty']) }} Unit ({{ $summary['out_transactions'] }}x)</div>
            </div>
        </div>

        <!-- Tabel Laporan -->
        <table class="w-full text-left border-collapse border border-slate-300 text-xs">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-300 py-2 px-2.5">No</th>
                    <th class="border border-slate-300 py-2 px-2.5">No. Referensi & Waktu</th>
                    <th class="border border-slate-300 py-2 px-2.5">Barcode & Nama Barang</th>
                    <th class="border border-slate-300 py-2 px-2.5">Jenis</th>
                    <th class="border border-slate-300 py-2 px-2.5">Qty</th>
                    <th class="border border-slate-300 py-2 px-2.5">Stok Akhir</th>
                    <th class="border border-slate-300 py-2 px-2.5">Sumber / Tujuan</th>
                    <th class="border border-slate-300 py-2 px-2.5">Petugas Scan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movements as $idx => $m)
                    <tr>
                        <td class="border border-slate-300 py-2 px-2.5">{{ $idx + 1 }}</td>
                        <td class="border border-slate-300 py-2 px-2.5">
                            <div class="font-bold">{{ $m->reference_no }}</div>
                            <div class="text-[11px] text-slate-600">{{ $m->scanned_at?->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="border border-slate-300 py-2 px-2.5">
                            <div class="font-bold">{{ $m->item?->name }}</div>
                            <div class="text-[11px] text-slate-600">Barcode: {{ $m->item?->barcode }}</div>
                        </td>
                        <td class="border border-slate-300 py-2 px-2.5 font-bold {{ $m->movement_type === 'in' ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $m->movement_type === 'in' ? 'MASUK' : 'KELUAR' }}
                        </td>
                        <td class="border border-slate-300 py-2 px-2.5 font-bold">
                            {{ $m->movement_type === 'in' ? '+' : '-' }}{{ $m->quantity }} {{ $m->item?->unit }}
                        </td>
                        <td class="border border-slate-300 py-2 px-2.5">{{ $m->stock_after }} {{ $m->item?->unit }}</td>
                        <td class="border border-slate-300 py-2 px-2.5">
                            <div>{{ $m->source_destination }}</div>
                            <div class="text-[11px] text-slate-500">Pihak: {{ $m->partner_name }}</div>
                        </td>
                        <td class="border border-slate-300 py-2 px-2.5">
                            <div class="font-semibold">{{ $m->user?->name ?? 'Sistem' }}</div>
                            <div class="text-[10px] text-slate-500">Pembuat Brg: {{ $m->item?->creator?->name ?? 'Admin' }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
