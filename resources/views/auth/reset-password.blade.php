<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setel Kata Sandi Baru — LOGISTIK.TOC Korps Brimob Polri</title>
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
                    <div class="w-16 h-16 rounded-full p-[2.5px] bg-gradient-to-b from-cyan-300 via-teal-400 to-cyan-500 shadow-[0_0_20px_rgba(34,211,238,0.5)] mx-auto">
                        @if (file_exists(public_path('images/logo-toc.jpg')))
                            <img src="{{ asset('images/logo-toc.jpg') }}" alt="Logo Korps Brimob Polri TOC Gegana" class="w-full h-full rounded-full object-cover">
                        @else
                            <div class="w-full h-full rounded-full bg-slate-900 flex items-center justify-center font-bold text-cyan-400">TOC</div>
                        @endif
                    </div>
                </div>

                <h1 class="mt-3 text-lg font-extrabold tracking-wider text-white">SETEL KATA SANDI BARU</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Akun: <strong class="text-cyan-300">{{ session('reset_user_name') }}</strong>
                </p>
            </div>

            <!-- Box Kode Verifikasi Siap Pakai (Untuk kemudahan operasional lokal/intranet) -->
            @if (session('reset_demo_otp'))
                <div class="mt-4 p-3 rounded-2xl border border-cyan-500/40 bg-cyan-950/40 text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">KODE VERIFIKASI RESMI TERVERIFIKASI:</div>
                    <div class="mt-1 font-mono-code text-2xl font-extrabold tracking-[0.3em] text-white">
                        {{ session('reset_demo_otp') }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Kode telah dikirimkan ke {{ session('reset_channel') === 'phone' ? 'Nomor HP' : 'Gmail' }} terdaftar</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 px-3.5 py-2.5 rounded-xl border border-rose-400/50 bg-rose-500/10 text-xs font-semibold text-rose-300 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form Reset Sandi Baru -->
            <form action="{{ route('password.update') }}" method="POST" class="mt-5 space-y-4" autocomplete="off">
                @csrf

                <!-- KODE VERIFIKASI OTP -->
                <div>
                    <label for="otp" class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-1.5">
                        KODE VERIFIKASI (6 DIGIT):
                    </label>
                    <input type="text"
                           id="otp"
                           name="otp"
                           value="{{ old('otp', session('reset_demo_otp')) }}"
                           required
                           maxlength="6"
                           placeholder="Masukkan 6 digit angka..."
                           class="toc-input w-full px-4 py-3 rounded-xl text-center font-mono-code text-base font-bold tracking-widest text-cyan-300">
                </div>

                <!-- KATA SANDI BARU -->
                <div>
                    <label for="password" class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-1.5">
                        KATA SANDI / PIN BARU:
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           placeholder="Minimal 6 karakter..."
                           class="toc-input w-full px-4 py-3 rounded-xl text-xs font-medium tracking-wide">
                </div>

                <!-- KONFIRMASI KATA SANDI BARU -->
                <div>
                    <label for="password_confirmation" class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-1.5">
                        ULANGI KATA SANDI BARU:
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           required
                           placeholder="Ulangi kata sandi baru..."
                           class="toc-input w-full px-4 py-3 rounded-xl text-xs font-medium tracking-wide">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="toc-btn-submit w-full py-3.5 px-5 rounded-xl text-[#04121C] font-extrabold text-xs tracking-wider uppercase flex items-center justify-center gap-2 shadow-lg transition-transform hover:scale-[1.02]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>SIMPAN KATA SANDI BARU</span>
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('login') }}" class="text-xs text-[#22d3ee]/80 hover:text-[#22d3ee] underline font-semibold">
                    &larr; Batal & Kembali ke Login
                </a>
            </div>

        </div>
    </main>

    <footer class="text-center text-[10px] text-slate-500 py-3">
        &copy; {{ date('Y') }} Korps Brimob Polri TOC &bull; Sistem Informasi Logistik Pro
    </footer>

</body>
</html>
