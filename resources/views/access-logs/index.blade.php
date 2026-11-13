@extends('layouts.app')

@section('title', 'History Akses Keluar-Masuk Web')
@section('page_title', 'History Akses Keluar-Masuk Web (Audit Trail)')
@section('page_subtitle', 'Rekam jejak waktu login, logout, gagal login, serta aktivitas operasional seluruh pengguna di web')

@section('content')
    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-emerald-700">Login Masuk Hari Ini</div>
            <div class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($summary['today_logins']) }}x</div>
            <div class="text-xs text-slate-500">Sesi masuk web hari ini</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-slate-600">Logout Keluar Hari Ini</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['today_logouts']) }}x</div>
            <div class="text-xs text-slate-500">Sesi keluar web hari ini</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-blue-700">Log Aktivitas Sistem</div>
            <div class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($summary['total_activities']) }}</div>
            <div class="text-xs text-slate-500">Tambah barang, scan & berita acara</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-rose-700">Percobaan Gagal Login</div>
            <div class="mt-1 text-2xl font-bold text-rose-600">{{ number_format($summary['failed_logins']) }}</div>
            <div class="text-xs text-slate-500">Deteksi keamanan akses</div>
        </div>
    </div>

    <!-- Filter Form -->
    <form action="{{ route('access-logs.index') }}" method="GET" class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Cari Nama / IP / Aktivitas</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Ketik nama user, IP address, perangkat, atau aktivitas..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Jenis Akses</label>
                <select name="event_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white">
                    <option value="">Semua Riwayat</option>
                    <option value="login" {{ request('event_type') === 'login' ? 'selected' : '' }}>Masuk Web (Login)</option>
                    <option value="logout" {{ request('event_type') === 'logout' ? 'selected' : '' }}>Keluar Web (Logout)</option>
                    <option value="activity" {{ request('event_type') === 'activity' ? 'selected' : '' }}>Aktivitas Operasional</option>
                    <option value="failed_login" {{ request('event_type') === 'failed_login' ? 'selected' : '' }}>Gagal Login</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Pengguna (User)</label>
                <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white">
                    <option value="">Semua User</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold">
                    Filter
                </button>
                <a href="{{ route('access-logs.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs sm:text-sm font-medium">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Access Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fluid">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Waktu Akses</th>
                        <th class="py-3.5 px-4">Pengguna & Peran</th>
                        <th class="py-3.5 px-4">Status Keluar / Masuk Web</th>
                        <th class="py-3.5 px-4">Detail Aktivitas</th>
                        <th class="py-3.5 px-4">IP Address & Perangkat</th>
                        @if (auth()->user()->isAdmin())
                            <th class="py-3.5 px-4">Lokasi Real-Time (Admin Only)</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/80 text-sm">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/90 transition-colors">
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-mono-code text-xs font-semibold text-slate-900">{{ $log->accessed_at?->format('d/m/Y H:i:s') }} WIB</div>
                                <div class="text-[11px] text-slate-400">{{ $log->accessed_at?->diffForHumans() }}</div>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">{{ $log->user_name }}</div>
                                <div class="text-xs text-slate-500">{{ $log->user_email ?: '-' }}</div>
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-100 text-slate-700">
                                    {{ $log->user_role ?: '-' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                @if ($log->event_type === 'login')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        MASUK WEB (LOGIN)
                                    </span>
                                @elseif ($log->event_type === 'logout')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        KELUAR WEB (LOGOUT)
                                    </span>
                                @elseif ($log->event_type === 'failed_login')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        GAGAL LOGIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        AKTIVITAS SISTEM
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-top text-xs sm:text-sm text-slate-700">
                                {{ $log->description }}
                            </td>

                            <td class="py-3.5 px-4 align-top">
                                <div class="font-mono-code text-xs font-semibold text-slate-800">{{ $log->ip_address }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $log->device_browser }}</div>
                            </td>

                            @if (auth()->user()->isAdmin())
                                <td class="py-3.5 px-4 align-top">
                                    @if ($log->google_maps_url)
                                        <div class="space-y-1">
                                            <a href="{{ $log->google_maps_url }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-2xs transition-all">
                                                <span>📍 Buka Google Maps</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                            <div class="font-mono-code text-[10px] text-slate-500">
                                                {{ number_format($log->latitude, 5) }}, {{ number_format($log->longitude, 5) }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 italic">
                                            <span>GPS Offline</span>
                                        </span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="py-10 text-center text-sm text-slate-500">
                                Tidak ada riwayat akses web yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
