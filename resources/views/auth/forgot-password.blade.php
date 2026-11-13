<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi / Reset PIN — LOGISTIK.TOC Korps Brimob Polri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;700;800&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #03050A;
            background-image:
                linear-gradient(rgba(34, 211, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, 0.05) 1px, transparent 1px),
                radial-gradient(ellipse at top, rgba(34, 211, 238, 0.12), transparent 55%),
                radial-gradient(ellipse at bottom, rgba(16, 185, 129, 0.08), transparent 55%);
            background-size: 36px 36px, 36px 36px, 100% 100%, 100% 100%;
        }

        .font-mono-code { font-family: 'JetBrains Mono', monospace; }

        .toc-card {
            background: linear-gradient(180deg, rgba(12, 20, 32, 0.96) 0%, rgba(6, 11, 18, 0.98) 100%);
            border: 1px solid rgba(34, 211, 238, 0.28);
            box-shadow:
                0 0 50px -10px rgba(34, 211, 238, 0.22),
                0 25px 45px -12px rgba(0, 0, 0, 0.85),
                inset 0 1px 0 rgba(34, 211, 238, 0.25);
        }

        .toc-input {
            background-color: rgba(25, 37, 52, 0.92);
            border: 1px solid rgba(71, 85, 105, 0.35);
            color: #f1f5f9;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .toc-input:focus {
            outline: none;
            border-color: rgba(34, 211, 238, 0.75);
            background-color: rgba(28, 42, 59, 0.98);
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.16), 0 0 22px rgba(34, 211, 238, 0.26);
        }

        .toc-btn-submit {
            background: linear-gradient(90deg, #22e6b8 0%, #15c5e0 50%, #0b84f3 100%);
            box-shadow: 0 0 28px -2px rgba(21, 197, 224, 0.60);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-200 relative overflow-x-hidden p-4">

    <!-- Top Spacer -->
    <div class="h-4"></div>

    <main class="w-full max-w-[440px] mx-auto my-auto relative z-10">
        <div class="toc-card rounded-3xl p-6 sm:p-8 backdrop-blur-xl">

            <!-- Logo & Brand Header -->
            <div class="text-center">
                <div class="inline-block relative">
                    <div class="w-20 h-20 rounded-full p-[3px] bg-gradient-to-b from-cyan-300 via-teal-400 to-cyan-500 shadow-[0_0_25px_rgba(34,211,238,0.6)] mx-auto">
                        @if (file_exists(public_path('images/logo-toc.jpg')))
                            <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo Korps Brimob Polri TOC Gegana" class="w-full h-full rounded-full object-cover">
                        @else
                            <div class="w-full h-full rounded-full bg-slate-900 flex items-center justify-center font-bold text-cyan-400">TOC</div>
                        @endif
                    </div>
                </div>

                <h1 class="mt-4 text-xl font-extrabold tracking-wider text-white">LUPA KATA SANDI</h1>
                <p class="text-xs text-slate-400 mt-1">Pemulihan Akun via Email (Gmail) atau Nomor HP Terdaftar</p>
            </div>

            @if ($errors->any())
                <div class="mt-4 px-3.5 py-2.5 rounded-xl border border-rose-400/50 bg-rose-500/10 text-xs font-semibold text-rose-300 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="mt-4 px-3.5 py-2.5 rounded-xl border border-emerald-400/50 bg-emerald-500/10 text-xs font-semibold text-emerald-300 text-center">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Permintaan Reset -->
            <form action="{{ route('password.email') }}" method="POST" class="mt-6 space-y-4" autocomplete="off">
                @csrf

                <!-- IDENTITAS -->
                <div>
                    <label for="identity" class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-1.5">
                        IDENTITAS AKUN (EMAIL / USERNAME / NO HP):
                    </label>
                    <input type="text"
                           id="identity"
                           name="identity"
                           value="{{ old('identity') }}"
                           required
                           autofocus
                           placeholder="Contoh: admin@logiscan.id atau 0812-..."
                           class="toc-input w-full px-4 py-3 rounded-xl text-xs font-medium tracking-wide">
                </div>

                <!-- PILIH METODE VERIFIKASI -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-2">
                        KIRIM KODE VERIFIKASI MELALUI:
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="p-3 rounded-xl border border-cyan-500/30 bg-slate-900/80 cursor-pointer flex items-center gap-2 hover:border-cyan-400 transition-colors">
                            <input type="radio" name="reset_channel" value="email" checked class="text-cyan-500 focus:ring-0">
                            <div>
                                <div class="font-bold text-white">Gmail / Email</div>
                                <div class="text-[10px] text-slate-400">Tautan Akun</div>
                            </div>
                        </label>
                        <label class="p-3 rounded-xl border border-cyan-500/30 bg-slate-900/80 cursor-pointer flex items-center gap-2 hover:border-cyan-400 transition-colors">
                            <input type="radio" name="reset_channel" value="phone" class="text-cyan-500 focus:ring-0">
                            <div>
                                <div class="font-bold text-white">Nomor HP / WA</div>
                                <div class="text-[10px] text-slate-400">Kontak Ponsel</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="toc-btn-submit w-full py-3.5 px-5 rounded-xl text-[#04121C] font-extrabold text-xs tracking-wider uppercase flex items-center justify-center gap-2 shadow-lg transition-transform hover:scale-[1.02]">
                        <span>LANJUTKAN &rarr; KIRIM KODE VERIFIKASI</span>
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('login') }}" class="text-xs text-[#22d3ee]/80 hover:text-[#22d3ee] underline font-semibold">
                    &larr; Kembali ke Halaman Login
                </a>
            </div>

        </div>
    </main>

    <footer class="text-center text-[10px] text-slate-500 py-3">
        &copy; {{ date('Y') }} Korps Brimob Polri TOC &bull; Sistem Informasi Logistik Pro
    </footer>

</body>
</html>
