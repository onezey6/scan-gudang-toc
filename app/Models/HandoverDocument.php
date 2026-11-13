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

    public static function defaultTermsAndConditions(): string
    {
        return "1. KEPERLUAN DINAS: Barang inventaris logistik yang diserahterimakan hanya dipergunakan untuk kepentingan dinas/operasional sesuai keperluan yang tercantum dalam Berita Acara ini.\n"
            . "2. TANGGUNG JAWAB PEMELIHARAAN: PIHAK KEDUA (Penerima/Peminjam) wajib menjaga keamanan, kebersihan, keutuhan label barcode/nomor seri, serta kelengkapan fungsi barang selama dalam penguasaan.\n"
            . "3. LARANGAN PINDAH TANGAN: Dilarang keras memindahtangankan, meminjamkan ulang, atau mengubah spesifikasi barang tanpa izin tertulis dari Kepala Logistik / Komandan Unit.\n"
            . "4. RISIKO KERUSAKAN & KEHILANGAN: Apabila terjadi kerusakan, gangguan fungsi, atau kehilangan barang, PIHAK KEDUA wajib melaporkan kepada Tim Logistik paling lambat 1x24 jam dan bertanggung jawab penuh sesuai ketentuan disiplin dinas yang berlaku.\n"
            . "5. KETENTUAN PENGEMBALIAN: Barang wajib dikembalikan ke Gudang Logistik tepat waktu sesuai batas pengembalian atau segera setelah giat/operasi selesai, disertai pengecekan fisik bersama dan penerbitan Berita Acara Pengembalian (BAP).";
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
