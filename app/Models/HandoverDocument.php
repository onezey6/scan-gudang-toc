<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HandoverDocument extends Model
{
    protected $fillable = [
        'document_no',
        'document_type',
        'loan_type',
        'item_status_target',
        'related_bast_id',
        'document_date',
        'purpose',
        'reference_letter_no',
        'operation_location',
        'expected_return_date',
        'user_id',
        'first_party_name',
        'first_party_nip',
        'first_party_title',
        'first_party_unit',
        'second_party_name',
        'second_party_nip',
        'second_party_title',
        'second_party_unit',
        'second_party_phone',
        'status',
        'sync_stock',
        'first_party_signature',
        'second_party_signature',
        'notes',
        'terms_conditions',
        'approver_name',
        'approver_nip',
        'approver_title',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'expected_return_date' => 'date',
            'sync_stock' => 'boolean',
        ];
    }

    public static function loanTypeOptions(): array
    {
        return [
            'pinjam_pakai' => 'Pinjam Pakai Operasional',
            'operasional_khusus' => 'Penugasan / Operasi Khusus',
            'inventaris_unit' => 'Penyerahan Inventaris Unit',
            'perawatan' => 'Perawatan / Kalibrasi / Perbaikan',
        ];
    }

    public static function defaultBorrowingTerms(): string
    {
        return "1. KEPERLUAN KEDINASAN: Barang inventaris logistik yang diserahterimakan hanya dipergunakan untuk kepentingan dinas/operasional sesuai keperluan yang tercantum dalam Berita Acara ini.\n"
            . "2. TANGGUNG JAWAB PEMELIHARAAN: PIHAK KEDUA (Peminjam) bertanggung jawab penuh menjaga keamanan, kebersihan fisik, keutuhan stiker barcode, nomor seri, serta kelengkapan aksesoris barang selama masa peminjaman.\n"
            . "3. LARANGAN PEMINDAHTANGANAN: Dilarang keras memindahtangankan, meminjamkan ulang kepada pihak lain, atau mengubah spesifikasi teknis barang tanpa persetujuan tertulis dari Kepala Logistik / Komandan Unit.\n"
            . "4. KEWAJIBAN PENGAMANAN & PELAPORAN INSIDEN: Apabila terjadi kerusakan fungsi, cacat fisik, atau kehilangan barang, PIHAK KEDUA wajib melaporkan secara tertulis kepada Tim Logistik paling lambat 1x24 jam sejak kejadian.\n"
            . "5. GANTI RUGI & TANGGUNG JAWAB DISIPLIN: Kerusakan atau kehilangan akibat kelalaian operasional, kelalaian penyimpanan, atau penyalahgunaan menjadi tanggung jawab penuh PIHAK KEDUA untuk memperbaiki atau mengganti barang yang setara sesuai ketentuan kedinasan yang berlaku.\n"
            . "6. KETENTUAN PENGEMBALIAN TEPAT WAKTU: Barang wajib dikembalikan ke Gudang Logistik sesuai batas waktu pengembalian yang ditetapkan atau segera setelah giat/operasi selesai, disertai pemeriksaan fisik bersama dan penerbitan Berita Acara Pengembalian (BAP).";
    }

    public static function defaultReturnTerms(): string
    {
        return "1. PEMERIKSAAN & VERIFIKASI FISIK: Seluruh barang inventaris yang diserahkan kembali telah diperiksa secara fisik, fungsi kelistrikan/mekanikal, keutuhan label barcode, dan kelengkapan aksesoris oleh Petugas Gudang Logistik (PIHAK KEDUA).\n"
            . "2. KESESUAIAN KONDISI BARANG: Kondisi barang saat diterima kembali dinyatakan sesuai dengan catatan hasil pemeriksaan fisik (Baik/Lengkap, Rusak Ringan, atau Rusak Berat) sebagaimana tercantum dalam rincian Berita Acara Pengembalian ini.\n"
            . "3. PELEPASAN TANGGUNG JAWAB OPERASIONAL: Dengan ditandatanganinya Berita Acara Pengembalian (BAP) ini, tanggung jawab penguasaan operasional barang dari PIHAK PERTAMA (Pengembali) dinyatakan telah selesai dan beralih kembali sepenuhnya kepada Gudang Logistik TOC.\n"
            . "4. TINDAK LANJUT KERUSAKAN / KEKURANGAN: Apabila ditemukan catatan kerusakan atau kekurangan aksesoris pada dokumen ini, PIHAK PERTAMA tetap berkewajiban menyelesaikan berita acara tindak lanjut perbaikan/penggantian sesuai ketentuan disiplin kedinasan.\n"
            . "5. PENGARSIPAN & PEMUTAKHIRAN STOK: Dokumen Berita Acara Pengembalian (BAP) ini menjadi dasar sah mutasi pengembalian barang ke dalam stok penyimpanan aktif ('sedia') serta penutupan status dokumen BAST terkait.";
    }

    public static function defaultTermsAndConditions(?string $type = 'bast'): string
    {
        return $type === 'bap' ? self::defaultReturnTerms() : self::defaultBorrowingTerms();
    }

    public function getLoanTypeLabelAttribute(): string
    {
        return self::loanTypeOptions()[$this->loan_type ?? 'pinjam_pakai'] ?? 'Pinjam Pakai Operasional';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedBast(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_bast_id');
    }

    public function returnDocuments(): HasMany
    {
        return $this->hasMany(self::class, 'related_bast_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HandoverItem::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(HandoverPhoto::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
