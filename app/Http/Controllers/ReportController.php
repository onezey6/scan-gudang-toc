<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function buildReportQuery(Request $request)
    {
        $query = StockMovement::with(['item.category', 'item.creator', 'user', 'handoverDocument']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('source_destination', 'like', "%{$search}%")
                    ->orWhere('partner_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($iq) use ($search) {
                        $iq->where('name', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    });
            });
        }

        if ($movementType = $request->input('movement_type')) {
            if (in_array($movementType, ['in', 'out'], true)) {
                $query->where('movement_type', $movementType);
            }
        }

        if ($itemId = $request->input('item_id')) {
            $query->where('item_id', $itemId);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('item', function ($iq) use ($categoryId) {
                $iq->where('category_id', $categoryId);
            });
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('scanned_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('scanned_at', '<=', $endDate);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $baseQuery = $this->buildReportQuery($request);

        $summary = [
            'total_transactions' => (clone $baseQuery)->count(),
            'in_transactions' => (clone $baseQuery)->where('movement_type', 'in')->count(),
            'in_qty' => (int) (clone $baseQuery)->where('movement_type', 'in')->sum('quantity'),
            'out_transactions' => (clone $baseQuery)->where('movement_type', 'out')->count(),
            'out_qty' => (int) (clone $baseQuery)->where('movement_type', 'out')->sum('quantity'),
        ];

        $movements = $baseQuery->latest('scanned_at')->paginate(15)->withQueryString();

        $items = Item::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('reports.index', compact(
            'movements',
            'summary',
            'items',
            'categories',
            'users'
        ));
    }

    public function print(Request $request)
    {
        $baseQuery = $this->buildReportQuery($request);

        $summary = [
            'total_transactions' => (clone $baseQuery)->count(),
            'in_transactions' => (clone $baseQuery)->where('movement_type', 'in')->count(),
            'in_qty' => (int) (clone $baseQuery)->where('movement_type', 'in')->sum('quantity'),
            'out_transactions' => (clone $baseQuery)->where('movement_type', 'out')->count(),
            'out_qty' => (int) (clone $baseQuery)->where('movement_type', 'out')->sum('quantity'),
        ];

        $movements = $baseQuery->latest('scanned_at')->get();

        return view('reports.print', compact('movements', 'summary'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $movements = $this->buildReportQuery($request)->latest('scanned_at')->get();
        $filename = 'Laporan-Scan-Logistik-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'No. Referensi Scan',
                'Waktu Scan',
                'Jenis Transaksi',
                'Kode Barang',
                'Kode Barcode',
                'Nama Barang',
                'Kategori',
                'Jumlah (Qty)',
                'Satuan',
                'Stok Sebelum',
                'Stok Sesudah',
                'Sumber / Tujuan',
                'Pihak Pengantar / Penerima',
                'Metode Scan',
                'Petugas Scan (Masuk/Keluar)',
                'Pembuat Master Barang',
                'Keterangan',
            ]);

            foreach ($movements as $idx => $m) {
                fputcsv($handle, [
                    $idx + 1,
                    $m->reference_no,
                    $m->scanned_at?->format('Y-m-d H:i:s'),
                    $m->movement_type === 'in' ? 'BARANG MASUK' : 'BARANG KELUAR',
                    $m->item?->item_code ?? '-',
                    $m->item?->barcode ?? '-',
                    $m->item?->name ?? '-',
                    $m->item?->category?->name ?? '-',
                    $m->quantity,
                    $m->item?->unit ?? 'Unit',
                    $m->stock_before,
                    $m->stock_after,
                    $m->source_destination,
                    $m->partner_name,
                    strtoupper($m->scan_method),
                    $m->user?->name ?? 'Sistem',
                    $m->item?->creator?->name ?? 'Admin',
                    $m->notes,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
