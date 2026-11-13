@extends('layouts.app')

@section('title', 'Berita Acara Serah Terima & Pengembalian')
@section('page_title', 'Berita Acara Serah Terima (BAST) & Pengembalian (BAP)')
@section('page_subtitle', 'Kelola dokumen resmi penyerahan dan pengembalian barang logistik lengkap dengan tanda tangan dan cetak surat')

@section('content')
    <!-- Summary & Action Buttons -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-1">
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold uppercase text-slate-500">Total BAST</div>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $summary['total_bast'] }}</div>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold uppercase text-amber-700">BAST Aktif (Dipinjam)</div>
                <div class="text-xl font-bold text-amber-600 mt-0.5">{{ $summary['active_bast'] }}</div>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold uppercase text-emerald-700">Sudah Dikembalikan</div>
                <div class="text-xl font-bold text-emerald-600 mt-0.5">{{ $summary['returned_bast'] }}</div>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold uppercase text-indigo-700">Total BAP Pengembalian</div>
                <div class="text-xl font-bold text-indigo-600 mt-0.5">{{ $summary['total_bap'] }}</div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('handovers.create', ['type' => 'bast']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs sm:text-sm font-semibold shadow-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Berita Acara Serah Terima (BAST)</span>
            </a>

            <a href="{{ route('handovers.create', ['type' => 'bap']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Buat Berita Acara Pengembalian (BAP)</span>
            </a>
        </div>
    </div>

    <!-- Sinkronisasi Status Ketersediaan Barang di Menu BAST & BAP -->
    <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Sinkronisasi Status Barang Gudang & Pinjam Pakai:</span>
            <a href="{{ route('items.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat Semua Barang &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <a href="{{ route('items.index', ['availability_status' => 'sedia']) }}" class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800">● Sedia</span>
                <strong class="text-lg font-extrabold text-emerald-700">{{ $summary['status_sedia'] ?? 0 }}</strong>
            </a>
            <a href="{{ route('items.index', ['availability_status' => 'sedang_keluar']) }}" class="p-3 rounded-xl border border-amber-200 bg-amber-50/50 flex items-center justify-between">
                <span class="text-xs font-bold text-amber-800">● Sedang Keluar</span>
                <strong class="text-lg font-extrabold text-amber-700">{{ $summary['status_sedang_keluar'] ?? 0 }}</strong>
            </a>
            <a href="{{ route('items.index', ['availability_status' => 'sedang_dipakai']) }}" class="p-3 rounded-xl border border-cyan-200 bg-cyan-50/50 flex items-center justify-between">
                <span class="text-xs font-bold text-cyan-800">● Sedang Dipakai</span>
                <strong class="text-lg font-extrabold text-cyan-700">{{ $summary['status_sedang_dipakai'] ?? 0 }}</strong>
            </a>
            <a href="{{ route('items.index', ['availability_status' => 'sedang_dipinjam']) }}" class="p-3 rounded-xl border border-indigo-200 bg-indigo-50/50 flex items-center justify-between">
                <span class="text-xs font-bold text-indigo-800">● Sedang Dipinjam</span>
                <strong class="text-lg font-extrabold text-indigo-700">{{ $summary['status_sedang_dipinjam'] ?? 0 }}</strong>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('handovers.index') }}" method="GET" class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Cari Nomor Dokumen / Nama Pihak</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Contoh: BAST-202611-001 atau nama penerima/pengembali..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Jenis Berita Acara</label>
                <select name="document_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white">
                    <option value="">Semua (BAST & BAP)</option>
                    <option value="bast" {{ request('document_type') === 'bast' ? 'selected' : '' }}>BAST — Berita Acara Serah Terima</option>
                    <option value="bap" {{ request('document_type') === 'bap' ? 'selected' : '' }}>BAP — Berita Acara Pengembalian</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold">
                    Filter
                </button>
                <a href="{{ route('handovers.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs sm:text-sm font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Handover Documents Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fluid">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Nomor & Jenis Dokumen</th>
                        <th class="py-3.5 px-4">Pihak Pertama (Penyerah)</th>
                        <th class="py-3.5 px-4">Pihak Kedua (Penerima)</th>
                        <th class="py-3.5 px-4">Daftar Barang</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 text-sm">
                    @forelse ($documents as $doc)
                        <tr class="hover:bg-slate-50/90 transition-colors">
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-mono-code text-xs font-bold text-slate-900">{{ $doc->document_no }}</div>
                                @if ($doc->document_type === 'bast')
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        SERAH TERIMA (BAST)
                                    </span>
                                    <div class="text-[11px] font-semibold text-indigo-600 mt-0.5">{{ $doc->loan_type_label }}</div>
                                @else
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        PENGEMBALIAN (BAP)
                                    </span>
                                @endif
                                <div class="text-xs text-slate-500 mt-1">Tanggal: {{ $doc->document_date?->format('d/m/Y') }}</div>
                                @if ($doc->expected_return_date && $doc->document_type === 'bast')
                                    <div class="text-[11px] text-amber-700 font-medium">Batas Kembali: {{ $doc->expected_return_date->format('d/m/Y') }}</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">{{ $doc->first_party_name }}</div>
                                <div class="text-xs text-slate-500">{{ $doc->first_party_title ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $doc->first_party_unit ?: '-' }}</div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">{{ $doc->second_party_name }}</div>
                                <div class="text-xs text-slate-500">{{ $doc->second_party_title ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $doc->second_party_unit ?: '-' }}</div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                @if ($doc->purpose)
                                    <div class="text-[11px] font-bold text-emerald-700 mb-1">Keperluan: {{ $doc->purpose }}</div>
                                @endif
                                <ul class="space-y-1 text-xs">
                                    @foreach ($doc->items as $hi)
                                        <li class="text-slate-700 flex flex-wrap items-center gap-1">
                                            <span>• <strong>{{ $hi->quantity }} {{ $hi->item?->unit }}</strong> {{ $hi->item?->name }}</span>
                                            <span class="text-slate-400">({{ $hi->condition_status }})</span>
                                            @if ($hi->item)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $hi->item->availability_label }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($doc->photos->count() > 0)
                                    <div class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-700 border border-cyan-200 text-[10px] font-bold">
                                        📷 {{ $doc->photos->count() }} Foto Bukti
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                @if ($doc->document_type === 'bast' && $doc->status === 'active')
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Diserahkan / Aktif
                                    </span>
                                @elseif ($doc->status === 'returned')
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sudah Dikembalikan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        Selesai (BAP)
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-400 mt-1">Petugas: {{ $doc->user?->name ?? 'Admin' }}</div>
                            </td>

                            <td class="py-3.5 px-4 align-top text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('handovers.show', $doc) }}"
                                       class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                                        Detail
                                    </a>
                                    <a href="{{ route('handovers.print', $doc) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold">
                                        Cetak PDF
                                    </a>
                                    @if ($doc->document_type === 'bast' && $doc->status === 'active')
                                        <a href="{{ route('handovers.create', ['type' => 'bap', 'bast_id' => $doc->id]) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                                            Buat BAP Kembali
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-slate-500">
                                Belum ada dokumen Berita Acara Serah Terima atau Pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
@endsection
