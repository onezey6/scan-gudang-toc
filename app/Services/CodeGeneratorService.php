<?php

namespace App\Services;

use App\Models\HandoverDocument;
use App\Models\Item;
use App\Models\StockMovement;

class CodeGeneratorService
{
    /**
     * Generate kode barang sekuensial (MAX + 1): BRG-YYYYMM-XXX
     */
    public static function nextItemCode(?string $period = null): string
    {
        $ym = $period ?: now()->format('Ym');
        $prefix = "BRG-{$ym}-";

        $lastCode = Item::where('item_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('item_code')
            ->value('item_code');

        $nextNum = 1;
        if ($lastCode && preg_match('/-(\d+)$/', $lastCode, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nilai Barcode unik (12 digit Code-128 / EAN-style): 899YYYYMMXXX
     */
    public static function nextBarcodeValue(?string $period = null): string
    {
        $ym = $period ?: now()->format('Ym');
        $prefix = "899{$ym}";

        $lastBarcode = Item::where('barcode', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('barcode')
            ->value('barcode');

        $nextNum = 1;
        if ($lastBarcode && str_starts_with($lastBarcode, $prefix)) {
            $suffix = substr($lastBarcode, strlen($prefix));
            if (is_numeric($suffix)) {
                $nextNum = ((int) $suffix) + 1;
            }
        }

        $candidate = $prefix . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
        while (Item::where('barcode', $candidate)->exists()) {
            $nextNum++;
            $candidate = $prefix . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Generate nomor referensi scan: SCN-IN-YYYYMMDD-XXX atau SCN-OUT-YYYYMMDD-XXX
     */
    public static function nextScanReference(string $movementType): string
    {
        $typeCode = strtoupper($movementType) === 'OUT' ? 'OUT' : 'IN';
        $ymd = now()->format('Ymd');
        $prefix = "SCN-{$typeCode}-{$ymd}-";

        $lastRef = StockMovement::where('reference_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $nextNum = 1;
        if ($lastRef && preg_match('/-(\d+)$/', $lastRef, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor dokumen Berita Acara: BAST-YYYYMM-XXX atau BAP-YYYYMM-XXX
     */
    public static function nextHandoverNumber(string $docType): string
    {
        $typeCode = strtolower($docType) === 'bap' ? 'BAP' : 'BAST';
        $ym = now()->format('Ym');
        $prefix = "{$typeCode}-{$ym}-";

        $lastDoc = HandoverDocument::where('document_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('document_no')
            ->value('document_no');

        $nextNum = 1;
        if ($lastDoc && preg_match('/-(\d+)$/', $lastDoc, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }
}
