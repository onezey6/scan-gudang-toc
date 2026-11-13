@extends('layouts.app')

@section('title', 'Detail Berita Acara ' . $handover->document_no)
@section('page_title', 'Detail Berita Acara: ' . $handover->document_no)
@section('page_subtitle', $handover->document_type === 'bast' ? 'Dokumen Berita Acara Serah Terima & Pinjam Pakai Barang Logistik' : 'Dokumen Berita Acara Pengembalian Barang Logistik')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('handovers.index') }}" class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Daftar Berita Acara
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if ($handover->document_type === 'bast' && $handover->status === 'active')
                <a href="{{ route('handovers.create', ['type' => 'bap', 'bast_id' => $handover->id]) }}"
                   class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold">
                    + Buat Berita Acara Pengembalian (BAP) atas BAST Ini
                </a>
            @endif
            <a href="{{ route('handovers.print', $handover) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Surat Resmi / PDF</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $handover->document_type === 'bast' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        {{ $handover->document_type === 'bast' ? 'BERITA ACARA SERAH TERIMA / PINJAM PAKAI (BAST)' : 'BERITA ACARA PENGEMBALIAN (BAP)' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $handover->loan_type_label }}
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-2 font-mono-code">{{ $handover->document_no }}</h2>
                <div class="text-xs text-slate-500 mt-1">
                    Tanggal Dokumen: <strong>{{ $handover->document_date?->format('d F Y') }}</strong> • Dibuat oleh Petugas: <strong>{{ $handover->user?->name ?? 'Admin' }}</strong>
                </div>
            </div>
            <div class="keep-light bg-white p-2 rounded-xl border border-slate-200">
                <svg class="js-barcode" data-barcode="{{ $handover->document_no }}" data-height="32" data-width="1.2" data-fontsize="10"></svg>
            </div>
        </div>

        <!-- Ringkasan Keperluan, Dasar Surat, Lokasi & Batas Pengembalian -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Keperluan / Kegiatan Operasi</div>
                <div class="font-bold text-slate-900 mt-0.5">{{ $handover->purpose ?: 'Keperluan Operasional Dinas Logistik' }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Dasar Surat Perintah / No. Sprin</div>
                <div class="font-mono-code font-bold text-slate-900 mt-0.5">{{ $handover->reference_letter_no ?: '-' }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Lokasi Penggunaan / Wilayah Tugas</div>
                <div class="font-semibold text-slate-800 mt-0.5">{{ $handover->operation_location ?: '-' }}</div>
            </div>
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Batas Waktu Pengembalian</div>
                <div class="font-bold text-amber-700 mt-0.5">{{ $handover->expected_return_date ? $handover->expected_return_date->format('d/m/Y') : '-' }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div class="text-[11px] font-bold uppercase text-slate-400">PIHAK PERTAMA ({{ $handover->document_type === 'bast' ? 'Yang Menyerahkan' : 'Yang Mengembalikan' }})</div>
                <div class="text-base font-bold text-slate-900 mt-1">{{ $handover->first_party_name }}</div>
                <div class="text-xs text-slate-600 mt-0.5">NRP/NIP/ID: {{ $handover->first_party_nip ?: '-' }}</div>
                <div class="text-xs text-slate-600">Jabatan: {{ $handover->first_party_title ?: '-' }}</div>
                <div class="text-xs text-slate-600">Satuan/Divisi: {{ $handover->first_party_unit ?: '-' }}</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div class="text-[11px] font-bold uppercase text-slate-400">PIHAK KEDUA ({{ $handover->document_type === 'bast' ? 'Yang Menerima / Meminjam' : 'Yang Menerima Kembali' }})</div>
                <div class="text-base font-bold text-slate-900 mt-1">{{ $handover->second_party_name }}</div>
                <div class="text-xs text-slate-600 mt-0.5">NRP/NIP/ID: {{ $handover->second_party_nip ?: '-' }}</div>
                <div class="text-xs text-slate-600">Jabatan: {{ $handover->second_party_title ?: '-' }}</div>
                <div class="text-xs text-slate-600">Satuan/Divisi: {{ $handover->second_party_unit ?: '-' }} ({{ $handover->second_party_phone ?: '-' }})</div>
            </div>
        </div>

        <!-- Tabel Barang -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-3">Daftar Rincian Barang, Nomor Seri, Kelengkapan & Status</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left border-collapse table-fluid">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase text-slate-500">
                            <th class="py-3 px-3.5">No</th>
                            <th class="py-3 px-3.5">Kode & Nama Barang</th>
                            <th class="py-3 px-3.5">Barcode</th>
                            <th class="py-3 px-3.5">Jumlah</th>
                            <th class="py-3 px-3.5">No. Seri & Kelengkapan</th>
                            <th class="py-3 px-3.5">Kondisi & Status</th>
                            <th class="py-3 px-3.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-sm">
                        @foreach ($handover->items as $idx => $hi)
                            <tr>
                                <td class="py-3 px-3.5">{{ $idx + 1 }}</td>
                                <td class="py-3 px-3.5">
                                    <div class="flex items-center gap-2.5">
                                        @if ($hi->item?->photo_url)
                                            <button type="button" onclick="openPhotoLightbox('{{ $hi->item->photo_url }}', '{{ addslashes($hi->item->name) }}')"
                                                    class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shrink-0">
                                                <img src="{{ $hi->item->photo_url }}" alt="{{ $hi->item->name }}" class="w-full h-full object-cover">
                                            </button>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $hi->item?->name }}</div>
                                            <div class="text-xs font-mono-code text-slate-500">{{ $hi->item?->item_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="keep-light inline-block bg-white px-1.5 py-0.5 rounded-md border border-slate-200">
                                        <svg class="js-barcode max-w-[105px] h-auto block" data-barcode="{{ $hi->item?->barcode }}" data-height="18" data-width="0.9" data-fontsize="8" data-margin="1"></svg>
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 font-bold">{{ $hi->quantity }} {{ $hi->item?->unit }}</td>
                                <td class="py-3 px-3.5 text-xs">
                                    <div>S/N: <strong class="font-mono-code">{{ $hi->serial_number ?: '-' }}</strong></div>
                                    <div class="text-slate-500 mt-0.5">Kelengkapan: {{ $hi->accessories ?: 'Standar' }}</div>
                                </td>
                                <td class="py-3 px-3.5 text-xs">
                                    <div class="font-semibold text-slate-900">{{ $hi->condition_status }}</div>
                                    @php $itemNowStatus = $hi->item?->availability_status ?? $hi->availability_status ?? 'sedia'; @endphp
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $itemNowStatus === 'sedia' ? 'bg-emerald-100 text-emerald-800' : ($itemNowStatus === 'sedang_keluar' ? 'bg-amber-100 text-amber-800' : ($itemNowStatus === 'sedang_dipakai' ? 'bg-cyan-100 text-cyan-800' : 'bg-indigo-100 text-indigo-800')) }}">
                                        {{ $hi->item?->availability_label ?? $hi->availability_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-xs text-slate-600">{{ $hi->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ketentuan & Kewajiban Pinjam Pakai / Pengembalian -->
        @if ($handover->terms_conditions)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
                <strong class="block text-slate-900 uppercase tracking-wider mb-1.5">{{ $handover->document_type === 'bap' ? 'Ketentuan & Syarat Pengembalian Barang (BAP):' : 'Ketentuan & Kewajiban Pinjam Pakai Barang (BAST):' }}</strong>
                <div class="whitespace-pre-line leading-relaxed font-mono-code">{{ $handover->terms_conditions }}</div>
            </div>
        @endif

        @if ($handover->notes)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
                <strong class="block text-slate-900 mb-1">Catatan Berita Acara:</strong>
                {{ $handover->notes }}
            </div>
        @endif

        <!-- Galeri & Upload Tambahan Foto Bukti Dokumentasi (Unlimited) -->
        <div class="pt-4 border-t border-slate-200 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Galeri Foto Bukti Serah Terima / Pengembalian ({{ $handover->photos->count() }} Foto)
                    </h3>
                    <p class="text-xs text-slate-500">Klik foto untuk memperbesar tampilan penuh, atau tambahkan foto bukti baru di bawah ini tanpa batas</p>
                </div>
            </div>

            @if ($handover->photos->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5">
                    @foreach ($handover->photos as $photo)
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between gap-2">
                            <div>
                                <button type="button"
                                        onclick="openPhotoLightbox('{{ $photo->photo_url }}', '{{ addslashes($photo->caption ?: 'Foto Bukti ' . $handover->document_no) }}')"
                                        class="w-full h-36 rounded-lg overflow-hidden border border-slate-200 bg-white block group">
                                    <img src="{{ $photo->photo_url }}" alt="Foto Bukti" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                </button>
                                <div class="text-xs font-semibold text-slate-800 mt-2">
                                    {{ $photo->caption ?: 'Dokumentasi Bukti Fisik' }}
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1.5 border-t border-slate-200/80">
                                <span>{{ $photo->created_at?->format('d/m/Y H:i') }}</span>
                                <form action="{{ route('handovers.photos.destroy', $photo) }}" method="POST"
                                      onsubmit="return confirm('Hapus foto bukti ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline font-bold">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 rounded-xl border border-dashed border-slate-300 text-center text-xs text-slate-500">
                    Belum ada foto bukti dokumentasi yang dilampirkan pada Berita Acara ini.
                </div>
            @endif

            <!-- Form Upload Tambahan Foto Bukti Tanpa Batas -->
            <form action="{{ route('handovers.photos.store', $handover) }}" method="POST" enctype="multipart/form-data"
                  class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                @csrf
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-slate-700">+ Tambah Foto Bukti Baru ke Dokumen Ini (Tidak Terbatas)</span>
                    <button type="button" onclick="addDetailPhotoSlot()" class="px-2.5 py-1 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 text-[11px] font-bold">
                        + Tambah Baris File
                    </button>
                </div>
                <div id="detail-photo-slots" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-2.5 rounded-lg border border-slate-200 bg-white space-y-2">
                        <input type="file" name="evidence_photos[]" accept="image/jpeg,image/png,image/webp" multiple required
                               class="block w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white cursor-pointer">
                        <input type="text" name="evidence_captions[]" placeholder="Keterangan foto bukti..."
                               class="w-full px-2.5 py-1.5 rounded-md border border-slate-300 text-xs">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                        Upload & Simpan Foto Bukti
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function addDetailPhotoSlot() {
        var container = document.getElementById('detail-photo-slots');
        var div = document.createElement('div');
        div.className = 'p-2.5 rounded-lg border border-slate-200 bg-white space-y-2';
        div.innerHTML =
            '<input type="file" name="evidence_photos[]" accept="image/jpeg,image/png,image/webp" multiple required class="block w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white cursor-pointer">' +
            '<div class="flex gap-1.5">' +
                '<input type="text" name="evidence_captions[]" placeholder="Keterangan foto bukti..." class="flex-1 px-2.5 py-1.5 rounded-md border border-slate-300 text-xs">' +
                '<button type="button" onclick="this.closest(\'.p-2\\\\.5\').remove()" class="px-2 text-rose-600 font-bold text-xs">&times;</button>' +
            '</div>';
        container.appendChild(div);
    }
</script>
@endpush
