@extends('layouts.app')

@section('title', 'Dashboard Analitik')
@section('page_title', 'Dashboard Analitik & Gudang Penyimpanan')
@section('page_subtitle', 'Visualisasi real-time stok gudang, mutasi scan barcode masuk/keluar, berita acara, dan aktivitas petugas')

@section('content')
    <!-- Hero Banner Bergradasi Modern -->
    <div class="mb-6 relative overflow-hidden p-5 sm:p-7 rounded-3xl bg-gradient-to-br from-[#0B1120] via-[#0F172A] to-emerald-950 text-white shadow-xl border border-slate-800">
        <div class="pointer-events-none absolute -right-16 -top-16 w-72 h-72 rounded-full bg-emerald-500/15 blur-3xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>SISTEM PENYIMPANAN & SCANNER REAL-TIME AKTIF</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                    Selamat bertugas, {{ auth()->user()->name }}!
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Jabatan: <strong class="text-white">{{ auth()->user()->job_title ?: 'Petugas Logistik' }}</strong> ({{ auth()->user()->department ?: 'Divisi Logistik' }}). Seluruh penambahan barang, scan barcode masuk/keluar, dan berita acara tercatat otomatis atas nama Anda.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-2.5">
                <a href="{{ route('scan.index', ['mode' => 'in']) }}"
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-950/50 transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Scan Masuk</span>
                </a>
                <a href="{{ route('scan.index', ['mode' => 'out']) }}"
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs sm:text-sm font-bold shadow-lg shadow-amber-950/30 transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                    <span>Scan Keluar</span>
                </a>
                <a href="{{ route('items.barcode-studio') }}"
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur transition-all">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h2v12H4V6zm4 0h1v12H8V6zm3 0h3v12h-3V6zm5 0h1v12h-1V6zm3 0h1v12h-1V6z"/>
                    </svg>
                    <span>Studio Barcode</span>
                </a>
                <a href="{{ route('handovers.create', ['type' => 'bast']) }}"
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur transition-all">
                    <svg class="w-4 h-4 text-teal-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Buat BAST/BAP</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Metric Cards Grid (2 Columns on Mobile, 4 Columns on Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5 mb-6">
        <div class="card-elevated bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-slate-700 to-slate-900"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Katalog & Stok</span>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($stats['total_items']) }} <span class="text-xs font-semibold text-slate-400">SKU</span></div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Total Unit Fisik:</span>
                <strong class="text-slate-900 font-bold">{{ number_format($stats['total_stock']) }} Unit</strong>
            </div>
        </div>

        <div class="card-elevated bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Scan Masuk Hari Ini</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-2xl sm:text-3xl font-extrabold text-emerald-600">+{{ number_format($stats['today_in_qty']) }}</div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Frekuensi Scan:</span>
                <strong class="text-emerald-700 font-bold">{{ $stats['today_in_count'] }}x Transaksi</strong>
            </div>
        </div>

        <div class="card-elevated bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Scan Keluar Hari Ini</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-2xl sm:text-3xl font-extrabold text-amber-600">-{{ number_format($stats['today_out_qty']) }}</div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Frekuensi Scan:</span>
                <strong class="text-amber-700 font-bold">{{ $stats['today_out_count'] }}x Transaksi</strong>
            </div>
        </div>

        <div class="card-elevated bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-600"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Berita Acara</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['active_bast_count'] }} <span class="text-xs font-semibold text-indigo-600">BAST Aktif</span></div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Pengembalian (BAP):</span>
                <strong class="text-slate-900 font-bold">{{ $stats['total_bap_count'] }} Dokumen</strong>
            </div>
        </div>
    </div>

    <!-- Panel Status Ketersediaan Barang Berenergi Tinggi (Tactical HUD) & Tabel Informasi Interaktif -->
    @php
        $totalStatusCount = max(1, ($stats['status_sedia'] ?? 0) + ($stats['status_sedang_keluar'] ?? 0) + ($stats['status_sedang_dipakai'] ?? 0) + ($stats['status_sedang_dipinjam'] ?? 0));
        $pctSedia = round((($stats['status_sedia'] ?? 0) / $totalStatusCount) * 100);
        $pctKeluar = round((($stats['status_sedang_keluar'] ?? 0) / $totalStatusCount) * 100);
        $pctDipakai = round((($stats['status_sedang_dipakai'] ?? 0) / $totalStatusCount) * 100);
        $pctDipinjam = round((($stats['status_sedang_dipinjam'] ?? 0) / $totalStatusCount) * 100);
    @endphp
    <div class="mb-6 bg-white rounded-2xl border-2 border-cyan-500/30 shadow-lg overflow-hidden">
        <!-- Header Panel -->
        <div class="p-4 sm:p-5 bg-gradient-to-r from-[#070D18] via-[#0B1528] to-[#070D18] border-b border-cyan-500/20 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500/20 to-emerald-500/30 border border-cyan-400/50 flex items-center justify-center text-cyan-300 shadow-[0_0_15px_rgba(34,211,238,0.3)] shrink-0">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wide text-white">Status Ketersediaan Barang Gudang (Real-Time HUD)</h3>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[9px] font-black uppercase tracking-wider animate-pulse">SYNC BAST & BAP</span>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">Klik salah satu kartu di bawah untuk menyaring <strong>Tabel Informasi Barang</strong> secara instan</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('handovers.index') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-cyan-600/30 to-blue-600/30 hover:from-cyan-600/50 hover:to-blue-600/50 text-cyan-300 border border-cyan-400/40 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                    <span>Kelola Dokumen BAST & BAP</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>

        <div class="p-4 sm:p-5 space-y-5">
            <!-- 4 Kartu Status Berenergi Tinggi (Vibrant Tactical Glow HUD) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
                <!-- 1. SEDIA (Neon Emerald) -->
                <button type="button" onclick="filterStatusTable('sedia')" id="btn-card-sedia"
                        class="status-filter-card group relative p-4 rounded-2xl bg-gradient-to-br from-[#06241b] via-[#091f1a] to-[#041310] border-2 border-emerald-400/70 hover:border-emerald-300 shadow-[0_0_20px_rgba(16,185,129,0.25)] hover:shadow-[0_0_30px_rgba(16,185,129,0.45)] text-left transition-all cursor-pointer active:scale-98 overflow-hidden select-none">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-emerald-500/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center justify-between gap-1 mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/60 text-[10px] font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            SEDIA
                        </span>
                        <span class="text-[10px] font-bold font-mono-code px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-300 border border-emerald-500/40">
                            {{ $pctSedia }}%
                        </span>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-emerald-300 font-mono-code tracking-tight leading-none drop-shadow-[0_0_12px_rgba(52,211,153,0.5)]">
                        {{ $stats['status_sedia'] ?? 0 }}
                    </div>
                    <div class="mt-2 text-xs font-bold text-slate-200 flex items-center justify-between">
                        <span>Siap di Gudang</span>
                        <span class="text-[10px] text-emerald-400 font-bold group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                    <!-- Mini Progress Bar -->
                    <div class="mt-2 w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-300 rounded-full" style="width: {{ $pctSedia }}%"></div>
                    </div>
                </button>

                <!-- 2. SEDANG KELUAR (Neon Amber Gold) -->
                <button type="button" onclick="filterStatusTable('sedang_keluar')" id="btn-card-sedang_keluar"
                        class="status-filter-card group relative p-4 rounded-2xl bg-gradient-to-br from-[#2a1b05] via-[#201506] to-[#120c03] border-2 border-amber-400/70 hover:border-amber-300 shadow-[0_0_20px_rgba(251,191,36,0.25)] hover:shadow-[0_0_30px_rgba(251,191,36,0.45)] text-left transition-all cursor-pointer active:scale-98 overflow-hidden select-none">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-amber-500/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center justify-between gap-1 mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/60 text-[10px] font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                            SEDANG KELUAR
                        </span>
                        <span class="text-[10px] font-bold font-mono-code px-1.5 py-0.5 rounded bg-amber-950/80 text-amber-300 border border-amber-500/40">
                            {{ $pctKeluar }}%
                        </span>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-amber-300 font-mono-code tracking-tight leading-none drop-shadow-[0_0_12px_rgba(251,191,36,0.5)]">
                        {{ $stats['status_sedang_keluar'] ?? 0 }}
                    </div>
                    <div class="mt-2 text-xs font-bold text-slate-200 flex items-center justify-between">
                        <span>Mutasi Keluar</span>
                        <span class="text-[10px] text-amber-400 font-bold group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                    <!-- Mini Progress Bar -->
                    <div class="mt-2 w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-300 rounded-full" style="width: {{ $pctKeluar }}%"></div>
                    </div>
                </button>

                <!-- 3. SEDANG DIPAKAI (Electric Cyan Operations) -->
                <button type="button" onclick="filterStatusTable('sedang_dipakai')" id="btn-card-sedang_dipakai"
                        class="status-filter-card group relative p-4 rounded-2xl bg-gradient-to-br from-[#06202c] via-[#081822] to-[#040e16] border-2 border-cyan-400/70 hover:border-cyan-300 shadow-[0_0_20px_rgba(34,211,238,0.25)] hover:shadow-[0_0_30px_rgba(34,211,238,0.45)] text-left transition-all cursor-pointer active:scale-98 overflow-hidden select-none">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-cyan-500/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center justify-between gap-1 mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-400/60 text-[10px] font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                            SEDANG DIPAKAI
                        </span>
                        <span class="text-[10px] font-bold font-mono-code px-1.5 py-0.5 rounded bg-cyan-950/80 text-cyan-300 border border-cyan-500/40">
                            {{ $pctDipakai }}%
                        </span>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-cyan-300 font-mono-code tracking-tight leading-none drop-shadow-[0_0_12px_rgba(34,211,238,0.5)]">
                        {{ $stats['status_sedang_dipakai'] ?? 0 }}
                    </div>
                    <div class="mt-2 text-xs font-bold text-slate-200 flex items-center justify-between">
                        <span>Giat Operasional</span>
                        <span class="text-[10px] text-cyan-400 font-bold group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                    <!-- Mini Progress Bar -->
                    <div class="mt-2 w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-cyan-500 to-sky-300 rounded-full" style="width: {{ $pctDipakai }}%"></div>
                    </div>
                </button>

                <!-- 4. SEDANG DIPINJAM (Royal Indigo Protocol) -->
                <button type="button" onclick="filterStatusTable('sedang_dipinjam')" id="btn-card-sedang_dipinjam"
                        class="status-filter-card group relative p-4 rounded-2xl bg-gradient-to-br from-[#191538] via-[#120f2b] to-[#0a081a] border-2 border-indigo-400/70 hover:border-indigo-300 shadow-[0_0_20px_rgba(129,140,248,0.25)] hover:shadow-[0_0_30px_rgba(129,140,248,0.45)] text-left transition-all cursor-pointer active:scale-98 overflow-hidden select-none">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-indigo-500/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center justify-between gap-1 mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/60 text-[10px] font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                            SEDANG DIPINJAM
                        </span>
                        <span class="text-[10px] font-bold font-mono-code px-1.5 py-0.5 rounded bg-indigo-950/80 text-indigo-300 border border-indigo-500/40">
                            {{ $pctDipinjam }}%
                        </span>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-indigo-300 font-mono-code tracking-tight leading-none drop-shadow-[0_0_12px_rgba(129,140,248,0.5)]">
                        {{ $stats['status_sedang_dipinjam'] ?? 0 }}
                    </div>
                    <div class="mt-2 text-xs font-bold text-slate-200 flex items-center justify-between">
                        <span>Pinjam Pakai BAST</span>
                        <span class="text-[10px] text-indigo-400 font-bold group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                    <!-- Mini Progress Bar -->
                    <div class="mt-2 w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-300 rounded-full" style="width: {{ $pctDipinjam }}%"></div>
                    </div>
                </button>
            </div>

            <!-- TABEL INFORMASI RINCIAN BARANG (Interactive Telemetry Table Info) -->
            <div class="rounded-2xl border border-slate-200/90 overflow-hidden bg-slate-900/40">
                <!-- Filter Tab Bar & Quick Search -->
                <div class="p-3 sm:p-4 bg-[#09111E] border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" onclick="filterStatusTable('all')" id="tab-status-all"
                                class="tab-status-btn px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 shadow-xs">
                            Semua Barang ({{ $statusItems->count() }})
                        </button>
                        <button type="button" onclick="filterStatusTable('sedia')" id="tab-status-sedia"
                                class="tab-status-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent">
                            Sedia ({{ $stats['status_sedia'] ?? 0 }})
                        </button>
                        <button type="button" onclick="filterStatusTable('sedang_keluar')" id="tab-status-sedang_keluar"
                                class="tab-status-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent">
                            Sedang Keluar ({{ $stats['status_sedang_keluar'] ?? 0 }})
                        </button>
                        <button type="button" onclick="filterStatusTable('sedang_dipakai')" id="tab-status-sedang_dipakai"
                                class="tab-status-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent">
                            Sedang Dipakai ({{ $stats['status_sedang_dipakai'] ?? 0 }})
                        </button>
                        <button type="button" onclick="filterStatusTable('sedang_dipinjam')" id="tab-status-sedang_dipinjam"
                                class="tab-status-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent">
                            Sedang Dipinjam ({{ $stats['status_sedang_dipinjam'] ?? 0 }})
                        </button>
                    </div>
                    <div class="relative min-w-[200px]">
                        <input type="text" id="status_table_search" oninput="searchStatusTable(this.value)"
                               placeholder="Cari nama / barcode di tabel..."
                               class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-slate-950/80 border border-slate-700 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-cyan-400">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Desktop Table View (MD ke Atas) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#050C16] border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-cyan-400">
                                <th class="py-3 px-4">Barang & Barcode</th>
                                <th class="py-3 px-4">Status Ketersediaan</th>
                                <th class="py-3 px-4">Kategori & Tipe</th>
                                <th class="py-3 px-4">Stok Fisik</th>
                                <th class="py-3 px-4">Lokasi Rak</th>
                                <th class="py-3 px-4 text-right">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody id="status-table-body" class="divide-y divide-slate-800/80">
                            @forelse ($statusItems as $stItem)
                                @php
                                    $stVal = $stItem->availability_status ?: 'sedia';
                                    $searchBlob = strtolower($stItem->name . ' ' . $stItem->barcode . ' ' . $stItem->item_code . ' ' . ($stItem->category?->name ?? ''));
                                @endphp
                                <tr class="status-table-row hover:bg-slate-800/50 transition-colors"
                                    data-status="{{ $stVal }}"
                                    data-keyword="{{ $searchBlob }}">
                                    <!-- 1. Barang & Barcode -->
                                    <td class="py-3 px-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            @if ($stItem->photo_url)
                                                <button type="button" onclick="openPhotoLightbox('{{ $stItem->photo_url }}', '{{ addslashes($stItem->name) }}')"
                                                        class="w-10 h-10 rounded-lg overflow-hidden border border-slate-700 shrink-0 hover:scale-105 transition-transform">
                                                    <img src="{{ $stItem->photo_url }}" alt="{{ $stItem->name }}" class="w-full h-full object-cover">
                                                </button>
                                            @else
                                                <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="font-extrabold text-white text-xs truncate max-w-xs">{{ $stItem->name }}</div>
                                                <div class="text-[11px] font-mono-code text-cyan-300 mt-0.5">
                                                    {{ $stItem->barcode }} <span class="text-slate-500">•</span> {{ $stItem->item_code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 2. Status Ketersediaan -->
                                    <td class="py-3 px-4 align-middle">
                                        @if ($stVal === 'sedia')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 text-[10px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>SEDIA (Gudang)
                                            </span>
                                        @elseif ($stVal === 'sedang_keluar')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/50 text-[10px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>SEDANG KELUAR
                                            </span>
                                        @elseif ($stVal === 'sedang_dipakai')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-400/50 text-[10px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>SEDANG DIPAKAI
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/50 text-[10px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>SEDANG DIPINJAM
                                            </span>
                                        @endif
                                    </td>

                                    <!-- 3. Kategori & Tipe -->
                                    <td class="py-3 px-4 align-middle">
                                        <div class="text-slate-300 font-semibold">{{ $stItem->category?->name ?? 'Umum' }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase">{{ $stItem->item_type === 'asset' ? 'Aset Inventaris' : 'Habis Pakai' }}</div>
                                    </td>

                                    <!-- 4. Stok Fisik -->
                                    <td class="py-3 px-4 align-middle">
                                        <span class="px-2.5 py-1 rounded-lg font-mono-code font-bold text-xs {{ $stItem->isLowStock() ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-slate-800 text-white' }}">
                                            {{ $stItem->stock }} {{ $stItem->unit }}
                                        </span>
                                    </td>

                                    <!-- 5. Lokasi Rak -->
                                    <td class="py-3 px-4 align-middle">
                                        <span class="font-mono-code text-slate-300 text-[11px]">{{ $stItem->storage_location ?: 'Gudang TOC' }}</span>
                                    </td>

                                    <!-- 6. Aksi Cepat -->
                                    <td class="py-3 px-4 align-middle text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('scan.index', ['barcode' => $stItem->barcode]) }}"
                                               class="px-2.5 py-1 rounded-lg bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/40 text-[11px] font-bold transition-all">
                                                Scan
                                            </a>
                                            <a href="{{ route('items.barcode-studio', ['selected_id' => $stItem->id]) }}"
                                               class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-[11px] font-semibold transition-all">
                                                Stiker
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">Belum ada barang di database.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stream (Khusus Layar HP / MD Hidden) -->
                <div id="status-mobile-stream" class="md:hidden divide-y divide-slate-800 p-3 space-y-2.5">
                    @forelse ($statusItems as $stItem)
                        @php
                            $stVal = $stItem->availability_status ?: 'sedia';
                            $searchBlob = strtolower($stItem->name . ' ' . $stItem->barcode . ' ' . $stItem->item_code);
                        @endphp
                        <div class="status-table-row p-3 rounded-xl bg-slate-900/90 border border-slate-800 space-y-2"
                             data-status="{{ $stVal }}"
                             data-keyword="{{ $searchBlob }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono-code text-[11px] text-cyan-300 font-bold">{{ $stItem->item_code }}</span>
                                @if ($stVal === 'sedia')
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[9px] font-black uppercase">SEDIA</span>
                                @elseif ($stVal === 'sedang_keluar')
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[9px] font-black uppercase">SEDANG KELUAR</span>
                                @elseif ($stVal === 'sedang_dipakai')
                                    <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 text-[9px] font-black uppercase">SEDANG DIPAKAI</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[9px] font-black uppercase">SEDANG DIPINJAM</span>
                                @endif
                            </div>
                            <div class="flex items-start gap-2.5">
                                @if ($stItem->photo_url)
                                    <img src="{{ $stItem->photo_url }}" alt="{{ $stItem->name }}" class="w-11 h-11 rounded-lg object-cover border border-slate-700 shrink-0">
                                @else
                                    <div class="w-11 h-11 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-white truncate">{{ $stItem->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono-code">{{ $stItem->barcode }}</div>
                                    <div class="text-[10px] text-slate-300 mt-1 flex items-center justify-between">
                                        <span>Stok: <strong class="text-white">{{ $stItem->stock }} {{ $stItem->unit }}</strong></span>
                                        <span>Rak: {{ $stItem->storage_location ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-1 flex items-center justify-end gap-2 border-t border-slate-800">
                                <a href="{{ route('scan.index', ['barcode' => $stItem->barcode]) }}" class="px-3 py-1 rounded-lg bg-emerald-600/40 text-emerald-300 text-[10px] font-bold">Scan In/Out</a>
                                <a href="{{ route('items.barcode-studio', ['selected_id' => $stItem->id]) }}" class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10px] font-semibold">Cetak Stiker</a>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">Belum ada barang di database.</div>
                    @endforelse
                </div>

                <!-- Empty State Saat Filter Tidak Menemukan Barang -->
                <div id="status-empty-state" class="hidden p-8 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center mx-auto mb-2 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-white" id="status-empty-title">Tidak ada barang dengan status ini</div>
                    <p class="text-xs text-slate-400 mt-1" id="status-empty-desc">Seluruh unit berada dalam kondisi normal.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Analytics Row: In vs Out Ratio & Stock Distribution by Category -->
    @php
        $totalFlow = max(1, $stats['total_in_all'] + $stats['total_out_all']);
        $inPercent = round(($stats['total_in_all'] / $totalFlow) * 100);
        $outPercent = 100 - $inPercent;
        $maxCatStock = max(1, (int) $categoryStats->max('items_sum_stock'));
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <!-- Left 5 Cols: Perbandingan Volume Scan Masuk vs Keluar -->
        <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Rasio Mutasi Scan Masuk vs Keluar</h3>
                        <p class="text-xs text-slate-500">Perbandingan akumulasi barang masuk dan keluar gudang</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">Real-Time</span>
                </div>

                <!-- Visual Multi-Bar Progress -->
                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs font-bold mb-2">
                        <span class="text-emerald-700">BARANG MASUK ({{ $inPercent }}%)</span>
                        <span class="text-amber-700">BARANG KELUAR ({{ $outPercent }}%)</span>
                    </div>
                    <div class="w-full h-4 rounded-full bg-slate-100 overflow-hidden flex p-0.5 gap-1">
                        <div class="h-full rounded-l-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500" style="width: {{ $inPercent }}%"></div>
                        <div class="h-full rounded-r-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-500" style="width: {{ $outPercent }}%"></div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/70">
                        <div class="text-[11px] font-bold uppercase text-emerald-700">Total Akumulasi Masuk</div>
                        <div class="text-xl font-extrabold text-emerald-700 mt-0.5">+{{ number_format($stats['total_in_all']) }} <span class="text-xs font-semibold">Unit</span></div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/70">
                        <div class="text-[11px] font-bold uppercase text-amber-700">Total Akumulasi Keluar</div>
                        <div class="text-xl font-extrabold text-amber-700 mt-0.5">-{{ number_format($stats['total_out_all']) }} <span class="text-xs font-semibold">Unit</span></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Lihat rincian per tanggal & petugas:</span>
                <a href="{{ route('reports.index') }}" class="font-bold text-emerald-600 hover:text-emerald-700">Buka Laporan Lengkap &rarr;</a>
            </div>
        </div>

        <!-- Right 7 Cols: Grafik Distribusi Stok per Kategori Gudang -->
        <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Distribusi Kapasitas Penyimpanan per Kategori</h3>
                    <p class="text-xs text-slate-500">Sebaran jumlah barang dan unit stok fisik yang tersimpan di gudang</p>
                </div>
                <a href="{{ route('items.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Kelola Kategori &rarr;</a>
            </div>

            <div class="space-y-3.5">
                @foreach ($categoryStats as $cat)
                    @php
                        $catStock = (int) ($cat->items_sum_stock ?? 0);
                        $barWidth = max(6, round(($catStock / $maxCatStock) * 100));
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded font-mono-code text-[10px] font-bold bg-slate-100 text-slate-700">{{ $cat->code }}</span>
                                <span class="font-bold text-slate-800">{{ $cat->name }}</span>
                                <span class="text-slate-400">({{ $cat->items_count }} jenis barang)</span>
                            </div>
                            <span class="font-extrabold text-slate-900">{{ number_format($catStock) }} Unit</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-slate-800 via-emerald-600 to-teal-500" style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Grid: Recent Scan Movements & Sidebar Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 8 Columns: Recent Scan Transactions & Recent Items -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200/80 flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Riwayat Scan Barcode Masuk & Keluar Terkini</h3>
                        <p class="text-xs text-slate-500">Dilengkapi lencana petugas scan dan perubahan stok secara presisi</p>
                    </div>
                    <a href="{{ route('scan.index') }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition-colors">
                        + Scan Baru
                    </a>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fluid">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Ref & Waktu Scan</th>
                                <th class="py-3 px-4">Barang & Barcode</th>
                                <th class="py-3 px-4">Mutasi & Stok</th>
                                <th class="py-3 px-4">Petugas Scan (User)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 text-sm">
                            @forelse ($recentMovements as $m)
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="py-3.5 px-4 align-top">
                                        <div class="font-mono-code font-bold text-xs text-slate-900">{{ $m->reference_no }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $m->scanned_at?->format('d/m/Y H:i') }} WIB</div>
                                    </td>
                                    <td class="py-3.5 px-4 align-top">
                                        <div class="flex items-start gap-2.5">
                                            @if ($m->item?->photo_url)
                                                <button type="button" onclick="openPhotoLightbox('{{ $m->item->photo_url }}', '{{ addslashes($m->item->name) }}')"
                                                        class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                                    <img src="{{ $m->item->photo_url }}" alt="{{ $m->item->name }}" class="w-full h-full object-cover">
                                                </button>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $m->item?->name ?? 'Barang Dihapus' }}</div>
                                                <div class="text-xs text-emerald-700 font-mono-code font-semibold mt-0.5">
                                                    Barcode: {{ $m->item?->barcode ?? '-' }}
                                                </div>
                                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $m->source_destination }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 align-top">
                                        @if ($m->movement_type === 'in')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                                                MASUK +{{ $m->quantity }} {{ $m->item?->unit }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800">
                                                KELUAR -{{ $m->quantity }} {{ $m->item?->unit }}
                                            </span>
                                        @endif
                                        <div class="text-[11px] text-slate-500 mt-1">
                                            Stok: {{ $m->stock_before }} &rarr; <strong class="text-slate-900">{{ $m->stock_after }}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="w-7 h-7 rounded-lg bg-slate-900 text-emerald-400 text-[11px] font-extrabold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($m->user?->name ?? 'S', 0, 2)) }}
                                            </span>
                                            <div>
                                                <div class="font-bold text-slate-900 text-xs">{{ $m->user?->name ?? 'Sistem' }}</div>
                                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Via {{ $m->scan_method }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-sm text-slate-500">
                                        Belum ada riwayat scan barcode.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TAMPILAN AKTIVITAS SCAN KHUSUS HP (MD HIDDEN) -->
                <div class="md:hidden divide-y divide-slate-100 p-3 space-y-2.5">
                    @forelse ($recentMovements as $m)
                        <div class="card-elevated bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono-code font-bold text-[11px] text-slate-800">{{ $m->reference_no }}</span>
                                @if ($m->movement_type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        MASUK +{{ $m->quantity }} {{ $m->item?->unit }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                        KELUAR -{{ $m->quantity }} {{ $m->item?->unit }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-start gap-2.5">
                                @if ($m->item?->photo_url)
                                    <button type="button" onclick="openPhotoLightbox('{{ $m->item->photo_url }}', '{{ addslashes($m->item->name) }}')"
                                            class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                        <img src="{{ $m->item->photo_url }}" alt="{{ $m->item->name }}" class="w-full h-full object-cover">
                                    </button>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-900 text-xs truncate">{{ $m->item?->name ?? 'Barang Dihapus' }}</div>
                                    <div class="text-[11px] text-emerald-700 font-mono-code">Barcode: {{ $m->item?->barcode ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-500 mt-1 flex items-center justify-between">
                                        <span>Stok: {{ $m->stock_before }} &rarr; <strong class="text-slate-800">{{ $m->stock_after }}</strong></span>
                                        <span>Petugas: <strong class="text-slate-700">{{ $m->user?->name ?? 'Sistem' }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-slate-400">Belum ada riwayat scan barcode.</div>
                    @endforelse
                </div>
            </div>

            <!-- Recently Added Items with Creator Tracking -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200/80 flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Barang & Barcode Terbaru yang Didaftarkan</h3>
                        <p class="text-xs text-slate-500">Menampilkan grafik barcode langsung serta user pembuat barang</p>
                    </div>
                    <a href="{{ route('items.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                        Semua Barang &rarr;
                    </a>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fluid">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Barang & Kode</th>
                                <th class="py-3 px-4">Grafik Barcode</th>
                                <th class="py-3 px-4">Stok & Lokasi</th>
                                <th class="py-3 px-4">Ditambahkan Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 text-sm">
                            @foreach ($recentItems as $item)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 align-top">
                                        <div class="flex items-start gap-2.5">
                                            @if ($item->photo_url)
                                                <button type="button" onclick="openPhotoLightbox('{{ $item->photo_url }}', '{{ addslashes($item->name) }}')"
                                                        class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                                    <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                </button>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                                <div class="text-xs text-slate-500 mt-0.5">
                                                    <span class="font-mono-code font-semibold text-emerald-700">{{ $item->item_code }}</span> • {{ $item->category?->name ?? 'Umum' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <div class="keep-light inline-block bg-white px-1.5 py-0.5 rounded-md border border-slate-200">
                                            <svg class="js-barcode max-w-[105px] h-auto block" data-barcode="{{ $item->barcode }}" data-height="18" data-width="0.9" data-fontsize="8" data-margin="1"></svg>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <div class="font-extrabold {{ $item->isLowStock() ? 'text-rose-600' : 'text-slate-900' }}">
                                            {{ $item->stock }} {{ $item->unit }}
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $item->storage_location ?: 'Gudang Utama' }}</div>
                                        <div class="mt-1">
                                            @php $avStatus = $item->availability_status ?? 'sedia'; @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold
                                                {{ $avStatus === 'sedia' ? 'bg-emerald-100 text-emerald-800' : ($avStatus === 'sedang_keluar' ? 'bg-amber-100 text-amber-800' : ($avStatus === 'sedang_dipakai' ? 'bg-cyan-100 text-cyan-800' : 'bg-indigo-100 text-indigo-800')) }}">
                                                {{ $item->availability_label }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-extrabold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($item->creator?->name ?? 'A', 0, 2)) }}
                                            </span>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900">{{ $item->creator?->name ?? 'Admin' }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $item->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- TAMPILAN BARANG TERBARU KHUSUS HP (MD HIDDEN) -->
                <div class="md:hidden divide-y divide-slate-100 p-3 space-y-2.5">
                    @foreach ($recentItems as $item)
                        <div class="card-elevated bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if ($item->photo_url)
                                    <button type="button" onclick="openPhotoLightbox('{{ $item->photo_url }}', '{{ addslashes($item->name) }}')"
                                            class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                        <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                    </button>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-xs truncate">{{ $item->name }}</div>
                                    <div class="text-[10px] font-mono-code text-emerald-700">{{ $item->item_code }}</div>
                                    <div class="text-[10px] text-slate-400">Rak: {{ $item->storage_location ?: 'Gudang' }}</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-sm font-extrabold text-slate-900">{{ number_format($item->stock) }} <span class="text-[10px] text-slate-400">{{ $item->unit }}</span></div>
                                <div class="keep-light bg-white px-1 py-0.5 rounded border border-slate-200 mt-1">
                                    <svg class="js-barcode max-w-[85px] h-auto block" data-barcode="{{ $item->barcode }}" data-height="16" data-width="0.8" data-fontsize="8" data-margin="1"></svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right 4 Columns: Low Stock Alert & Vertical Timeline History Akses Web -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Peringatan Stok Menipis -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $stats['low_stock_count'] > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                        <h3 class="text-sm font-extrabold text-slate-900">Peringatan Stok Minimum</h3>
                    </div>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $stats['low_stock_count'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $stats['low_stock_count'] }} Item
                    </span>
                </div>
                <div class="p-4 space-y-3">
                    @forelse ($lowStockItems as $low)
                        <div class="p-3.5 rounded-xl bg-gradient-to-r from-rose-50 to-orange-50/40 border border-rose-200 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-extrabold text-slate-900">{{ $low->name }}</div>
                                <div class="text-[11px] text-slate-600 font-mono-code mt-0.5">Barcode: {{ $low->barcode }}</div>
                                <div class="text-[11px] text-rose-700 font-semibold mt-0.5">
                                    Sisa: <strong>{{ $low->stock }} {{ $low->unit }}</strong> (Min: {{ $low->min_stock }})
                                </div>
                            </div>
                            <a href="{{ route('scan.index', ['mode' => 'in', 'barcode' => $low->barcode]) }}"
                               class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-2xs shrink-0">
                                + Restock
                            </a>
                        </div>
                    @empty
                        <div class="text-xs text-slate-500 text-center py-5">
                            Seluruh stok barang berada di atas batas minimum aman.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Vertical Timeline: History Akses Keluar Masuk Web Terkini -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Timeline History Akses Web</h3>
                        <p class="text-[11px] text-slate-500">Jejak login masuk, logout keluar & aktivitas</p>
                    </div>
                    <a href="{{ route('access-logs.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                        Semua &rarr;
                    </a>
                </div>
                <div class="p-4">
                    <div class="relative pl-5 border-l-2 border-slate-200 space-y-4">
                        @foreach ($recentAccessLogs as $log)
                            <div class="relative">
                                <span class="absolute -left-[25px] top-1 w-3 h-3 rounded-full ring-4 ring-white {{ $log->event_type === 'login' ? 'bg-emerald-500' : ($log->event_type === 'logout' ? 'bg-slate-400' : ($log->event_type === 'failed_login' ? 'bg-rose-500' : 'bg-blue-500')) }}"></span>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold text-slate-900">{{ $log->user_name }}</span>
                                    @if ($log->event_type === 'login')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">LOGIN</span>
                                    @elseif ($log->event_type === 'logout')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">LOGOUT</span>
                                    @elseif ($log->event_type === 'failed_login')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">GAGAL</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">AKTIVITAS</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">{{ $log->description }}</p>
                                <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400 font-medium">
                                    <span>{{ $log->ip_address }} • {{ $log->device_browser }}</span>
                                    <span>{{ $log->accessed_at?->format('d/m H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var currentActiveStatus = 'all';

    function filterStatusTable(status) {
        currentActiveStatus = status;

        // 1. Update Card Active Rings
        var cardIds = ['sedia', 'sedang_keluar', 'sedang_dipakai', 'sedang_dipinjam'];
        cardIds.forEach(function (cid) {
            var card = document.getElementById('btn-card-' + cid);
            if (card) {
                if (status === cid) {
                    card.classList.add('ring-4', 'ring-cyan-400', 'scale-[1.02]');
                } else {
                    card.classList.remove('ring-4', 'ring-cyan-400', 'scale-[1.02]');
                }
            }
        });

        // 2. Update Tab Buttons
        var tabIds = ['all', 'sedia', 'sedang_keluar', 'sedang_dipakai', 'sedang_dipinjam'];
        tabIds.forEach(function (tid) {
            var tab = document.getElementById('tab-status-' + tid);
            if (tab) {
                if (status === tid) {
                    tab.className = 'tab-status-btn px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 shadow-xs';
                } else {
                    tab.className = 'tab-status-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-300 hover:text-white hover:bg-slate-800 border border-transparent';
                }
            }
        });

        // 3. Filter Table Rows
        applyStatusFilterAndSearch();
    }

    function searchStatusTable(keyword) {
        applyStatusFilterAndSearch();
    }

    function applyStatusFilterAndSearch() {
        var searchInput = document.getElementById('status_table_search');
        var query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        var rows = document.querySelectorAll('.status-table-row');
        var visibleCount = 0;

        rows.forEach(function (row) {
            var rowStatus = row.getAttribute('data-status') || 'sedia';
            var rowKeyword = row.getAttribute('data-keyword') || '';

            var matchStatus = (currentActiveStatus === 'all' || rowStatus === currentActiveStatus);
            var matchSearch = (!query || rowKeyword.indexOf(query) !== -1);

            if (matchStatus && matchSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var emptyBox = document.getElementById('status-empty-state');
        if (emptyBox) {
            if (visibleCount === 0) {
                emptyBox.classList.remove('hidden');
                var title = document.getElementById('status-empty-title');
                var desc = document.getElementById('status-empty-desc');
                if (currentActiveStatus !== 'all') {
                    var statusLabels = {
                        'sedia': 'Sedia di Gudang',
                        'sedang_keluar': 'Sedang Keluar',
                        'sedang_dipakai': 'Sedang Dipakai',
                        'sedang_dipinjam': 'Sedang Dipinjam'
                    };
                    if (title) title.textContent = 'Tidak Ada Barang Berstatus "' + (statusLabels[currentActiveStatus] || currentActiveStatus) + '"';
                    if (desc) desc.textContent = 'Saat ini belum ada unit logistik yang terdaftar dalam status ini.';
                } else {
                    if (title) title.textContent = 'Tidak Ada Barang yang Cocok';
                    if (desc) desc.textContent = 'Coba kata kunci pencarian yang lain.';
                }
            } else {
                emptyBox.classList.add('hidden');
            }
        }
    }
</script>
@endpush
