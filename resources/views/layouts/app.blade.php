<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F7FB]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LogiScan Pro') — Sistem Barcode & Gudang Logistik</title>
    <script>
        // Terapkan tema sebelum halaman dirender (anti kedip). Default: Mode Cyber.
        (function () {
            try {
                if (localStorage.getItem('logiscan-theme') !== 'light') {
                    document.documentElement.classList.add('theme-cyber');
                }
            } catch (e) {
                document.documentElement.classList.add('theme-cyber');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;700;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-image: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 45%),
                              radial-gradient(at 100% 0%, rgba(15, 23, 42, 0.04) 0px, transparent 45%);
            background-attachment: fixed;
        }
        .font-mono-code { font-family: 'JetBrains Mono', monospace; }
        .table-fluid th, .table-fluid td {
            font-size: clamp(0.76rem, 1.45vw, 0.875rem);
            line-height: 1.45;
        }
        .card-elevated {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-elevated:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(15, 23, 42, 0.08), 0 4px 8px -4px rgba(15, 23, 42, 0.04);
        }
        @keyframes scanLaser {
            0% { top: 8%; opacity: 0.4; }
            50% { top: 88%; opacity: 1; }
            100% { top: 8%; opacity: 0.4; }
        }
        .laser-line {
            position: absolute;
            left: 6%;
            right: 6%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, #34d399, #10b981, transparent);
            box-shadow: 0 0 12px 2px rgba(16, 185, 129, 0.75);
            animation: scanLaser 2.4s ease-in-out infinite;
        }
        @keyframes radarSweep { to { transform: rotate(360deg); } }
        .radar-sweep {
            background: conic-gradient(from 0deg, rgba(34, 211, 238, 0.35), transparent 28%);
            animation: radarSweep 4s linear infinite;
        }
        @keyframes blinkCursor { 50% { opacity: 0; } }
        .blink-cursor { animation: blinkCursor 1s step-end infinite; }

        /* Pengalih elemen per tema */
        html:not(.theme-cyber) .cyber-only { display: none !important; }
        html.theme-cyber .light-only { display: none !important; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* =====================================================================
           TEMA CYBER HUD — lapisan warna global (tidak mengubah logika apa pun)
           ===================================================================== */
        html.theme-cyber { color-scheme: dark; background-color: #05070D !important; }
        html.theme-cyber body {
            background-color: #05070D !important;
            color: #cbd5e1;
            background-image:
                linear-gradient(rgba(34, 211, 238, 0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, 0.045) 1px, transparent 1px),
                radial-gradient(ellipse at top, rgba(34, 211, 238, 0.10), transparent 60%);
            background-size: 32px 32px, 32px 32px, 100% 100%;
        }
        html.theme-cyber body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 60;
            background: repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.018) 0 1px, transparent 1px 3px);
        }

        /* Tipografi HUD */
        html.theme-cyber h1, html.theme-cyber h2, html.theme-cyber h3, html.theme-cyber h4 {
            font-family: 'Rajdhani', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.03em;
        }
        html.theme-cyber .text-2xl.font-extrabold,
        html.theme-cyber .text-3xl,
        html.theme-cyber .text-xl.font-extrabold {
            font-family: 'Orbitron', 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.02em;
        }

        /* Panel / kartu kaca gelap */
        html.theme-cyber .bg-white,
        html.theme-cyber .bg-white\/95,
        html.theme-cyber .bg-white\/85,
        html.theme-cyber .bg-white\/80 { background-color: rgba(10, 16, 30, 0.86) !important; }
        html.theme-cyber .bg-white.border,
        html.theme-cyber .bg-white.rounded-2xl {
            border-color: rgba(34, 211, 238, 0.18) !important;
            box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(34, 211, 238, 0.08) !important;
        }
        html.theme-cyber .bg-white.rounded-2xl { position: relative; }
        html.theme-cyber .bg-white.rounded-2xl::before,
        html.theme-cyber .bg-white.rounded-2xl::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            pointer-events: none;
            z-index: 2;
        }
        html.theme-cyber .bg-white.rounded-2xl::before {
            top: 0; left: 0;
            border-top: 2px solid #22d3ee;
            border-left: 2px solid #22d3ee;
            border-top-left-radius: 1rem;
        }
        html.theme-cyber .bg-white.rounded-2xl::after {
            bottom: 0; right: 0;
            border-bottom: 2px solid #22d3ee;
            border-right: 2px solid #22d3ee;
            border-bottom-right-radius: 1rem;
        }
        html.theme-cyber .card-elevated:hover {
            box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.35), 0 0 28px -6px rgba(34, 211, 238, 0.35) !important;
        }

        /* Latar tipis */
        html.theme-cyber .bg-slate-50, html.theme-cyber .bg-slate-50\/90, html.theme-cyber .bg-slate-50\/80,
        html.theme-cyber .bg-slate-50\/70, html.theme-cyber .bg-gray-50 { background-color: rgba(34, 211, 238, 0.045) !important; }
        html.theme-cyber .bg-slate-100, html.theme-cyber .bg-slate-100\/90, html.theme-cyber .bg-gray-100 { background-color: rgba(148, 163, 184, 0.10) !important; }
        html.theme-cyber .bg-slate-200 { background-color: rgba(148, 163, 184, 0.18) !important; }
        html.theme-cyber .bg-slate-900 { background-color: #0b1626 !important; box-shadow: inset 0 0 0 1px rgba(34, 211, 238, 0.32); }
        html.theme-cyber .bg-emerald-50, html.theme-cyber .bg-emerald-50\/50, html.theme-cyber .bg-emerald-50\/70,
        html.theme-cyber .bg-emerald-50\/80 { background-color: rgba(16, 185, 129, 0.10) !important; }
        html.theme-cyber .bg-emerald-100 { background-color: rgba(16, 185, 129, 0.18) !important; }
        html.theme-cyber .bg-amber-50, html.theme-cyber .bg-amber-50\/70, html.theme-cyber .bg-amber-50\/80 { background-color: rgba(245, 158, 11, 0.10) !important; }
        html.theme-cyber .bg-amber-100 { background-color: rgba(245, 158, 11, 0.18) !important; }
        html.theme-cyber .bg-rose-50, html.theme-cyber .bg-rose-50\/70 { background-color: rgba(244, 63, 94, 0.10) !important; }
        html.theme-cyber .bg-rose-100 { background-color: rgba(244, 63, 94, 0.18) !important; }
        html.theme-cyber .bg-indigo-50 { background-color: rgba(129, 140, 248, 0.12) !important; }
        html.theme-cyber .bg-blue-50 { background-color: rgba(56, 189, 248, 0.12) !important; }
        html.theme-cyber .from-emerald-50 { background-image: none !important; background-color: rgba(16, 185, 129, 0.10) !important; }
        html.theme-cyber .from-rose-50 { background-image: none !important; background-color: rgba(244, 63, 94, 0.10) !important; }
        html.theme-cyber .from-slate-50 { background-image: none !important; background-color: rgba(34, 211, 238, 0.05) !important; }

        /* Hover */
        html.theme-cyber .hover\:bg-slate-50:hover, html.theme-cyber .hover\:bg-slate-100:hover,
        html.theme-cyber .hover\:bg-slate-200:hover, html.theme-cyber .hover\:bg-emerald-50\/70:hover,
        html.theme-cyber .hover\:bg-emerald-50\/60:hover, html.theme-cyber .hover\:bg-emerald-50\/30:hover,
        html.theme-cyber .hover\:bg-emerald-100:hover { background-color: rgba(34, 211, 238, 0.09) !important; }
        html.theme-cyber tbody tr:hover { background-color: rgba(34, 211, 238, 0.05) !important; }
        html.theme-cyber .hover\:bg-emerald-600:hover { background-color: #0891b2 !important; box-shadow: 0 0 18px rgba(34, 211, 238, 0.45) !important; }

        /* Teks */
        html.theme-cyber .text-slate-900, html.theme-cyber .text-slate-800,
        html.theme-cyber .text-gray-900, html.theme-cyber .text-gray-800 { color: #e6f6ff !important; }
        html.theme-cyber .text-slate-700, html.theme-cyber .text-gray-700 { color: #c3d4e6 !important; }
        html.theme-cyber .text-slate-600, html.theme-cyber .text-gray-600 { color: #9fb3c8 !important; }
        html.theme-cyber .text-slate-500, html.theme-cyber .text-gray-500 { color: #8196ae !important; }
        html.theme-cyber .text-slate-400 { color: #667b93 !important; }
        html.theme-cyber .text-emerald-600, html.theme-cyber .text-emerald-700,
        html.theme-cyber .text-emerald-800, html.theme-cyber .text-emerald-900,
        html.theme-cyber .text-emerald-950 { color: #34d399 !important; text-shadow: 0 0 10px rgba(52, 211, 153, 0.25); }
        html.theme-cyber .text-amber-600, html.theme-cyber .text-amber-700,
        html.theme-cyber .text-amber-800 { color: #fbbf24 !important; }
        html.theme-cyber .text-rose-600, html.theme-cyber .text-rose-700,
        html.theme-cyber .text-rose-800, html.theme-cyber .text-rose-900 { color: #fb7185 !important; }
        html.theme-cyber .text-indigo-600, html.theme-cyber .text-indigo-700 { color: #a5b4fc !important; }
        html.theme-cyber .text-blue-600, html.theme-cyber .text-blue-700 { color: #7dd3fc !important; }
        html.theme-cyber thead th { color: #67e8f9 !important; letter-spacing: 0.08em; }

        /* Garis tepi */
        html.theme-cyber .border-slate-100, html.theme-cyber .border-slate-200, html.theme-cyber .border-slate-200\/90,
        html.theme-cyber .border-slate-200\/80, html.theme-cyber .border-slate-300, html.theme-cyber .border-gray-200,
        html.theme-cyber .border-gray-300 { border-color: rgba(34, 211, 238, 0.16) !important; }
        html.theme-cyber .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        html.theme-cyber .divide-slate-200 > :not([hidden]) ~ :not([hidden]),
        html.theme-cyber .divide-slate-200\/70 > :not([hidden]) ~ :not([hidden]),
        html.theme-cyber .divide-slate-200\/80 > :not([hidden]) ~ :not([hidden]) { border-color: rgba(34, 211, 238, 0.09) !important; }
        html.theme-cyber .border-emerald-200, html.theme-cyber .border-emerald-200\/70, html.theme-cyber .border-emerald-300,
        html.theme-cyber .border-emerald-400 { border-color: rgba(52, 211, 153, 0.38) !important; }
        html.theme-cyber .border-amber-200, html.theme-cyber .border-amber-200\/70 { border-color: rgba(251, 191, 36, 0.35) !important; }
        html.theme-cyber .border-rose-200, html.theme-cyber .border-rose-200\/80 { border-color: rgba(251, 113, 133, 0.35) !important; }
        html.theme-cyber .border-indigo-200, html.theme-cyber .border-blue-200 { border-color: rgba(129, 140, 248, 0.35) !important; }
        html.theme-cyber .ring-white { --tw-ring-color: #05070D !important; }

        /* Formulir */
        html.theme-cyber input:not([type=checkbox]):not([type=radio]),
        html.theme-cyber select,
        html.theme-cyber textarea {
            background-color: rgba(3, 7, 15, 0.88) !important;
            color: #e6f6ff !important;
            border-color: rgba(34, 211, 238, 0.28) !important;
        }
        html.theme-cyber input:not([type=checkbox]):not([type=radio]):focus,
        html.theme-cyber select:focus,
        html.theme-cyber textarea:focus {
            border-color: #22d3ee !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.16), 0 0 16px rgba(34, 211, 238, 0.25) !important;
        }
        html.theme-cyber ::placeholder { color: #5b6f86 !important; }
        html.theme-cyber option { background-color: #0a101e; color: #e6f6ff; }
        html.theme-cyber input[type=date]::-webkit-calendar-picker-indicator { filter: invert(1) hue-rotate(150deg); }

        /* Tombol utama gradien -> cyan ke emerald bercahaya */
        html.theme-cyber .from-emerald-600.to-teal-600 {
            --tw-gradient-from: #0891b2 var(--tw-gradient-from-position) !important;
            --tw-gradient-to: #10b981 var(--tw-gradient-to-position) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
            box-shadow: 0 0 20px -2px rgba(34, 211, 238, 0.45) !important;
        }

        /* Sidebar & navigasi terminal komando */
        html.theme-cyber .app-sidebar { background-color: #03050A !important; border-right-color: rgba(34, 211, 238, 0.18) !important; }
        html.theme-cyber .nav-link {
            font-family: 'Rajdhani', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.82rem;
            font-weight: 700;
        }
        html.theme-cyber .nav-active {
            background: linear-gradient(90deg, rgba(34, 211, 238, 0.22), rgba(34, 211, 238, 0.03)) !important;
            box-shadow: inset 3px 0 0 #22d3ee, 0 0 22px -8px rgba(34, 211, 238, 0.6) !important;
            color: #ecfeff !important;
        }
        html.theme-cyber .nav-active svg { color: #22d3ee !important; }
        html.theme-cyber .laser-line {
            background: linear-gradient(90deg, transparent, #06b6d4, #67e8f9, #06b6d4, transparent);
            box-shadow: 0 0 14px 2px rgba(34, 211, 238, 0.8);
        }
        html.theme-cyber ::-webkit-scrollbar-track { background: #05070D; }
        html.theme-cyber ::-webkit-scrollbar-thumb { background: rgba(34, 211, 238, 0.35); }

        /* Elemen yang WAJIB tetap terang (stiker barcode, kanvas tanda tangan) */
        html.theme-cyber .keep-light {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }
        html.theme-cyber .keep-light * { color: #0f172a !important; border-color: #e2e8f0 !important; text-shadow: none !important; }
        html.theme-cyber .keep-light .bg-slate-100 { background-color: #f1f5f9 !important; }
        html.theme-cyber .keep-light::before, html.theme-cyber .keep-light::after { display: none !important; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; background-image: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased selection:bg-emerald-600 selection:text-white">
    @php
        $menuSections = [
            'Operasional Utama' => [
                [
                    'route' => 'dashboard',
                    'active' => 'dashboard',
                    'code' => '01',
                    'label' => 'Dashboard Analitik',
                    'icon' => 'M3 4a1 1 0 011-1h5a1 1 0 011 1v7a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm11-1a1 1 0 011-1h5a1 1 0 011 1v4a1 1 0 01-1 1h-5a1 1 0 01-1-1V3zm0 9a1 1 0 011-1h5a1 1 0 011 1v7a1 1 0 01-1 1h-5a1 1 0 01-1-1v-7zM3 16a1 1 0 011-1h5a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4z',
                    'color' => 'text-cyan-400',
                    'bg' => 'bg-cyan-500/15 border-cyan-400/30 text-cyan-400 shadow-[0_0_12px_rgba(34,211,238,0.25)]',
                ],
                [
                    'route' => 'scan.index',
                    'active' => 'scan.*',
                    'code' => '02',
                    'label' => 'Scan Barcode (In/Out)',
                    'live' => true,
                    'icon' => 'M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2M3 17v2a2 2 0 002 2h2m10 0h2a2 2 0 002-2v-2M2 12h20M7 8v2m3-2v2m4-2v2m3-2v2m-10 4v2m3-2v2m4-2v2m3-2v2',
                    'color' => 'text-emerald-400',
                    'bg' => 'bg-emerald-500/15 border-emerald-400/30 text-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.25)]',
                ],
                [
                    'route' => 'items.index',
                    'active' => 'items.index',
                    'code' => '03',
                    'label' => 'Data Barang & Gudang',
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4M12 11l4-2M8 9l4 2',
                    'color' => 'text-amber-400',
                    'bg' => 'bg-amber-500/15 border-amber-400/30 text-amber-400 shadow-[0_0_12px_rgba(251,191,36,0.25)]',
                ],
                [
                    'route' => 'items.barcode-studio',
                    'active' => 'items.barcode-studio',
                    'code' => '04',
                    'label' => 'Studio Pembuat Barcode',
                    'icon' => 'M6 9V4a1 1 0 011-1h10a1 1 0 011 1v5M6 18H5a2 2 0 01-2-2v-5a2 2 0 012-2h14a2 2 0 012 2v5a2 2 0 01-2 2h-1M17 12h.01M6 14h12v7a1 1 0 01-1 1H7a1 1 0 01-1-1v-7zm3 2v3m2-3v3m3-3v3m2-3v3',
                    'color' => 'text-purple-400',
                    'bg' => 'bg-purple-500/15 border-purple-400/30 text-purple-400 shadow-[0_0_12px_rgba(167,139,250,0.25)]',
                ],
            ],
            'Dokumen & Pelaporan' => [
                [
                    'route' => 'handovers.index',
                    'active' => 'handovers.*',
                    'code' => '05',
                    'label' => 'Berita Acara (BAST & BAP)',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9 2 2 4-4m-6 5h6',
                    'color' => 'text-sky-400',
                    'bg' => 'bg-sky-500/15 border-sky-400/30 text-sky-400 shadow-[0_0_12px_rgba(56,189,248,0.25)]',
                ],
                [
                    'route' => 'reports.index',
                    'active' => 'reports.*',
                    'code' => '06',
                    'label' => 'Laporan Keluar & Masuk',
                    'icon' => 'M3 3v18h18M7 16v-4m4 4v-7m4 7v-10m4 2l2-2m0 0l2 2m-2-2v7',
                    'color' => 'text-teal-400',
                    'bg' => 'bg-teal-500/15 border-teal-400/30 text-teal-400 shadow-[0_0_12px_rgba(45,212,191,0.25)]',
                ],
            ],
            'Audit & Pengaturan' => [
                [
                    'route' => 'access-logs.index',
                    'active' => 'access-logs.*',
                    'code' => '07',
                    'label' => 'History Akses Web',
                    'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM12 8v4l3 2',
                    'color' => 'text-rose-400',
                    'bg' => 'bg-rose-500/15 border-rose-400/30 text-rose-400 shadow-[0_0_12px_rgba(251,113,133,0.25)]',
                ],
                [
                    'route' => 'profile.edit',
                    'active' => 'profile.*',
                    'code' => '08',
                    'label' => 'Setingan Profil & User',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'color' => 'text-fuchsia-400',
                    'bg' => 'bg-fuchsia-500/15 border-fuchsia-400/30 text-fuchsia-400 shadow-[0_0_12px_rgba(232,121,249,0.25)]',
                ],
            ],
        ];
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- SIDEBAR DESKTOP -->
        <aside class="app-sidebar no-print hidden lg:flex lg:flex-col lg:w-[272px] lg:fixed lg:inset-y-0 bg-[#0B1120] text-slate-200 border-r border-slate-800/80 z-30 shadow-2xl overflow-hidden">
            <div class="pointer-events-none absolute -top-24 -left-24 w-64 h-64 rounded-full bg-emerald-500/10 blur-3xl"></div>
            <div class="cyber-only pointer-events-none absolute -bottom-24 -right-24 w-64 h-64 rounded-full bg-cyan-500/10 blur-3xl"></div>

            <!-- Brand Header -->
            <div class="relative flex items-center gap-3.5 px-6 h-20 border-b border-slate-800/80 bg-slate-950/40">
                <div class="w-11 h-11 rounded-full p-[2px] bg-gradient-to-b from-cyan-300 via-teal-400 to-cyan-500 shadow-[0_0_15px_rgba(34,211,238,0.45)] shrink-0">
                    <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo Korps Brimob Polri TOC Gegana" class="w-full h-full rounded-full object-cover">
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-white tracking-wider text-base leading-none font-mono-code">LOGISTIK.<span class="text-cyan-400">TOC</span></span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 font-medium light-only">Korps Brimob Polri • TOC</div>
                    <div class="text-[10px] text-cyan-400 mt-1 font-mono-code cyber-only flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>By xZeyyOne • ONLINE
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="relative flex-1 px-3.5 py-5 space-y-1 overflow-y-auto">
                @foreach ($menuSections as $sectionLabel => $menus)
                    <div class="px-3 pb-2 {{ $loop->first ? '' : 'pt-5' }} text-[10px] font-bold uppercase tracking-widest text-slate-500">
                        <span class="cyber-only text-cyan-500/70 font-mono-code">//&nbsp;</span>{{ $sectionLabel }}
                    </div>
                    @foreach ($menus as $menu)
                        @php $isActive = request()->routeIs($menu['active']); @endphp
                        <a href="{{ route($menu['route']) }}"
                           class="nav-link group relative flex items-center justify-between px-3 py-2 rounded-xl text-sm font-semibold transition-all duration-200 {{ $isActive ? 'nav-active bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-950/50' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="cyber-only font-mono-code text-[10px] shrink-0 {{ $isActive ? 'text-cyan-200' : 'text-slate-500' }}">[{{ $menu['code'] }}]</span>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 border transition-all duration-200 group-hover:scale-110 {{ $isActive ? 'bg-white/20 border-white/40 text-white shadow-[0_0_12px_rgba(255,255,255,0.4)]' : $menu['bg'] }}">
                                    <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}"/>
                                    </svg>
                                </div>
                                <span class="truncate">{{ $menu['label'] }}</span>
                            </div>
                            @if (!empty($menu['live']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    LIVE
                                </span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <!-- User Footer Card -->
            <div class="relative p-4 border-t border-slate-800/80 bg-slate-950/60">
                <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-extrabold text-sm shrink-0 shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10px] font-mono-code text-cyan-400 cyber-only">OPERATOR</div>
                        <div class="text-sm font-bold text-white leading-tight truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-emerald-400 font-semibold mt-0.5 flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>{{ auth()->user()->isAdmin() ? 'Admin Logistik' : 'Petugas / Staff' }}</span>
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-2.5">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-rose-600 border border-slate-700/70 hover:border-rose-500 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar Sesi (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 lg:pl-[272px] flex flex-col min-h-screen pb-20 lg:pb-0">
            <!-- Top Header Bar -->
            <header class="no-print sticky top-0 z-20 bg-white/85 backdrop-blur-md border-b border-slate-200/80 px-3 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2.5 sm:gap-4 shadow-2xs">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <!-- Mobile Brand Icon & Logo TOC -->
                    <a href="{{ route('dashboard') }}" class="lg:hidden flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-full p-[2px] bg-gradient-to-b from-cyan-300 via-teal-400 to-cyan-500 shadow-[0_0_10px_rgba(34,211,238,0.45)]">
                            <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo TOC" class="w-full h-full rounded-full object-cover">
                        </div>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-base lg:text-lg font-extrabold text-slate-900 tracking-tight leading-tight truncate">
                            <span class="cyber-only text-cyan-400 font-mono-code text-xs sm:text-sm">&gt;_&nbsp;</span>@yield('page_title', 'Dashboard Logistik')
                        </h1>
                        <p class="hidden sm:block text-xs text-slate-500 font-medium truncate">@yield('page_subtitle', 'Sistem Informasi Gudang Penyimpanan & Scan Barcode')</p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    <!-- Sesi HP (Mobile Session Countdown Pill) -->
                    <div class="md:hidden flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50/90 border border-emerald-300 text-[10px] font-bold text-emerald-700 font-mono-code"
                         title="Sisa sesi aktif (15 Menit)">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="session-countdown-mobile">15:00</span>
                    </div>

                    <!-- HUD status (Mode Cyber) -->
                    <div class="cyber-only hidden 2xl:flex items-center gap-2 px-3 py-1.5 rounded-xl border border-emerald-400/30 bg-emerald-500/10 text-[11px] font-mono-code text-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        DB LINK: CONNECTED
                    </div>

                    <!-- Indikator Sesi 15 Menit (Desktop) -->
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700"
                         title="Sesi aktif 15 menit (Otomatis reset saat beraktivitas & kembali ke login jika 15 menit tanpa aktivitas)">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-mono-code">SESI: <strong id="session-countdown-timer">15:00</strong></span>
                    </div>

                    <!-- Jam WIB live (Desktop) -->
                    <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100/90 border border-slate-200/80 text-xs font-medium text-slate-700">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="live-wib-clock" class="font-mono-code font-semibold text-slate-800">{{ now()->format('d/m/Y • H:i:s') }} WIB</span>
                    </div>

                    <!-- Pengalih Tema -->
                    <button type="button" onclick="toggleAppTheme()" title="Ganti tema tampilan"
                            class="inline-flex items-center gap-1 px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition-all">
                        <span class="cyber-only inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="hidden sm:inline">Mode Terang</span>
                        </span>
                        <span class="light-only items-center gap-1.5 inline-flex">
                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <span class="hidden sm:inline">Mode Cyber</span>
                        </span>
                    </button>

                    <a href="{{ route('scan.index', ['camera' => 1]) }}"
                       onclick="if ((window.location.pathname.endsWith('/scan') || window.location.pathname.indexOf('/scan') !== -1) && typeof startCameraScanner === 'function') { event.preventDefault(); startCameraScanner(true); return false; }"
                       class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-sm shadow-emerald-600/20 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span>Scan Barcode</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="hidden md:inline-flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 shadow-2xs transition-all duration-200">
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-emerald-400 flex items-center justify-center font-extrabold text-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </span>
                        <div class="text-left leading-tight">
                            <div class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-400 font-medium">{{ auth()->user()->isAdmin() ? 'Admin' : 'Staff' }}</div>
                        </div>
                    </a>

                    <button type="button" onclick="toggleMobileDrawer()"
                            class="lg:hidden inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="no-print px-4 sm:px-6 lg:px-8 pt-4">
                @if (session('success'))
                    <div id="flash-success" class="mb-4 flex items-start justify-between gap-3 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50/70 border border-emerald-200 text-emerald-950 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Berhasil Diproses</div>
                                <div class="text-sm font-semibold text-slate-900 mt-0.5">{{ session('success') }}</div>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('flash-success').remove()" class="text-emerald-600 hover:text-emerald-900 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div id="flash-error" class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-rose-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-rose-900">Periksa kembali data yang Anda masukkan:</div>
                                    <ul class="mt-1 text-xs list-disc list-inside space-y-0.5 text-rose-800 font-medium">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button type="button" onclick="document.getElementById('flash-error').remove()" class="text-rose-600 hover:text-rose-900 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="no-print border-t border-slate-200/80 bg-white/80 backdrop-blur px-4 sm:px-6 lg:px-8 py-3.5 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>&copy; {{ date('Y') }} <strong class="text-slate-800">LogiScan Pro</strong> — Sistem Gudang Penyimpanan & Scan Barcode Logistik (Laravel 12)</span>
                </div>
                <div>Petugas Aktif: <strong class="text-slate-800">{{ auth()->user()->name }}</strong> ({{ strtoupper(auth()->user()->role) }})</div>
            </footer>
        </div>

        <!-- MOBILE BOTTOM DOCK (Native App Style with Elevated Center Scan Action) -->
        <nav class="no-print lg:hidden fixed bottom-0 inset-x-0 z-30 bg-[#0B1120]/95 backdrop-blur-lg border-t border-cyan-500/20 shadow-[0_-8px_30px_rgba(0,0,0,0.6)] px-2 pt-1 pb-2">
            <div class="grid grid-cols-5 items-end max-w-md mx-auto">
                <!-- 1. Dashboard -->
                <a href="{{ route('dashboard') }}" class="group flex flex-col items-center justify-center gap-1 py-1 text-[10px] font-bold transition-all {{ request()->routeIs('dashboard') ? 'text-cyan-400' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-active:scale-95 {{ request()->routeIs('dashboard') ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-400/30 shadow-[0_0_10px_rgba(34,211,238,0.3)]' : 'bg-slate-900/60 text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menuSections['Operasional Utama'][0]['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="leading-none tracking-tight">Beranda</span>
                </a>

                <!-- 2. Barang & Stok -->
                <a href="{{ route('items.index') }}" class="group flex flex-col items-center justify-center gap-1 py-1 text-[10px] font-bold transition-all {{ request()->routeIs('items.*') ? 'text-amber-400' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-active:scale-95 {{ request()->routeIs('items.*') ? 'bg-amber-500/20 text-amber-400 border border-amber-400/30 shadow-[0_0_10px_rgba(251,191,36,0.3)]' : 'bg-slate-900/60 text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menuSections['Operasional Utama'][2]['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="leading-none tracking-tight">Barang</span>
                </a>

                <!-- 3. CENTER ELEVATED SCAN BUTTON (FAB) -->
                <div class="flex flex-col items-center justify-center -mt-5">
                    <a href="{{ route('scan.index', ['camera' => 1]) }}"
                       onclick="if ((window.location.pathname.endsWith('/scan') || window.location.pathname.indexOf('/scan') !== -1) && typeof startCameraScanner === 'function') { event.preventDefault(); startCameraScanner(true); return false; }"
                       title="Buka Terminal Scan Barcode & Langsung Nyalakan Kamera"
                       class="group relative flex items-center justify-center w-13 h-13 rounded-full bg-gradient-to-tr from-emerald-600 via-teal-500 to-cyan-400 text-white shadow-[0_0_20px_rgba(52,211,153,0.55)] border-2 border-emerald-300/80 transition-transform active:scale-90 hover:scale-105">
                        <div class="absolute inset-0 rounded-full bg-emerald-400/30 animate-ping pointer-events-none"></div>
                        <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menuSections['Operasional Utama'][1]['icon'] }}"/>
                        </svg>
                    </a>
                    <span class="text-[9px] font-extrabold uppercase font-mono-code text-emerald-400 mt-1 leading-none">SCAN</span>
                </div>

                <!-- 4. Berita Acara BAST/BAP -->
                <a href="{{ route('handovers.index') }}" class="group flex flex-col items-center justify-center gap-1 py-1 text-[10px] font-bold transition-all {{ request()->routeIs('handovers.*') ? 'text-sky-400' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-transform group-active:scale-95 {{ request()->routeIs('handovers.*') ? 'bg-sky-500/20 text-sky-400 border border-sky-400/30 shadow-[0_0_10px_rgba(56,189,248,0.3)]' : 'bg-slate-900/60 text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menuSections['Dokumen & Pelaporan'][0]['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="leading-none tracking-tight">BAST/BAP</span>
                </a>

                <!-- 5. Drawer Lainnya -->
                <button type="button" onclick="toggleMobileDrawer()" class="group flex flex-col items-center justify-center gap-1 py-1 text-[10px] font-bold text-slate-400 hover:text-white transition-all">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-900/60 text-slate-400 group-hover:text-white transition-transform group-active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </div>
                    <span class="leading-none tracking-tight">Lainnya</span>
                </button>
            </div>
        </nav>

        <!-- MOBILE OVERFLOW DRAWER -->
        <div id="mobile-drawer" class="no-print fixed inset-0 z-50 hidden">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="toggleMobileDrawer()"></div>
            <div class="app-sidebar fixed right-0 inset-y-0 w-72 bg-[#0B1120] text-white p-5 flex flex-col justify-between shadow-2xl">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <span class="font-bold text-base">Menu Navigasi Lengkap</span>
                        <button type="button" onclick="toggleMobileDrawer()" class="p-1.5 rounded-lg text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-4 space-y-3 overflow-y-auto max-h-[calc(100vh-210px)] pr-1">
                        @foreach ($menuSections as $sectionLabel => $menus)
                            <div>
                                <div class="px-2 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                    <span class="cyber-only text-cyan-500/70 font-mono-code">//&nbsp;</span>{{ $sectionLabel }}
                                </div>
                                <div class="space-y-1">
                                    @foreach ($menus as $menu)
                                        @php $isActive = request()->routeIs($menu['active']); @endphp
                                        <a href="{{ route($menu['route']) }}"
                                           class="nav-link flex items-center justify-between px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 {{ $isActive ? 'nav-active bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="cyber-only font-mono-code text-[10px] shrink-0 {{ $isActive ? 'text-cyan-200' : 'text-slate-500' }}">[{{ $menu['code'] }}]</span>
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 border {{ $isActive ? 'bg-white/20 border-white/40 text-white' : $menu['bg'] }}">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}"/>
                                                    </svg>
                                                </div>
                                                <span class="truncate">{{ $menu['label'] }}</span>
                                            </div>
                                            @if (!empty($menu['live']))
                                                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' }}">LIVE</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="space-y-2">
                    <button type="button" onclick="toggleAppTheme()" class="w-full py-2.5 rounded-xl border border-slate-700 text-sm font-bold text-slate-200 hover:bg-slate-800">
                        <span class="cyber-only">Ganti ke Mode Terang</span>
                        <span class="light-only">Ganti ke Mode Cyber</span>
                    </button>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm">
                            Keluar (Logout)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileDrawer() {
            var drawer = document.getElementById('mobile-drawer');
            if (drawer) drawer.classList.toggle('hidden');
        }

        function toggleAppTheme() {
            var root = document.documentElement;
            var toCyber = !root.classList.contains('theme-cyber');
            root.classList.toggle('theme-cyber', toCyber);
            try {
                localStorage.setItem('logiscan-theme', toCyber ? 'cyber' : 'light');
            } catch (e) {}
        }

        // Saat mencetak (stiker barcode, dll) selalu gunakan tampilan terang
        var cyberBeforePrint = false;
        window.addEventListener('beforeprint', function () {
            cyberBeforePrint = document.documentElement.classList.contains('theme-cyber');
            document.documentElement.classList.remove('theme-cyber');
        });
        window.addEventListener('afterprint', function () {
            if (cyberBeforePrint) document.documentElement.classList.add('theme-cyber');
        });

        function renderAllBarcodes() {
            if (typeof JsBarcode === 'undefined') return;
            var elements = document.querySelectorAll('.js-barcode');
            elements.forEach(function (el) {
                var code = el.getAttribute('data-barcode');
                var height = parseInt(el.getAttribute('data-height') || '36', 10);
                var width = parseFloat(el.getAttribute('data-width') || '1.4');
                var fontSize = parseInt(el.getAttribute('data-fontsize') || '11', 10);
                var margin = parseInt(el.getAttribute('data-margin') || '4', 10);
                if (code) {
                    try {
                        JsBarcode(el, code, {
                            format: 'CODE128',
                            lineColor: '#0f172a',
                            background: '#ffffff',
                            width: width,
                            height: height,
                            displayValue: true,
                            fontSize: fontSize,
                            margin: margin
                        });
                    } catch (err) {}
                }
            });
        }

        function openPhotoLightbox(url, title) {
            if (!url) return;
            var modal = document.getElementById('global-photo-lightbox');
            var img = document.getElementById('global-photo-lightbox-img');
            var caption = document.getElementById('global-photo-lightbox-title');
            if (!modal || !img) return;
            img.src = url;
            if (caption) caption.textContent = title || 'Foto Barang Logistik';
            modal.classList.remove('hidden');
        }

        function closePhotoLightbox() {
            var modal = document.getElementById('global-photo-lightbox');
            if (modal) modal.classList.add('hidden');
        }

        // Fitur 15 Menit Auto-Login / Sesi Aktif (900 Detik)
        function startSession15MinTimer() {
            var maxSeconds = 15 * 60; // 15 menit = 900 detik
            var remaining = maxSeconds;
            var timerEl = document.getElementById('session-countdown-timer');

            function resetTimer() {
                remaining = maxSeconds;
                try {
                    sessionStorage.setItem('toc_last_activity', String(Date.now()));
                } catch (e) {}
            }

            ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
                document.addEventListener(evt, resetTimer, { passive: true });
            });

            setInterval(function () {
                remaining--;
                if (remaining < 0) remaining = 0;
                var mm = String(Math.floor(remaining / 60)).padStart(2, '0');
                var ss = String(remaining % 60).padStart(2, '0');
                var timeText = mm + ':' + ss;
                if (timerEl) {
                    timerEl.textContent = timeText;
                }
                var mobileTimerEl = document.getElementById('session-countdown-mobile');
                if (mobileTimerEl) {
                    mobileTimerEl.textContent = timeText;
                }
                if (remaining === 0) {
                    var logoutForm = document.getElementById('auto-logout-form');
                    if (logoutForm) {
                        logoutForm.submit();
                    } else {
                        window.location.href = '{{ route('login') }}';
                    }
                }
            }, 1000);
        }

        function startLiveClock() {
            var clockEl = document.getElementById('live-wib-clock');
            if (!clockEl) return;
            setInterval(function () {
                var now = new Date();
                var d = String(now.getDate()).padStart(2, '0');
                var m = String(now.getMonth() + 1).padStart(2, '0');
                var y = now.getFullYear();
                var hh = String(now.getHours()).padStart(2, '0');
                var mm = String(now.getMinutes()).padStart(2, '0');
                var ss = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = d + '/' + m + '/' + y + ' • ' + hh + ':' + mm + ':' + ss + ' WIB';
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderAllBarcodes();
            startLiveClock();
            startSession15MinTimer();
        });
    </script>

    <!-- Form Auto-Logout 15 Menit -->
    <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Modal Lightbox Foto Barang Global -->
    <div id="global-photo-lightbox" class="no-print fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closePhotoLightbox()"></div>
        <div class="relative z-10 max-w-lg w-full bg-white rounded-2xl border border-slate-200 shadow-2xl overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                <h4 id="global-photo-lightbox-title" class="text-sm font-bold truncate">Foto Barang Logistik</h4>
                <button type="button" onclick="closePhotoLightbox()" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-slate-950/40">
                <img id="global-photo-lightbox-img" src="" alt="Foto Barang" class="max-h-[420px] w-auto rounded-xl object-contain">
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
