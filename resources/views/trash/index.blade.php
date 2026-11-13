@extends('layouts.app')

@section('title', 'Menu Sampah (Recycle Bin)')
@section('page_title', 'Menu Sampah (Recycle Bin)')
@section('page_subtitle', 'Penampungan data Berita Acara dan Barang Inventaris yang telah dihapus sementara. Anda dapat memulihkannya kembali atau menghapusnya secara permanen.')

@section('content')
    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-slate-500">Total Data di Sampah</div>
            <div class="mt-1 text-2xl font-bold {{ $totalTrash > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                {{ number_format($totalTrash) }} Data
            </div>
            <div class="text-xs text-slate-500">Kombinasi Berita Acara & Barang</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-sky-700">Berita Acara Terhapus</div>
            <div class="mt-1 text-2xl font-bold text-sky-600">{{ number_format($totalTrashedHandovers) }} Dokumen</div>
            <div class="text-xs text-slate-500">BAST & BAP dapat dipulihkan</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold uppercase text-amber-700">Barang Inventaris Terhapus</div>
            <div class="mt-1 text-2xl font-bold text-amber-600">{{ number_format($totalTrashedItems) }} Barang</div>
            <div class="text-xs text-slate-500">Master barang beserta barcode</div>
        </div>
    </div>

    <!-- Global Actions Bar -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <!-- Tab Selector -->
        <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs font-bold">
            <a href="{{ route('trash.index', ['tab' => 'handovers']) }}"
               class="px-4 py-2 rounded-lg transition-all {{ $activeTab === 'handovers' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                📋 Berita Acara ({{ $totalTrashedHandovers }})
            </a>
            <a href="{{ route('trash.index', ['tab' => 'items']) }}"
               class="px-4 py-2 rounded-lg transition-all {{ $activeTab === 'items' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                📦 Barang Inventaris ({{ $totalTrashedItems }})
            </a>
        </div>

        <!-- Global Buttons: Restore All & Empty Trash -->
        @if ($totalTrash > 0)
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('trash.restore-all') }}" method="POST"
                      onsubmit="return confirm('Pulihkan SEMUA data di Sampah (Berita Acara & Barang)?')">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Pulihkan Semua</span>
                    </button>
                </form>

                <form action="{{ route('trash.empty') }}" method="POST"
                      onsubmit="return confirm('PERINGATAN: Kosongkan seluruh sampah secara PERMANEN? Data yang dihapus permanen tidak dapat dikembalikan lagi!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Kosongkan Sampah</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- TAB CONTENT: BERITA ACARA TERHAPUS -->
    @if ($activeTab === 'handovers')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Daftar Dokumen Berita Acara yang Dihapus</h3>
                    <p class="text-xs text-slate-500">Klik "Pulihkan" untuk mengembalikan dokumen ke daftar aktif BAST & BAP</p>
                </div>
                <span class="text-xs font-bold text-sky-700 bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-200">
                    {{ $totalTrashedHandovers }} Dokumen di Sampah
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-fluid">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Nomor & Tipe Dokumen</th>
                            <th class="py-3.5 px-4">Pihak Pertama (Penyerah)</th>
                            <th class="py-3.5 px-4">Pihak Kedua (Penerima)</th>
                            <th class="py-3.5 px-4">Barang Terlampir</th>
                            <th class="py-3.5 px-4">Waktu Dihapus</th>
                            <th class="py-3.5 px-4 text-right">Aksi Sampah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 text-sm">
                        @forelse ($deletedHandovers as $doc)
                            <tr class="hover:bg-slate-50/90 transition-colors">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono-code font-bold text-slate-900">{{ $doc->document_no }}</div>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $doc->document_type === 'bast' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $doc->document_type === 'bast' ? 'BAST' : 'BAP' }} • {{ $doc->loan_type_label }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-900">{{ $doc->first_party_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $doc->first_party_unit ?: '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-900">{{ $doc->second_party_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $doc->second_party_unit ?: '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4 align-top text-xs">
                                    <div><strong>{{ $doc->items->count() }}</strong> Jenis Barang</div>
                                    <div class="text-slate-500 mt-0.5">Keperluan: {{ $doc->purpose ?: '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono-code text-xs text-rose-600 font-semibold">
                                        {{ $doc->deleted_at?->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $doc->deleted_at?->diffForHumans() }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-top text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <!-- Pulihkan -->
                                        <form action="{{ route('trash.handovers.restore', $doc->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1 shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                <span>Pulihkan</span>
                                            </button>
                                        </form>

                                        <!-- Hapus Permanen -->
                                        <form action="{{ route('trash.handovers.force-delete', $doc->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus permanen Berita Acara {{ $doc->document_no }}? Tindakan ini tidak bisa dibatalkan!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 rounded-lg border border-rose-300 hover:bg-rose-50 text-rose-600 text-xs font-semibold">
                                                Hapus Permanen
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-sm text-slate-400">
                                    Tidak ada dokumen Berita Acara di dalam kotak sampah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($deletedHandovers->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $deletedHandovers->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- TAB CONTENT: BARANG INVENTARIS TERHAPUS -->
    @if ($activeTab === 'items')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Daftar Barang Inventaris yang Dihapus</h3>
                    <p class="text-xs text-slate-500">Klik "Pulihkan" untuk mengembalikan barang ke Master Data Barang & Terminal Scan</p>
                </div>
                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                    {{ $totalTrashedItems }} Barang di Sampah
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-fluid">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Barang & SKU</th>
                            <th class="py-3.5 px-4">Barcode</th>
                            <th class="py-3.5 px-4">Kategori & Lokasi</th>
                            <th class="py-3.5 px-4">Stok Terakhir</th>
                            <th class="py-3.5 px-4">Waktu Dihapus</th>
                            <th class="py-3.5 px-4 text-right">Aksi Sampah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 text-sm">
                        @forelse ($deletedItems as $item)
                            <tr class="hover:bg-slate-50/90 transition-colors">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex items-start gap-3">
                                        @if ($item->photo_url)
                                            <div class="w-11 h-11 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0">
                                                <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-11 h-11 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 text-[9px] font-bold">
                                                NO IMG
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-mono-code text-xs font-semibold text-emerald-700">{{ $item->item_code }}</div>
                                            <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $item->brand ?: 'Tanpa Merek' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <span class="font-mono-code text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-200 font-bold text-slate-800">
                                        {{ $item->barcode }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 align-top text-xs">
                                    <div class="font-semibold text-slate-800">{{ $item->category?->name ?? 'Tanpa Kategori' }}</div>
                                    <div class="text-slate-500 mt-0.5">Lokasi: {{ $item->storage_location ?: 'Gudang Utama' }}</div>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <span class="font-bold text-slate-900 text-sm">{{ $item->stock }}</span>
                                    <span class="text-xs text-slate-500">{{ $item->unit }}</span>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono-code text-xs text-rose-600 font-semibold">
                                        {{ $item->deleted_at?->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $item->deleted_at?->diffForHumans() }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-top text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <!-- Pulihkan -->
                                        <form action="{{ route('trash.items.restore', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1 shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                <span>Pulihkan</span>
                                            </button>
                                        </form>

                                        <!-- Hapus Permanen -->
                                        <form action="{{ route('trash.items.force-delete', $item->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus permanen barang \"{{ $item->name }}\"? Tindakan ini tidak bisa dibatalkan!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 rounded-lg border border-rose-300 hover:bg-rose-50 text-rose-600 text-xs font-semibold">
                                                Hapus Permanen
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-sm text-slate-400">
                                    Tidak ada barang inventaris di dalam kotak sampah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($deletedItems->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $deletedItems->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
