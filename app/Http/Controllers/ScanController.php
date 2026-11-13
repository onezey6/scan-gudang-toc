<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Item;
use App\Models\StockMovement;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with(['category', 'creator'])
            ->orderBy('name')
            ->get();

        $recentScans = StockMovement::with(['item.category', 'user'])
            ->latest('scanned_at')
            ->limit(15)
            ->get();

        $today = now()->toDateString();
        $todaySummary = [
            'in_count' => StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'in')->count(),
            'in_qty' => (int) StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'in')->sum('quantity'),
            'out_count' => StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'out')->count(),
            'out_qty' => (int) StockMovement::whereDate('scanned_at', $today)->where('movement_type', 'out')->sum('quantity'),
        ];

        $initialMode = in_array($request->input('mode'), ['in', 'out'], true)
            ? $request->input('mode')
            : 'in';

        $preselectedBarcode = $request->input('barcode', '');

        return view('scan.index', compact(
            'items',
            'recentScans',
            'todaySummary',
            'initialMode',
            'preselectedBarcode'
        ));
    }

    public function lookup(Request $request)
    {
        $code = trim((string) $request->input('barcode'));

        if ($code === '') {
            return response()->json([
                'found' => false,
                'message' => 'Masukkan atau scan kode barcode terlebih dahulu.',
            ], 422);
        }

        $item = Item::with(['category', 'creator'])
            ->where('barcode', $code)
            ->orWhere('item_code', $code)
            ->first();

        if (!$item) {
            return response()->json([
                'found' => false,
                'barcode' => $code,
                'message' => "Barang dengan kode barcode \"{$code}\" belum terdaftar di database penyimpanan.",
            ], 404);
        }

        $lastMovements = StockMovement::with('user')
            ->where('item_id', $item->id)
            ->latest('scanned_at')
            ->limit(5)
            ->get()
            ->map(function (StockMovement $m) {
                return [
                    'reference_no' => $m->reference_no,
                    'movement_type' => $m->movement_type,
                    'quantity' => $m->quantity,
                    'stock_after' => $m->stock_after,
                    'user_name' => $m->user?->name ?? 'Sistem',
                    'scanned_at' => $m->scanned_at?->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'found' => true,
            'item' => [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'barcode' => $item->barcode,
                'name' => $item->name,
                'photo_url' => $item->photo_url,
                'category_name' => $item->category?->name ?? 'Tanpa Kategori',
                'item_type' => $item->item_type,
                'item_type_label' => $item->item_type === 'asset' ? 'Barang Aset / Inventaris' : 'Barang Habis Pakai (Stok)',
                'brand' => $item->brand ?: '-',
                'unit' => $item->unit,
                'stock' => $item->stock,
                'min_stock' => $item->min_stock,
                'storage_location' => $item->storage_location ?: 'Gudang Utama',
                'condition_status' => $item->condition_status,
                'availability_status' => $item->availability_status ?? 'sedia',
                'availability_label' => $item->availability_label,
                'creator_name' => $item->creator?->name ?? 'Admin Sistem',
                'created_at_formatted' => $item->created_at?->format('d/m/Y H:i'),
                'last_movements' => $lastMovements,
            ],
        ]);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
            'movement_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'source_destination' => ['nullable', 'string', 'max:200'],
            'partner_name' => ['nullable', 'string', 'max:150'],
            'scan_method' => ['nullable', 'in:camera,scanner_device,manual'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'barcode.required' => 'Kode Barcode barang wajib di-scan atau diisi.',
            'quantity.min' => 'Jumlah barang minimal 1.',
        ]);

        $user = Auth::user();
        $code = trim($validated['barcode']);

        try {
            $result = DB::transaction(function () use ($validated, $user, $code, $request) {
                $item = Item::where('barcode', $code)
                    ->orWhere('item_code', $code)
                    ->lockForUpdate()
                    ->first();

                if (!$item) {
                    throw ValidationException::withMessages([
                        'barcode' => "Barang dengan barcode \"{$code}\" tidak ditemukan di database penyimpanan.",
                    ]);
                }

                $qty = (int) $validated['quantity'];
                $stockBefore = (int) $item->stock;
                $type = $validated['movement_type'];

                if ($type === 'out' && $stockBefore < $qty) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok barang \"{$item->name}\" tidak mencukupi! Sisa stok saat ini: {$stockBefore} {$item->unit}, sedangkan jumlah keluar yang diminta: {$qty} {$item->unit}.",
                    ]);
                }

                $stockAfter = $type === 'in'
                    ? $stockBefore + $qty
                    : $stockBefore - $qty;

                $referenceNo = CodeGeneratorService::nextScanReference($type);

                $movement = StockMovement::create([
                    'reference_no' => $referenceNo,
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                    'movement_type' => $type,
                    'quantity' => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'source_destination' => $validated['source_destination']
                        ?: ($type === 'in' ? 'Penerimaan Gudang Penyimpanan' : 'Pengeluaran Barang Logistik'),
                    'partner_name' => $validated['partner_name'] ?: '-',
                    'scan_method' => $validated['scan_method'] ?? 'scanner_device',
                    'notes' => $validated['notes'] ?? null,
                    'scanned_at' => now(),
                ]);

                $newAvailability = $type === 'in'
                    ? Item::STATUS_SEDIA
                    : (in_array($request->input('availability_status'), [Item::STATUS_SEDANG_KELUAR, Item::STATUS_SEDANG_DIPAKAI, Item::STATUS_SEDANG_DIPINJAM], true)
                        ? $request->input('availability_status')
                        : Item::STATUS_SEDANG_KELUAR);

                $item->update([
                    'stock' => $stockAfter,
                    'availability_status' => $newAvailability,
                    'updated_by' => $user->id,
                ]);

                $actionLabel = $type === 'in' ? 'BARANG MASUK' : 'BARANG KELUAR';
                AccessLog::record(
                    'activity',
                    "Scan Barcode {$actionLabel} ({$referenceNo}): {$item->name} [{$item->barcode}] sebanyak {$qty} {$item->unit} (Stok: {$stockBefore} -> {$stockAfter})",
                    $user,
                    $request
                );

                return [
                    'movement' => $movement->load(['item.category', 'user']),
                    'item' => $item->fresh(['category', 'creator']),
                ];
            });
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $movement = $result['movement'];
        $item = $result['item'];
        $typeLabel = $movement->movement_type === 'in' ? 'Barang Masuk' : 'Barang Keluar';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Scan {$typeLabel} berhasil disimpan! ({$movement->reference_no}) — {$item->name}: {$movement->quantity} {$item->unit}. Sisa stok sekarang: {$item->stock} {$item->unit}.",
                'movement' => [
                    'id' => $movement->id,
                    'reference_no' => $movement->reference_no,
                    'movement_type' => $movement->movement_type,
                    'quantity' => $movement->quantity,
                    'stock_before' => $movement->stock_before,
                    'stock_after' => $movement->stock_after,
                    'source_destination' => $movement->source_destination,
                    'partner_name' => $movement->partner_name,
                    'scan_method' => $movement->scan_method,
                    'scanned_at' => $movement->scanned_at->format('d/m/Y H:i:s'),
                    'user_name' => $user->name,
                    'user_role' => strtoupper($user->role),
                    'item_name' => $item->name,
                    'item_photo_url' => $item->photo_url,
                    'item_barcode' => $item->barcode,
                    'item_code' => $item->item_code,
                    'item_unit' => $item->unit,
                ],
                'item_stock' => $item->stock,
            ]);
        }

        return redirect()->route('scan.index', ['mode' => $movement->movement_type])
            ->with('success', "Scan {$typeLabel} ({$movement->reference_no}) untuk \"{$item->name}\" sebanyak {$movement->quantity} {$item->unit} berhasil disimpan oleh {$user->name}. Stok terkini: {$item->stock} {$item->unit}.");
    }
}
