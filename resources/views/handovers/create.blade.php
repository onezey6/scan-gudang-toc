@extends('layouts.app')

@section('title', $docType === 'bast' ? 'Buat Berita Acara Serah Terima & Pinjam Pakai (BAST)' : 'Buat Berita Acara Pengembalian (BAP)')
@section('page_title', $docType === 'bast' ? 'Buat Berita Acara Serah Terima & Pinjam Pakai (BAST)' : 'Buat Berita Acara Pengembalian Barang (BAP)')
@section('page_subtitle', 'Format resmi pinjam pakai dilengkapi rincian keperluan, ketentuan kedinasan, sinkronisasi status barang, dan upload foto bukti tanpa batas')

@section('content')
    <!-- Switcher BAST vs BAP -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-xl bg-white p-1 border border-slate-200 shadow-2xs">
            <a href="{{ route('handovers.create', ['type' => 'bast']) }}"
               class="px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-all {{ $docType === 'bast' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                1. Berita Acara Serah Terima / Pinjam Pakai (BAST)
            </a>
            <a href="{{ route('handovers.create', ['type' => 'bap']) }}"
               class="px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-all {{ $docType === 'bap' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                2. Berita Acara Pengembalian (BAP)
            </a>
        </div>

        <a href="{{ route('handovers.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Daftar Berita Acara
        </a>
    </div>

    @if ($docType === 'bap' && $activeBasts->count() > 0)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs text-emerald-900">
                <strong class="block text-sm">Tarik Data Otomatis dari BAST Sebelumnya (Opsional):</strong>
                Pilih dokumen BAST yang barangnya akan dikembalikan agar identitas pihak, keperluan, dan daftar barang terisi otomatis.
            </div>
            <form action="{{ route('handovers.create') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="type" value="bap">
                <select name="bast_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-emerald-400 bg-white text-xs font-semibold text-slate-800">
                    <option value="">-- Pilih Dokumen BAST Aktif --</option>
                    @foreach ($activeBasts as $ab)
                        <option value="{{ $ab->id }}" {{ $selectedBast && $selectedBast->id === $ab->id ? 'selected' : '' }}>
                            {{ $ab->document_no }} — {{ $ab->second_party_name }} ({{ $ab->document_date?->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <form action="{{ route('handovers.store') }}" method="POST" enctype="multipart/form-data" onsubmit="captureSignaturesBeforeSubmit()" class="space-y-6">
        @csrf
        <input type="hidden" name="document_type" value="{{ $docType }}">
        @if ($selectedBast)
            <input type="hidden" name="related_bast_id" value="{{ $selectedBast->id }}">
        @endif

        <!-- Bagian 1: Header Dokumen, Jenis Pinjam Pakai, Keperluan & Sinkronisasi Status Barang -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <div class="pb-2 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">1. Rincian Administrasi, Keperluan & Status Pinjam Pakai</h3>
                    <p class="text-xs text-slate-500">Tentukan jenis penggunaan, dasar surat perintah, batas pengembalian, dan sinkronisasi status barang</p>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
                    <input type="checkbox" name="sync_stock" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <span>
                        {{ $docType === 'bast' ? 'Sinkronkan Stok & Status Barang Otomatis' : 'Kembalikan Stok & Status Barang ke Sedia' }}
                    </span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Nomor Dokumen (Otomatis)</label>
                    <input type="text" value="{{ $nextDocNo }}" readonly
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-100 font-mono-code text-sm font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Tanggal Berita Acara *</label>
                    <input type="date" name="document_date" value="{{ old('document_date', now()->toDateString()) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Jenis Keperluan Dokumen *</label>
                    <select name="loan_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        <option value="pinjam_pakai" {{ old('loan_type', $selectedBast?->loan_type) === 'pinjam_pakai' ? 'selected' : '' }}>Pinjam Pakai Operasional</option>
                        <option value="operasional_khusus" {{ old('loan_type', $selectedBast?->loan_type) === 'operasional_khusus' ? 'selected' : '' }}>Penugasan / Operasi Khusus</option>
                        <option value="inventaris_unit" {{ old('loan_type', $selectedBast?->loan_type) === 'inventaris_unit' ? 'selected' : '' }}>Penyerahan Inventaris Unit</option>
                        <option value="perawatan" {{ old('loan_type', $selectedBast?->loan_type) === 'perawatan' ? 'selected' : '' }}>Perawatan / Kalibrasi / Perbaikan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Ubah Status Barang Menjadi *</label>
                    @if ($docType === 'bast')
                        <select id="global_item_status_target" name="item_status_target" onchange="syncRowStatusFromGlobal(this.value)"
                                class="w-full px-3 py-2 rounded-xl border border-indigo-400 text-sm font-semibold bg-white">
                            <option value="sedang_dipinjam">Sedang Dipinjam</option>
                            <option value="sedang_dipakai">Sedang Dipakai</option>
                            <option value="sedang_keluar">Sedang Keluar</option>
                            <option value="sedia">Tetap Sedia</option>
                        </select>
                    @else
                        <select name="item_status_target" class="w-full px-3 py-2 rounded-xl border border-emerald-400 text-sm font-semibold bg-white">
                            <option value="sedia" selected>Sedia (Kembali ke Gudang)</option>
                        </select>
                    @endif
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Keperluan / Nama Kegiatan / Operasi *</label>
                    <input type="text" name="purpose" value="{{ old('purpose', $selectedBast?->purpose ?? ($docType === 'bast' ? 'Dukungan Peralatan Komunikasi & Operasional Taktis TOC' : 'Pengembalian Peralatan Pasca Giat Operasional TOC')) }}"
                           placeholder="Contoh: Giat Pengamanan & Patroli Taktis Korps Brimob Polri..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Dasar Surat Perintah / No. Sprin</label>
                    <input type="text" name="reference_letter_no" value="{{ old('reference_letter_no', $selectedBast?->reference_letter_no) }}"
                           placeholder="Contoh: Sprin/104/XI/LOG.TOC/2026"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono-code">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">
                        {{ $docType === 'bast' ? 'Batas Waktu Pengembalian' : 'Tanggal Realisasi Kembali' }}
                    </label>
                    <input type="date" name="expected_return_date"
                           value="{{ old('expected_return_date', $selectedBast?->expected_return_date?->toDateString() ?? now()->addDays(7)->toDateString()) }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Lokasi Penggunaan / Wilayah Penugasan</label>
                    <input type="text" name="operation_location" value="{{ old('operation_location', $selectedBast?->operation_location ?? 'Mako Tactical Operation Centre (TOC) — Korps Brimob Polri') }}"
                           placeholder="Contoh: Mako Korps Brimob Polri / Posko Taktis Gegana..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
            </div>
        </div>

        <!-- Bagian 2: Identitas Pihak Pertama & Pihak Kedua -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pihak Pertama -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3.5">
                <div class="pb-2 border-b border-slate-200">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-900 text-white">PIHAK PERTAMA</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">
                        {{ $docType === 'bast' ? 'Yang Menyerahkan / Meminjamkan (Petugas Logistik)' : 'Yang Mengembalikan Barang (Peminjam / Pemakai)' }}
                    </h3>
                </div>

                @php
                    $p1Name = $docType === 'bast'
                        ? auth()->user()->name
                        : ($selectedBast?->second_party_name ?? '');
                    $p1Nip = $docType === 'bast'
                        ? (auth()->user()->employee_id ?? 'NRP-TOC-001')
                        : ($selectedBast?->second_party_nip ?? '');
                    $p1Title = $docType === 'bast'
                        ? (auth()->user()->job_title ?? 'Petugas Gudang Logistik')
                        : ($selectedBast?->second_party_title ?? '');
                    $p1Unit = $docType === 'bast'
                        ? (auth()->user()->department ?? 'Divisi Logistik TOC')
                        : ($selectedBast?->second_party_unit ?? '');
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap Pihak Pertama *</label>
                        <input type="text" name="first_party_name" value="{{ old('first_party_name', $p1Name) }}" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NRP / NIP / ID Personel</label>
                        <input type="text" name="first_party_nip" value="{{ old('first_party_nip', $p1Nip) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pangkat / Jabatan</label>
                        <input type="text" name="first_party_title" value="{{ old('first_party_title', $p1Title) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan / Subden / Divisi</label>
                        <input type="text" name="first_party_unit" value="{{ old('first_party_unit', $p1Unit) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </div>

            <!-- Pihak Kedua -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3.5">
                <div class="pb-2 border-b border-slate-200">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-600 text-white">PIHAK KEDUA</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">
                        {{ $docType === 'bast' ? 'Yang Menerima / Meminjam Barang' : 'Yang Menerima Kembali (Petugas Gudang Logistik)' }}
                    </h3>
                </div>

                @php
                    $p2Name = $docType === 'bap' ? auth()->user()->name : '';
                    $p2Nip = $docType === 'bap' ? (auth()->user()->employee_id ?? 'NRP-TOC-001') : '';
                    $p2Title = $docType === 'bap' ? (auth()->user()->job_title ?? 'Petugas Gudang Logistik') : '';
                    $p2Unit = $docType === 'bap' ? (auth()->user()->department ?? 'Divisi Logistik TOC') : '';
                    $p2Phone = $docType === 'bap' ? (auth()->user()->phone ?? '') : '';
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap Pihak Kedua *</label>
                        <input type="text" name="second_party_name" value="{{ old('second_party_name', $p2Name) }}" required
                               placeholder="Nama lengkap personel penerima / peminjam..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NRP / NIP / ID Personel</label>
                        <input type="text" name="second_party_nip" value="{{ old('second_party_nip', $p2Nip) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pangkat / Jabatan</label>
                        <input type="text" name="second_party_title" value="{{ old('second_party_title', $p2Title) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan / Subden / Divisi</label>
                        <input type="text" name="second_party_unit" value="{{ old('second_party_unit', $p2Unit) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">No. Telepon / HT / WhatsApp</label>
                        <input type="text" name="second_party_phone" value="{{ old('second_party_phone', $p2Phone) }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Daftar Rincian Barang yang Diserahterimakan / Dikembalikan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">2. Rincian Barang, Nomor Seri, Kelengkapan & Status</h3>
                    <p class="text-xs text-slate-500">Ketik/scan barcode di kolom cepat atau klik "+ Tambah Baris Barang"</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="text" id="quick_barcode_handover"
                           placeholder="Scan barcode untuk tambah baris..."
                           class="px-3 py-1.5 rounded-xl border border-slate-300 font-mono-code text-xs">
                    <button type="button" onclick="addHandoverItemRow()"
                            class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white text-xs font-semibold">
                        + Tambah Baris Barang
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-fluid">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase text-slate-500">
                            <th class="py-2.5 px-2.5">Pilih Barang (Barcode & Stok)</th>
                            <th class="py-2.5 px-2.5 w-20">Qty</th>
                            <th class="py-2.5 px-2.5 w-36">No. Seri (S/N)</th>
                            <th class="py-2.5 px-2.5 w-44">Kelengkapan / Aksesoris</th>
                            <th class="py-2.5 px-2.5 w-36">Kondisi</th>
                            <th class="py-2.5 px-2.5 w-36">Status Barang</th>
                            <th class="py-2.5 px-2.5">Catatan</th>
                            <th class="py-2.5 px-2 w-12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="handover-items-tbody" class="divide-y divide-slate-200">
                        @if ($selectedBast && $selectedBast->items->count() > 0)
                            @foreach ($selectedBast->items as $idx => $bItem)
                                <tr class="handover-item-row">
                                    <td class="py-2.5 px-2.5">
                                        <select name="items[{{ $idx }}][item_id]" required class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                            @foreach ($items as $it)
                                                <option value="{{ $it->id }}" data-barcode="{{ $it->barcode }}" {{ $it->id === $bItem->item_id ? 'selected' : '' }}>
                                                    [{{ $it->barcode }}] {{ $it->name }} ({{ $it->stock }} {{ $it->unit }} • {{ $it->availability_label }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-2.5">
                                        <input type="number" name="items[{{ $idx }}][quantity]" value="{{ $bItem->quantity }}" min="1" required
                                               class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                                    </td>
                                    <td class="py-2.5 px-2.5">
                                        <input type="text" name="items[{{ $idx }}][serial_number]" value="{{ $bItem->serial_number }}"
                                               placeholder="S/N / No. Reg..."
                                               class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-mono-code">
                                    </td>
                                    <td class="py-2.5 px-2.5">
                                        <input type="text" name="items[{{ $idx }}][accessories]" value="{{ $bItem->accessories }}"
                                               placeholder="Baterai, charger, antena..."
                                               class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs">
                                    </td>
                                    <td class="py-2.5 px-2.5">
                                        <select name="items[{{ $idx }}][condition_status]" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                            <option value="Baik">Baik / Lengkap</option>
                                            <option value="Rusak Ringan">Rusak Ringan</option>
                                            <option value="Rusak Berat">Rusak Berat</option>
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-2.5">
                                        <select name="items[{{ $idx }}][availability_status]" class="row-availability-select w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                            <option value="sedia" {{ $docType === 'bap' ? 'selected' : '' }}>Sedia</option>
                                            <option value="sedang_dipinjam" {{ $docType === 'bast' ? 'selected' : '' }}>Sedang Dipinjam</option>
                                            <option value="sedang_dipakai">Sedang Dipakai</option>
                                            <option value="sedang_keluar">Sedang Keluar</option>
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-2.5">
                                        <input type="text" name="items[{{ $idx }}][notes]" value="{{ $bItem->notes }}"
                                               placeholder="Catatan..."
                                               class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs">
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <button type="button" onclick="removeHandoverRow(this)" class="text-rose-600 hover:text-rose-800 font-bold text-sm">&times;</button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="handover-item-row">
                                <td class="py-2.5 px-2.5">
                                    <select name="items[0][item_id]" required class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                        @foreach ($items as $it)
                                            <option value="{{ $it->id }}" data-barcode="{{ $it->barcode }}">
                                                [{{ $it->barcode }}] {{ $it->name }} ({{ $it->stock }} {{ $it->unit }} • {{ $it->availability_label }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2.5 px-2.5">
                                    <input type="number" name="items[0][quantity]" value="1" min="1" required
                                           class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                                </td>
                                <td class="py-2.5 px-2.5">
                                    <input type="text" name="items[0][serial_number]" placeholder="S/N / No. Reg..."
                                           class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-mono-code">
                                </td>
                                <td class="py-2.5 px-2.5">
                                    <input type="text" name="items[0][accessories]" placeholder="Baterai, charger, antena..."
                                           class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs">
                                </td>
                                <td class="py-2.5 px-2.5">
                                    <select name="items[0][condition_status]" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                        <option value="Baik">Baik / Lengkap</option>
                                        <option value="Rusak Ringan">Rusak Ringan</option>
                                        <option value="Rusak Berat">Rusak Berat</option>
                                    </select>
                                </td>
                                <td class="py-2.5 px-2.5">
                                    <select name="items[0][availability_status]" class="row-availability-select w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                                        <option value="sedia" {{ $docType === 'bap' ? 'selected' : '' }}>Sedia</option>
                                        <option value="sedang_dipinjam" {{ $docType === 'bast' ? 'selected' : '' }}>Sedang Dipinjam</option>
                                        <option value="sedang_dipakai">Sedang Dipakai</option>
                                        <option value="sedang_keluar">Sedang Keluar</option>
                                    </select>
                                </td>
                                <td class="py-2.5 px-2.5">
                                    <input type="text" name="items[0][notes]" placeholder="Catatan..."
                                           class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs">
                                </td>
                                <td class="py-2.5 px-2 text-center">
                                    <button type="button" onclick="removeHandoverRow(this)" class="text-rose-600 hover:text-rose-800 font-bold text-sm">&times;</button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bagian 4: Ketentuan & Kewajiban Pinjam Pakai Rinci + Pejabat Mengetahui -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3.5">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">3. Ketentuan, Kewajiban & Pasal Pinjam Pakai</h3>
                    <p class="text-xs text-slate-500">Klausul standar kedinasan ini akan tercetak otomatis pada surat resmi BAST/BAP</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Ketentuan & Syarat Pinjam Pakai (Dapat Disesuaikan)</label>
                    <textarea name="terms_conditions" rows="6"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs leading-relaxed">{{ old('terms_conditions', $defaultTerms) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Catatan Tambahan / Pemeriksaan Fisik</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs">{{ old('notes', $docType === 'bast' ? 'Seluruh barang beserta kelengkapannya telah diuji fungsi dan diserahkan dalam keadaan baik/lengkap.' : 'Barang telah dikembalikan ke gudang logistik dan diperiksa kelengkapannya dalam kondisi baik.') }}</textarea>
                </div>
            </div>

            <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3.5">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">4. Pejabat yang Mengetahui / Menyetujui (Opsional)</h3>
                    <p class="text-xs text-slate-500">Tampil pada kolom pengesahan di bagian bawah surat cetak BAST / BAP</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pejabat / Atasan Langsung</label>
                        <input type="text" name="approver_name" value="{{ old('approver_name', $selectedBast?->approver_name) }}"
                               placeholder="Contoh: KOMPOL / AKP Nama Pejabat, S.I.K."
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NRP / NIP Pejabat</label>
                        <input type="text" name="approver_nip" value="{{ old('approver_nip', $selectedBast?->approver_nip) }}"
                               placeholder="Contoh: NRP 85010123"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jabatan Pengesah</label>
                        <input type="text" name="approver_title" value="{{ old('approver_title', $selectedBast?->approver_title ?? 'Kepala Seksi Logistik TOC') }}"
                               placeholder="Contoh: Kasi Logistik / Komandan Tim TOC"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 5: Upload Foto Bukti Serah Terima / Pengembalian (Tidak Terbatas / Unlimited) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">5. Upload Foto Bukti Dokumentasi (Tidak Terbatas / Unlimited Photos)</h3>
                    <p class="text-xs text-slate-500">Lampirkan foto fisik barang, foto penyerahan, atau kondisi kelengkapan sebanyak yang diperlukan</p>
                </div>
                <button type="button" onclick="addEvidencePhotoSlot()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shrink-0">
                    <span>+ Tambah Slot Foto Bukti Lagi</span>
                </button>
            </div>

            <div id="evidence-photos-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="evidence-photo-slot p-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex flex-col gap-2">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                        <span>Foto Bukti #1</span>
                    </div>
                    <input type="file" name="evidence_photos[]" accept="image/jpeg,image/png,image/webp" multiple
                           onchange="previewEvidenceFiles(this)"
                           class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-emerald-600 cursor-pointer">
                    <input type="text" name="evidence_captions[]" placeholder="Keterangan foto (misal: Kondisi fisik HT & antena)..."
                           class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">
                    <div class="evidence-preview-thumbs flex flex-wrap gap-1.5"></div>
                </div>
            </div>
        </div>

        <!-- Bagian 6: Kanvas Tanda Tangan Digital Kedua Pihak (Signature Pad) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold uppercase text-slate-700">Tanda Tangan Pihak Pertama (Opsional di Layar)</label>
                    <button type="button" onclick="clearPad('canvas_sig_1')" class="text-xs text-rose-600 hover:underline font-medium">Bersihkan</button>
                </div>
                <canvas id="canvas_sig_1" width="420" height="150" class="keep-light w-full h-36 rounded-xl border-2 border-dashed border-slate-300 bg-white cursor-crosshair touch-none"></canvas>
                <input type="hidden" id="first_party_signature" name="first_party_signature">
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold uppercase text-slate-700">Tanda Tangan Pihak Kedua (Opsional di Layar)</label>
                    <button type="button" onclick="clearPad('canvas_sig_2')" class="text-xs text-rose-600 hover:underline font-medium">Bersihkan</button>
                </div>
                <canvas id="canvas_sig_2" width="420" height="150" class="keep-light w-full h-36 rounded-xl border-2 border-dashed border-slate-300 bg-white cursor-crosshair touch-none"></canvas>
                <input type="hidden" id="second_party_signature" name="second_party_signature">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('handovers.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-100">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-sm">
                Terbitkan & Simpan {{ $docType === 'bast' ? 'Berita Acara Serah Terima (BAST)' : 'Berita Acara Pengembalian (BAP)' }}
            </button>
        </div>
    </form>
@endsection

@php
    $itemsCatalogData = $items->map(function ($i) {
        return [
            'id' => $i->id,
            'barcode' => $i->barcode,
            'name' => $i->name,
            'stock' => $i->stock,
            'unit' => $i->unit,
            'availability_label' => $i->availability_label,
        ];
    })->values();
@endphp
@push('scripts')
<script>
    var rowCounter = 20;
    var photoSlotCounter = 1;
    var itemsCatalog = @json($itemsCatalogData);

    function syncRowStatusFromGlobal(val) {
        document.querySelectorAll('.row-availability-select').forEach(function (sel) {
            sel.value = val;
        });
    }

    function addHandoverItemRow(selectedItemId) {
        rowCounter++;
        var tbody = document.getElementById('handover-items-tbody');
        var tr = document.createElement('tr');
        tr.className = 'handover-item-row';

        var globalStatusEl = document.getElementById('global_item_status_target');
        var defStatus = globalStatusEl ? globalStatusEl.value : '{{ $docType === 'bap' ? 'sedia' : 'sedang_dipinjam' }}';

        var optionsHtml = '';
        itemsCatalog.forEach(function (it) {
            var sel = (selectedItemId && parseInt(selectedItemId, 10) === parseInt(it.id, 10)) ? 'selected' : '';
            optionsHtml += '<option value="' + it.id + '" ' + sel + '>[' + it.barcode + '] ' + it.name + ' (' + it.stock + ' ' + it.unit + ' • ' + it.availability_label + ')</option>';
        });

        tr.innerHTML =
            '<td class="py-2.5 px-2.5"><select name="items[' + rowCounter + '][item_id]" required class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">' + optionsHtml + '</select></td>' +
            '<td class="py-2.5 px-2.5"><input type="number" name="items[' + rowCounter + '][quantity]" value="1" min="1" required class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-bold"></td>' +
            '<td class="py-2.5 px-2.5"><input type="text" name="items[' + rowCounter + '][serial_number]" placeholder="S/N / No. Reg..." class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs font-mono-code"></td>' +
            '<td class="py-2.5 px-2.5"><input type="text" name="items[' + rowCounter + '][accessories]" placeholder="Baterai, charger, antena..." class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs"></td>' +
            '<td class="py-2.5 px-2.5"><select name="items[' + rowCounter + '][condition_status]" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white"><option value="Baik">Baik / Lengkap</option><option value="Rusak Ringan">Rusak Ringan</option><option value="Rusak Berat">Rusak Berat</option></select></td>' +
            '<td class="py-2.5 px-2.5"><select name="items[' + rowCounter + '][availability_status]" class="row-availability-select w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs bg-white">' +
                '<option value="sedia" ' + (defStatus === 'sedia' ? 'selected' : '') + '>Sedia</option>' +
                '<option value="sedang_dipinjam" ' + (defStatus === 'sedang_dipinjam' ? 'selected' : '') + '>Sedang Dipinjam</option>' +
                '<option value="sedang_dipakai" ' + (defStatus === 'sedang_dipakai' ? 'selected' : '') + '>Sedang Dipakai</option>' +
                '<option value="sedang_keluar" ' + (defStatus === 'sedang_keluar' ? 'selected' : '') + '>Sedang Keluar</option>' +
            '</select></td>' +
            '<td class="py-2.5 px-2.5"><input type="text" name="items[' + rowCounter + '][notes]" placeholder="Catatan..." class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-xs"></td>' +
            '<td class="py-2.5 px-2 text-center"><button type="button" onclick="removeHandoverRow(this)" class="text-rose-600 hover:text-rose-800 font-bold text-sm">&times;</button></td>';

        tbody.appendChild(tr);
    }

    function removeHandoverRow(btn) {
        var rows = document.querySelectorAll('.handover-item-row');
        if (rows.length <= 1) {
            alert('Minimal harus terdapat 1 baris barang dalam Berita Acara.');
            return;
        }
        btn.closest('tr').remove();
    }

    function addEvidencePhotoSlot() {
        photoSlotCounter++;
        var container = document.getElementById('evidence-photos-container');
        var div = document.createElement('div');
        div.className = 'evidence-photo-slot p-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex flex-col gap-2';
        div.innerHTML =
            '<div class="flex items-center justify-between text-[11px] font-bold text-slate-600">' +
                '<span>Foto Bukti #' + photoSlotCounter + '</span>' +
                '<button type="button" onclick="this.closest(\'.evidence-photo-slot\').remove()" class="text-rose-600 hover:underline">Hapus Slot</button>' +
            '</div>' +
            '<input type="file" name="evidence_photos[]" accept="image/jpeg,image/png,image/webp" multiple onchange="previewEvidenceFiles(this)" class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-emerald-600 cursor-pointer">' +
            '<input type="text" name="evidence_captions[]" placeholder="Keterangan foto bukti..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white">' +
            '<div class="evidence-preview-thumbs flex flex-wrap gap-1.5"></div>';
        container.appendChild(div);
    }

    function previewEvidenceFiles(input) {
        var slot = input.closest('.evidence-photo-slot');
        if (!slot) return;
        var thumbs = slot.querySelector('.evidence-preview-thumbs');
        if (!thumbs) return;
        thumbs.innerHTML = '';
        if (!input.files) return;
        Array.from(input.files).forEach(function (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-14 h-14 rounded-lg object-cover border border-slate-200';
                thumbs.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // Simple Signature Pad Implementation
    var padStates = {};
    function initSignaturePad(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.2;
        ctx.lineCap = 'round';
        padStates[canvasId] = { drawing: false, hasInk: false };

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        canvas.addEventListener('mousedown', function (e) {
            padStates[canvasId].drawing = true;
            padStates[canvasId].hasInk = true;
            var p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener('mousemove', function (e) {
            if (!padStates[canvasId].drawing) return;
            var p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        });
        window.addEventListener('mouseup', function () {
            padStates[canvasId].drawing = false;
        });

        canvas.addEventListener('touchstart', function (e) {
            e.preventDefault();
            padStates[canvasId].drawing = true;
            padStates[canvasId].hasInk = true;
            var p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }, { passive: false });
        canvas.addEventListener('touchmove', function (e) {
            e.preventDefault();
            if (!padStates[canvasId].drawing) return;
            var p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        }, { passive: false });
        canvas.addEventListener('touchend', function () {
            padStates[canvasId].drawing = false;
        });
    }

    function clearPad(canvasId) {
        var canvas = document.getElementById(canvasId);
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (padStates[canvasId]) padStates[canvasId].hasInk = false;
    }

    function captureSignaturesBeforeSubmit() {
        if (padStates['canvas_sig_1'] && padStates['canvas_sig_1'].hasInk) {
            document.getElementById('first_party_signature').value = document.getElementById('canvas_sig_1').toDataURL('image/png');
        }
        if (padStates['canvas_sig_2'] && padStates['canvas_sig_2'].hasInk) {
            document.getElementById('second_party_signature').value = document.getElementById('canvas_sig_2').toDataURL('image/png');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSignaturePad('canvas_sig_1');
        initSignaturePad('canvas_sig_2');

        var quickInput = document.getElementById('quick_barcode_handover');
        if (quickInput) {
            quickInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var code = quickInput.value.trim();
                    var found = itemsCatalog.find(function (it) { return it.barcode === code; });
                    if (found) {
                        addHandoverItemRow(found.id);
                        quickInput.value = '';
                    } else {
                        alert('Barcode "' + code + '" tidak ditemukan pada katalog barang.');
                    }
                }
            });
        }
    });
</script>
@endpush
