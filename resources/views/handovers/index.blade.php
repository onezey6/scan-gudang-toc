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

    <!-- Sinkronisasi Status Ketersediaan Barang di Menu BAST & BAP (Tactical Cyber HUD) -->
    @php
        $totalHandoverCount = max(1, ($summary['status_sedia'] ?? 0) + ($summary['status_sedang_keluar'] ?? 0) + ($summary['status_sedang_dipakai'] ?? 0) + ($summary['status_sedang_dipinjam'] ?? 0));
        $pctHSedia = round((($summary['status_sedia'] ?? 0) / $totalHandoverCount) * 100);
        $pctHKeluar = round((($summary['status_sedang_keluar'] ?? 0) / $totalHandoverCount) * 100);
        $pctHDipakai = round((($summary['status_sedang_dipakai'] ?? 0) / $totalHandoverCount) * 100);
        $pctHDipinjam = round((($summary['status_sedang_dipinjam'] ?? 0) / $totalHandoverCount) * 100);
    @endphp
    <div class="mb-6 rounded-2xl border-2 border-cyan-500/30 bg-[#070D18] shadow-lg overflow-hidden">
        <!-- Header Panel -->
        <div class="p-3.5 sm:p-4 bg-gradient-to-r from-[#070D18] via-[#0B1528] to-[#070D18] border-b border-cyan-500/20 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 border border-cyan-400/50 flex items-center justify-center text-cyan-300 shadow-[0_0_12px_rgba(34,211,238,0.3)] shrink-0">
                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-base font-extrabold uppercase tracking-wide text-white">Sinkronisasi Status Ketersediaan Barang & Pinjam Pakai</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[9px] font-black uppercase tracking-wider animate-pulse">LIVE SYNC</span>
                    </div>
                    <p class="text-[11px] text-slate-300 mt-0.5">Pantau ketersediaan barang operasional TOC yang terhubung dengan BAST & BAP</p>
                </div>
            </div>
            <a href="{{ route('items.index') }}" class="text-xs font-bold text-cyan-300 hover:text-cyan-200 flex items-center gap-1 self-start sm:self-auto transition-colors">
                <span>Lihat Semua Barang di Master Inventory</span>
                <span class="text-sm">&rarr;</span>
            </a>
        </div>

        <!-- 4 Tactical Cards -->
        <div class="p-3.5 sm:p-4 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- 1. SEDIA -->
            <a href="{{ route('items.index', ['availability_status' => 'sedia']) }}"
               class="group relative p-3.5 rounded-xl bg-gradient-to-br from-[#06241b] via-[#091f1a] to-[#041310] border-2 border-emerald-400/70 hover:border-emerald-300 shadow-[0_0_15px_rgba(16,185,129,0.2)] hover:shadow-[0_0_25px_rgba(16,185,129,0.4)] flex flex-col justify-between transition-all hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-1 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 text-[10px] font-extrabold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        SEDIA
                    </span>
                    <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                        {{ $pctHSedia }}%
                    </span>
                </div>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black font-sans text-emerald-300 tracking-tight drop-shadow-[0_0_10px_rgba(52,211,153,0.5)]">
                        {{ $summary['status_sedia'] ?? 0 }}
                    </span>
                    <span class="text-xs font-black uppercase text-emerald-400/80 tracking-wider font-sans">Unit</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] font-semibold text-slate-300">
                    <span>Siap di Gudang</span>
                    <span class="text-emerald-400 text-xs group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
                <div class="mt-2 w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-300 rounded-full" style="width: {{ $pctHSedia }}%"></div>
                </div>
            </a>

            <!-- 2. SEDANG KELUAR -->
            <a href="{{ route('items.index', ['availability_status' => 'sedang_keluar']) }}"
               class="group relative p-3.5 rounded-xl bg-gradient-to-br from-[#2a1b05] via-[#201506] to-[#120c03] border-2 border-amber-400/70 hover:border-amber-300 shadow-[0_0_15px_rgba(251,191,36,0.2)] hover:shadow-[0_0_25px_rgba(251,191,36,0.4)] flex flex-col justify-between transition-all hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-1 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/50 text-[10px] font-extrabold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
                        SEDANG KELUAR
                    </span>
                    <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-500/30">
                        {{ $pctHKeluar }}%
                    </span>
                </div>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black font-sans text-amber-300 tracking-tight drop-shadow-[0_0_10px_rgba(251,191,36,0.5)]">
                        {{ $summary['status_sedang_keluar'] ?? 0 }}
                    </span>
                    <span class="text-xs font-black uppercase text-amber-400/80 tracking-wider font-sans">Unit</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] font-semibold text-slate-300">
                    <span>Mutasi Keluar</span>
                    <span class="text-amber-400 text-xs group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
                <div class="mt-2 w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-300 rounded-full" style="width: {{ $pctHKeluar }}%"></div>
                </div>
            </a>

            <!-- 3. SEDANG DIPAKAI -->
            <a href="{{ route('items.index', ['availability_status' => 'sedang_dipakai']) }}"
               class="group relative p-3.5 rounded-xl bg-gradient-to-br from-[#06202c] via-[#081822] to-[#040e16] border-2 border-cyan-400/70 hover:border-cyan-300 shadow-[0_0_15px_rgba(34,211,238,0.2)] hover:shadow-[0_0_25px_rgba(34,211,238,0.4)] flex flex-col justify-between transition-all hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-1 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-400/50 text-[10px] font-extrabold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                        SEDANG DIPAKAI
                    </span>
                    <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-cyan-950 text-cyan-300 border border-cyan-500/30">
                        {{ $pctHDipakai }}%
                    </span>
                </div>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black font-sans text-cyan-300 tracking-tight drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]">
                        {{ $summary['status_sedang_dipakai'] ?? 0 }}
                    </span>
                    <span class="text-xs font-black uppercase text-cyan-400/80 tracking-wider font-sans">Unit</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] font-semibold text-slate-300">
                    <span>Giat Operasional</span>
                    <span class="text-cyan-400 text-xs group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
                <div class="mt-2 w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-cyan-500 to-sky-300 rounded-full" style="width: {{ $pctHDipakai }}%"></div>
                </div>
            </a>

            <!-- 4. SEDANG DIPINJAM -->
            <a href="{{ route('items.index', ['availability_status' => 'sedang_dipinjam']) }}"
               class="group relative p-3.5 rounded-xl bg-gradient-to-br from-[#191538] via-[#120f2b] to-[#0a081a] border-2 border-indigo-400/70 hover:border-indigo-300 shadow-[0_0_15px_rgba(129,140,248,0.2)] hover:shadow-[0_0_25px_rgba(129,140,248,0.4)] flex flex-col justify-between transition-all hover:-translate-y-0.5">
                <div class="flex items-center justify-between gap-1 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/50 text-[10px] font-extrabold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                        SEDANG DIPINJAM
                    </span>
                    <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-500/30">
                        {{ $pctHDipinjam }}%
                    </span>
                </div>
                <div class="my-1 flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black font-sans text-indigo-300 tracking-tight drop-shadow-[0_0_10px_rgba(129,140,248,0.5)]">
                        {{ $summary['status_sedang_dipinjam'] ?? 0 }}
                    </span>
                    <span class="text-xs font-black uppercase text-indigo-400/80 tracking-wider font-sans">Unit</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] font-semibold text-slate-300">
                    <span>Pinjam Pakai BAST</span>
                    <span class="text-indigo-400 text-xs group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
                <div class="mt-2 w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-300 rounded-full" style="width: {{ $pctHDipinjam }}%"></div>
                </div>
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
                                    @if ($doc->is_overdue)
                                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                            Jatuh Tempo (+{{ $doc->days_overdue }} Hari)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Diserahkan / Aktif
                                        </span>
                                    @endif
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
                                    @if ($doc->is_overdue && $doc->whatsapp_reminder_url)
                                        <a href="{{ $doc->whatsapp_reminder_url }}" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold inline-flex items-center gap-1 shadow-sm">
                                            <span>WA Ingatkan</span>
                                        </a>
                                    @endif
                                    @if ($doc->document_type === 'bast' && $doc->status === 'active')
                                        <a href="{{ route('handovers.create', ['type' => 'bap', 'bast_id' => $doc->id]) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                                            Buat BAP Kembali
                                        </a>
                                    @endif
                                    <form action="{{ route('handovers.destroy', $doc) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Pindahkan Berita Acara {{ $doc->document_no }} ini ke Sampah?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Pindahkan ke Sampah" class="px-2 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-semibold">
                                            Hapus
                                        </button>
                                    </form>
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
