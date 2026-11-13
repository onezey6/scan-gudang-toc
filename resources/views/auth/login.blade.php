<!DOCTYPE html>
<html lang="id" class="h-full bg-[#040C10]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGISTIK.TOC — Secure System Terminal (Korps Brimob Polri)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'JetBrains Mono', monospace;
            background-color: #040c10;
            background-image:
                linear-gradient(to bottom, rgba(4, 12, 16, 0.50) 0%, rgba(4, 12, 16, 0.30) 40%, rgba(4, 12, 16, 0.75) 100%),
                url('{{ asset('images/bg-login.jpg') }}');
            background-size: cover;
            background-position: center bottom;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }

        .toc-card {
            background: linear-gradient(180deg, rgba(8, 20, 34, 0.86) 0%, rgba(5, 14, 24, 0.92) 100%);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(34, 211, 238, 0.42);
            box-shadow:
                0 0 60px -10px rgba(6, 182, 212, 0.40),
                0 30px 60px -15px rgba(0, 0, 0, 0.88),
                inset 0 1px 0 rgba(103, 232, 249, 0.28);
        }

        .toc-logo-ring {
            box-shadow:
                0 0 28px rgba(34, 211, 238, 0.65),
                0 0 8px rgba(45, 212, 191, 0.8),
                inset 0 0 12px rgba(34, 211, 238, 0.45);
        }

        .toc-title-glow {
            text-shadow: 0 0 16px rgba(34, 211, 238, 0.65);
        }

        /* Animasi Kedap-Kedip Neon untuk "By xZeyyOne" */
        @keyframes blinkXzeyyNeon {
            0%, 100% {
                opacity: 1;
                color: #22d3ee;
                text-shadow: 0 0 12px rgba(34, 211, 238, 0.95), 0 0 24px rgba(20, 184, 166, 0.75);
            }
            40% {
                opacity: 0.22;
                color: #67e8f9;
                text-shadow: none;
            }
            55% {
                opacity: 0.95;
                color: #22e6b8;
                text-shadow: 0 0 16px rgba(34, 230, 184, 0.9);
            }
            70% {
                opacity: 0.18;
                text-shadow: none;
            }
        }

        .blink-xzeyy {
            animation: blinkXzeyyNeon 1.8s infinite ease-in-out;
            display: inline-block;
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
            transform: scale(1.01);
        }

        .toc-input::placeholder {
            color: #64748b;
        }

        .toc-btn-submit {
            position: relative;
            overflow: hidden;
            background: linear-gradient(90deg, #22e6b8 0%, #15c5e0 50%, #0b84f3 100%);
            box-shadow:
                0 0 28px -2px rgba(21, 197, 224, 0.60),
                0 8px 20px -4px rgba(11, 132, 243, 0.45);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, filter 0.2s ease;
        }

        .toc-btn-submit:hover {
            filter: brightness(1.08);
            box-shadow:
                0 0 36px 0px rgba(34, 230, 184, 0.75),
                0 10px 24px -4px rgba(11, 132, 243, 0.6);
            transform: scale(1.025) translateY(-1px);
        }

        .toc-btn-submit:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-200 relative overflow-x-hidden select-none">

    <!-- Ambient Center Glow -->
    <div class="pointer-events-none fixed inset-0 flex items-center justify-center">
        <div class="w-[520px] h-[520px] rounded-full bg-teal-500/10 blur-[110px]"></div>
    </div>

    <!-- Top Spacer -->
    <div class="h-6"></div>

    <!-- Main Centered Login Container -->
    <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-8">
        <div class="w-full max-w-[400px] toc-card rounded-[26px] px-8 pt-8 pb-8">

            <!-- Circular Logo Badge with Green Online Indicator -->
            <div class="relative mx-auto w-[96px] h-[96px] flex items-center justify-center">
                <!-- Outer Glowing Cyan Ring -->
                <div class="w-full h-full rounded-full p-[3px] bg-gradient-to-b from-cyan-300 via-teal-400 to-cyan-500 toc-logo-ring">
                    <div class="w-full h-full rounded-full bg-[#06121a] p-[3px] flex items-center justify-center overflow-hidden border border-cyan-300/40">
                        <img src="{{ asset('images/logo-toc.jpg') }}"
                             alt="Logo Korps Brimob Polri - Tactical Operation Centre Gegana"
                             class="w-full h-full rounded-full object-cover">
                    </div>
                </div>

                <!-- Green Active Dot Indicator (Top-Right) -->
                <span class="absolute -top-0.5 right-1 w-4 h-4 rounded-full bg-[#071720] border border-cyan-400/50 flex items-center justify-center shadow-[0_0_10px_rgba(34,211,238,0.5)]"
                      title="Terminal Online">
                    <span class="w-2 h-2 rounded-full bg-[#22c55e] shadow-[0_0_8px_#22c55e]"></span>
                </span>
            </div>

            <!-- Title & Creator Subtitle (Blinking Neon) -->
            <div class="text-center mt-5">
                <h1 class="text-[22px] font-extrabold tracking-wider text-white">
                    LOGISTIK.<span class="text-[#22d3ee] toc-title-glow">TOC</span>
                </h1>
                <p class="mt-1 text-[11px] font-bold tracking-wide blink-xzeyy">
                    By xZeyyOne
                </p>
            </div>

            <!-- Alert Notifications -->
            @if (session('success'))
                <div class="mt-5 px-3.5 py-2.5 rounded-xl border border-emerald-400/40 bg-emerald-500/10 text-[11px] font-semibold text-emerald-300 text-center">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-5 px-3.5 py-2.5 rounded-xl border border-rose-400/50 bg-rose-500/10 text-[11px] font-semibold text-rose-300 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Login Form (Username & Password Dikosongkan) -->
            <form action="{{ route('login.submit') }}" method="POST" class="mt-7 space-y-5" autocomplete="off">
                @csrf

                <!-- IDENTITAS_USERNAME -->
                <div>
                    <label for="login" class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-2">
                        IDENTITAS_USERNAME:
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="login"
                               name="login"
                               value="{{ old('login') }}"
                               required
                               autofocus
                               placeholder="Masukkan username..."
                               class="ios-touch-target toc-input w-full pl-4 pr-11 py-3.5 rounded-xl text-xs font-medium tracking-wide select-text">
                        <div class="pointer-events-none absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#22d3ee]/70">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- KATA_SANDI / PIN -->
                <div>
                    <label for="password" class="block text-[10px] font-bold uppercase tracking-wider text-[#22d3ee]/90 mb-2">
                        KATA_SANDI / PIN:
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password"
                               name="password"
                               value=""
                               required
                               placeholder="Masukkan kata sandi / PIN..."
                               class="ios-touch-target toc-input w-full pl-4 pr-11 py-3.5 rounded-xl text-xs font-medium tracking-widest select-text">
                        <button type="button"
                                onclick="togglePassVisibility()"
                                id="btn-toggle-pass"
                                title="Tampilkan / Sembunyikan Kata Sandi"
                                class="ios-touch-target absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#22d3ee]/70 hover:text-[#22d3ee] transition-colors">
                            <!-- Eye Icon -->
                            <svg id="icon-eye-open" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <!-- Eye Off Icon -->
                            <svg id="icon-eye-closed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="remember" value="1">

                <!-- Submit Button: BUKA_AKSES_SISTEM -->
                <div class="pt-1">
                    <button type="submit"
                            class="ios-touch-target toc-btn-submit w-full py-3.5 px-5 rounded-xl text-[#04121C] font-extrabold text-xs tracking-wider uppercase flex items-center justify-center gap-2.5">
                        <svg class="w-4 h-4 stroke-[2.4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>BUKA_AKSES_SISTEM</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Caption Below Card -->
        <div class="mt-5 text-center text-[10px] sm:text-[11px] font-semibold uppercase tracking-[0.22em] text-[#22d3ee]/75">
            POLRI &bull; CREATED BY XZEYYONE &bull; SECURE SYSTEM TERMINAL
        </div>
    </main>

    <!-- Bottom Full-Width Footer Bar -->
    <footer class="relative z-10 w-full px-6 sm:px-10 py-3.5 border-t border-cyan-500/10 bg-[#03090D]/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-[10px] sm:text-[11px]">
        <div class="text-slate-500 tracking-wide text-center sm:text-left">
            &copy; {{ date('Y') }} INVENTARIS TOC &bull; KORPS BRIMOB POLRI &bull; Dikelola oleh Tim Logistik
        </div>
        <div class="text-[#22d3ee]/85 font-semibold tracking-wider text-center sm:text-right">
            Created By xZeyyOne &bull; ALL RIGHTS RESERVED
        </div>
    </footer>

    <script>
        function togglePassVisibility() {
            var passInput = document.getElementById('password');
            var eyeOpen = document.getElementById('icon-eye-open');
            var eyeClosed = document.getElementById('icon-eye-closed');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
