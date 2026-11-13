<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Category;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'creator', 'updater'])
            ->withCount('stockMovements');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('storage_location', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($itemType = $request->input('item_type')) {
            $query->where('item_type', $itemType);
        }

        if ($createdBy = $request->input('created_by')) {
            $query->where('created_by', $createdBy);
        }

        if ($availabilityStatus = $request->input('availability_status')) {
            if (array_key_exists($availabilityStatus, Item::availabilityOptions())) {
                $query->where('availability_status', $availabilityStatus);
            }
        }

        if ($request->input('stock_status') === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        $nextItemCode = CodeGeneratorService::nextItemCode();
        $nextBarcode = CodeGeneratorService::nextBarcodeValue();

        return view('items.index', compact(
            'items',
            'categories',
            'users',
            'nextItemCode',
            'nextBarcode'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => ['nullable', 'string', 'max:50', 'unique:items,item_code'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:items,barcode'],
            'name' => ['required', 'string', 'max:200'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'item_type' => ['required', 'in:consumable,asset'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'storage_location' => ['nullable', 'string', 'max:120'],
            'condition_status' => ['required', 'string', 'max:50'],
            'availability_status' => ['nullable', 'in:sedia,sedang_keluar,sedang_dipakai,sedang_dipinjam'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $photoPath = $this->handlePhotoUpload($request);

        $item = DB::transaction(function () use ($validated, $user, $photoPath) {
            $itemCode = !empty($validated['item_code'])
                ? strtoupper(trim($validated['item_code']))
                : CodeGeneratorService::nextItemCode();

            $barcode = !empty($validated['barcode'])
                ? trim($validated['barcode'])
                : CodeGeneratorService::nextBarcodeValue();

            $initialStock = (int) $validated['stock'];

            $newItem = Item::create([
                'item_code' => $itemCode,
                'barcode' => $barcode,
                'name' => $validated['name'],
                'photo_path' => $photoPath,
                'category_id' => $validated['category_id'] ?? null,
                'item_type' => $validated['item_type'],
                'brand' => $validated['brand'] ?? null,
                'unit' => $validated['unit'],
                'stock' => $initialStock,
                'min_stock' => (int) $validated['min_stock'],
                'storage_location' => $validated['storage_location'] ?? 'Gudang Utama',
                'condition_status' => $validated['condition_status'],
                'availability_status' => $validated['availability_status'] ?? Item::STATUS_SEDIA,
                'description' => $validated['description'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            if ($initialStock > 0) {
                StockMovement::create([
                    'reference_no' => CodeGeneratorService::nextScanReference('in'),
                    'item_id' => $newItem->id,
                    'user_id' => $user->id,
                    'movement_type' => 'in',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'source_destination' => 'Stok Awal Pendaftaran Barang Baru',
                    'partner_name' => $user->name,
                    'scan_method' => 'manual',
                    'notes' => "Stok awal ditambahkan oleh {$user->name} saat pembuatan barcode barang",
                    'scanned_at' => now(),
                ]);
            }

            AccessLog::record(
                'activity',
                "Menambahkan barang baru & membuat barcode: {$newItem->name} (Barcode: {$newItem->barcode}, Stok Awal: {$initialStock} {$newItem->unit})",
                $user
            );

            return $newItem;
        });

        return redirect()->route('items.index')
            ->with('success', "Barang \"{$item->name}\" berhasil ditambahkan dengan Barcode {$item->barcode} oleh {$user->name}.");
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'item_code' => ['required', 'string', 'max:50', Rule::unique('items', 'item_code')->ignore($item->id)],
            'barcode' => ['required', 'string', 'max:100', Rule::unique('items', 'barcode')->ignore($item->id)],
            'name' => ['required', 'string', 'max:200'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'item_type' => ['required', 'in:consumable,asset'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'storage_location' => ['nullable', 'string', 'max:120'],
            'condition_status' => ['required', 'string', 'max:50'],
            'availability_status' => ['nullable', 'in:sedia,sedang_keluar,sedang_dipakai,sedang_dipinjam'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $photoPath = $item->photo_path;

        if ($request->hasFile('photo')) {
            $this->deletePhotoFile($item->photo_path);
            $photoPath = $this->handlePhotoUpload($request);
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhotoFile($item->photo_path);
            $photoPath = null;
        }

        $item->update([
            'item_code' => strtoupper(trim($validated['item_code'])),
            'barcode' => trim($validated['barcode']),
            'name' => $validated['name'],
            'photo_path' => $photoPath,
            'category_id' => $validated['category_id'] ?? null,
            'item_type' => $validated['item_type'],
            'brand' => $validated['brand'] ?? null,
            'unit' => $validated['unit'],
            'min_stock' => (int) $validated['min_stock'],
            'storage_location' => $validated['storage_location'] ?? 'Gudang Utama',
            'condition_status' => $validated['condition_status'],
            'availability_status' => $validated['availability_status'] ?? $item->availability_status ?? Item::STATUS_SEDIA,
            'description' => $validated['description'] ?? null,
            'updated_by' => $user->id,
        ]);

        AccessLog::record(
            'activity',
            "Memperbarui data barang & barcode: {$item->name} (Barcode: {$item->barcode})",
            $user,
            $request
        );

        return redirect()->route('items.index')
            ->with('success', "Data barang \"{$item->name}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, Item $item)
    {
        $user = Auth::user();
        $itemName = $item->name;
        $itemBarcode = $item->barcode;
        $this->deletePhotoFile($item->photo_path);

        $item->delete();

        AccessLog::record(
            'activity',
            "Menghapus barang dari penyimpanan: {$itemName} (Barcode: {$itemBarcode})",
            $user,
            $request
        );

        return redirect()->route('items.index')
            ->with('success', "Barang \"{$itemName}\" telah dihapus.");
    }

    public function generateCode()
    {
        return response()->json([
            'item_code' => CodeGeneratorService::nextItemCode(),
            'barcode' => CodeGeneratorService::nextBarcodeValue(),
        ]);
    }

    public function barcodeStudio(Request $request)
    {
        $items = Item::with(['category', 'creator'])
            ->orderByDesc('id')
            ->get();

        $categories = Category::orderBy('name')->get();
        $selectedId = $request->input('item_id');
        $nextItemCode = CodeGeneratorService::nextItemCode();
        $nextBarcode = CodeGeneratorService::nextBarcodeValue();

        return view('items.barcode-studio', compact(
            'items',
            'categories',
            'selectedId',
            'nextItemCode',
            'nextBarcode'
        ));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:categories,code'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', "Kategori \"{$category->name}\" berhasil ditambahkan.");
    }

    private function handlePhotoUpload(Request $request): ?string
    {
        if (!$request->hasFile('photo') || !$request->file('photo')->isValid()) {
            return null;
        }

        $uploadDir = public_path('uploads/items');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $file = $request->file('photo');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'item_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $file->move($uploadDir, $filename);

        return 'uploads/items/' . $filename;
    }

    private function deletePhotoFile(?string $photoPath): void
    {
        if (empty($photoPath) || !str_starts_with($photoPath, 'uploads/items/')) {
            return;
        }

        $fullPath = public_path($photoPath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
