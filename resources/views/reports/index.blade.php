@extends('layouts.app')

@section('title', 'Laporan Barang Keluar & Masuk')
@section('page_title', 'Laporan Barang Masuk & Keluar via Scan Barcode')
@section('page_subtitle', 'Rekapitulasi transaksi hasil scan barcode beserta akuntabilitas petugas yang memasukkan dan mengeluarkan barang')

@section('content')
    <!-- Summary Cards Grid (2–4 Columns) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-slate-500">Total Frekuensi Scan</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['total_transactions']) }}x</div>
            <div class="text-xs text-slate-500">Transaksi sesuai filter aktif</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-emerald-700">Total Barang Masuk (Inbound)</div>
            <div class="mt-1 text-2xl font-bold text-emerald-600">+{{ number_format($summary['in_qty']) }}</div>
            <div class="text-xs text-slate-500">Dari {{ number_format($summary['in_transactions']) }}x scan masuk</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-amber-700">Total Barang Keluar (Outbound)</div>
            <div class="mt-1 text-2xl font-bold text-amber-600">-{{ number_format($summary['out_qty']) }}</div>
            <div class="text-xs text-slate-500">Dari {{ number_format($summary['out_transactions']) }}x scan keluar</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="text-[11px] font-semibold uppercase text-slate-500">Unduh & Cetak Laporan</div>
            <div class="mt-2 flex items-center gap-2">
                <a href="{{ route('reports.print', request()->query()) }}" target="_blank"
                   class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold text-center">
                    Cetak / PDF
                </a>
                <a href="{{ route('reports.export-csv', request()->query()) }}"
                   class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold text-center">
                    Export Excel/CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <form action="{{ route('reports.index') }}" method="GET" class="mb-6 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Jenis Mutasi</label>
                <select name="movement_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white">
                    <option value="">Semua (Masuk & Keluar)</option>
                    <option value="in" {{ request('movement_type') === 'in' ? 'selected' : '' }}>Barang Masuk (IN)</option>
                    <option value="out" {{ request('movement_type') === 'out' ? 'selected' : '' }}>Barang Keluar (OUT)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Barang</label>
                <select name="item_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white">
                    <option value="">Semua Barang</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" {{ (string) request('item_id') === (string) $item->id ? 'selected' : '' }}>
                            {{ $item->name }} ({{ $item->barcode }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Petugas Scan (User)</label>
                <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white">
                    <option value="">Semua Petugas</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold">
                    Terapkan
                </button>
                <a href="{{ route('reports.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs sm:text-sm font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Report Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fluid">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">No. Referensi & Waktu</th>
                        <th class="py-3.5 px-4">Barang & Barcode</th>
                        <th class="py-3.5 px-4">Status & Jumlah</th>
                        <th class="py-3.5 px-4">Sumber / Tujuan & Partner</th>
                        <th class="py-3.5 px-4">Petugas Scan & Pembuat Barang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 text-sm">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/90 transition-colors">
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-mono-code text-xs font-bold text-slate-900">{{ $m->reference_no }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $m->scanned_at?->format('d/m/Y H:i:s') }} WIB</div>
                                <div class="text-[11px] text-slate-400">Via: {{ strtoupper($m->scan_method) }}</div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="flex items-start gap-2.5">
                                    @if ($m->item?->photo_url)
                                        <button type="button" onclick="openPhotoLightbox('{{ $m->item->photo_url }}', '{{ addslashes($m->item->name) }}')"
                                                class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                            <img src="{{ $m->item->photo_url }}" alt="{{ $m->item->name }}" class="w-full h-full object-cover">
                                        </button>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $m->item?->name ?? '-' }}</div>
                                        <div class="text-xs font-mono-code text-emerald-700 mt-0.5">
                                            Barcode: {{ $m->item?->barcode ?? '-' }} ({{ $m->item?->item_code ?? '-' }})
                                        </div>
                                        <div class="text-[11px] text-slate-500">{{ $m->item?->category?->name ?? 'Tanpa Kategori' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                @if ($m->movement_type === 'in')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        MASUK +{{ $m->quantity }} {{ $m->item?->unit }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        KELUAR -{{ $m->quantity }} {{ $m->item?->unit }}
                                    </span>
                                @endif
                                <div class="text-xs text-slate-600 mt-1">
                                    Stok: {{ $m->stock_before }} &rarr; <strong class="text-slate-900">{{ $m->stock_after }} {{ $m->item?->unit }}</strong>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="text-xs font-semibold text-slate-800">{{ $m->source_destination ?: '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">Pihak Terkait: {{ $m->partner_name ?: '-' }}</div>
                                @if ($m->notes)
                                    <div class="text-[11px] text-slate-500 italic mt-0.5">"{{ $m->notes }}"</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="text-xs">
                                    <span class="text-slate-400">Scan Oleh:</span>
                                    <strong class="text-slate-900">{{ $m->user?->name ?? 'Sistem' }}</strong>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1">
                                    <span class="text-slate-400">Pembuat Barang:</span>
                                    <span class="font-medium text-slate-700">{{ $m->item?->creator?->name ?? 'Admin' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-slate-500">
                                Tidak ada data transaksi barang masuk/keluar pada rentang filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
@endsection
