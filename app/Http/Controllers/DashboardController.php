<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Category;
use App\Models\HandoverDocument;
use App\Models\Item;
use App\Models\StockMovement;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalInAllTime = (int) StockMovement::where('movement_type', 'in')->sum('quantity');
        $totalOutAllTime = (int) StockMovement::where('movement_type', 'out')->sum('quantity');

        $stats = [
            'total_items' => Item::count(),
            'total_stock' => (int) Item::sum('stock'),
            'low_stock_count' => Item::whereColumn('stock', '<=', 'min_stock')->count(),
            'today_in_count' => StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'in')->count(),
            'today_in_qty' => (int) StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'in')->sum('quantity'),
            'today_out_count' => StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'out')->count(),
            'today_out_qty' => (int) StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'out')->sum('quantity'),
            'active_bast_count' => HandoverDocument::where('document_type', 'bast')->where('status', 'active')->count(),
            'total_bap_count' => HandoverDocument::where('document_type', 'bap')->count(),
            'today_access_count' => AccessLog::whereDate('accessed_at', $today)->whereIn('event_type', ['login', 'logout'])->count(),
            'total_in_all' => $totalInAllTime,
            'total_out_all' => $totalOutAllTime,
            'status_sedia' => Item::where('availability_status', Item::STATUS_SEDIA)->count(),
            'status_sedang_keluar' => Item::where('availability_status', Item::STATUS_SEDANG_KELUAR)->count(),
            'status_sedang_dipakai' => Item::where('availability_status', Item::STATUS_SEDANG_DIPAKAI)->count(),
            'status_sedang_dipinjam' => Item::where('availability_status', Item::STATUS_SEDANG_DIPINJAM)->count(),
        ];

        $categoryStats = Category::withCount('items')
            ->withSum('items', 'stock')
            ->orderByDesc('items_sum_stock')
            ->get();

        $recentMovements = StockMovement::with(['item.category', 'user'])
            ->latest('scanned_at')
            ->limit(8)
            ->get();

        $lowStockItems = Item::with(['category', 'creator'])
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(6)
            ->get();

        $recentItems = Item::with(['category', 'creator'])
            ->latest()
            ->limit(5)
            ->get();

        $recentAccessLogs = AccessLog::with('user')
            ->latest('accessed_at')
            ->limit(6)
            ->get();

        $statusItems = Item::with(['category', 'creator'])
            ->orderBy('name')
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'categoryStats',
            'recentMovements',
            'lowStockItems',
            'recentItems',
            'recentAccessLogs',
            'statusItems'
        ));
    }
}
