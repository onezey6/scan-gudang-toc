<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\HandoverDocument;
use App\Models\HandoverItem;
use App\Models\HandoverPhoto;
use App\Models\Item;
use App\Models\StockMovement;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class HandoverController extends Controller
{
    public function index(Request $request)
    {
        $query = HandoverDocument::with(['user', 'items.item', 'relatedBast', 'photos']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('document_no', 'like', "%{$search}%")
                    ->orWhere('first_party_name', 'like', "%{$search}%")
                    ->orWhere('second_party_name', 'like', "%{$search}%")
                    ->orWhere('second_party_unit', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('reference_letter_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($docType = $request->input('document_type')) {
            if (in_array($docType, ['bast', 'bap'], true)) {
                $query->where('document_type', $docType);
            }
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $documents = $query->latest('document_date')->latest('id')->paginate(10)->withQueryString();

        $summary = [
            'total_bast' => HandoverDocument::where('document_type', 'bast')->count(),
            'active_bast' => HandoverDocument::where('document_type', 'bast')->where('status', 'active')->count(),
            'returned_bast' => HandoverDocument::where('document_type', 'bast')->where('status', 'returned')->count(),
            'total_bap' => HandoverDocument::where('document_type', 'bap')->count(),
            'status_sedia' => Item::where('availability_status', Item::STATUS_SEDIA)->count(),
            'status_sedang_keluar' => Item::where('availability_status', Item::STATUS_SEDANG_KELUAR)->count(),
            'status_sedang_dipakai' => Item::where('availability_status', Item::STATUS_SEDANG_DIPAKAI)->count(),
            'status_sedang_dipinjam' => Item::where('availability_status', Item::STATUS_SEDANG_DIPINJAM)->count(),
        ];

        return view('handovers.index', compact('documents', 'summary'));
    }

    public function create(Request $request)
    {
        $docType = $request->input('type') === 'bap' ? 'bap' : 'bast';
        $nextDocNo = CodeGeneratorService::nextHandoverNumber($docType);

        $items = Item::with('category')->orderBy('name')->get();
        $activeBasts = HandoverDocument::with('items.item')
            ->where('document_type', 'bast')
            ->where('status', 'active')
            ->latest()
            ->get();

        $selectedBast = null;
        if ($docType === 'bap' && $request->filled('bast_id')) {
            $selectedBast = HandoverDocument::with('items.item')->find($request->input('bast_id'));
        }

        $defaultTerms = HandoverDocument::defaultTermsAndConditions($docType);

        return view('handovers.create', compact(
            'docType',
            'nextDocNo',
            'items',
            'activeBasts',
            'selectedBast',
            'defaultTerms'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'in:bast,bap'],
            'loan_type' => ['nullable', 'string', 'max:50'],
            'item_status_target' => ['nullable', 'in:sedia,sedang_keluar,sedang_dipakai,sedang_dipinjam'],
            'related_bast_id' => ['nullable', 'exists:handover_documents,id'],
            'document_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'reference_letter_no' => ['nullable', 'string', 'max:120'],
            'operation_location' => ['nullable', 'string', 'max:180'],
            'expected_return_date' => ['nullable', 'date'],
            'first_party_name' => ['required', 'string', 'max:150'],
            'first_party_nip' => ['nullable', 'string', 'max:60'],
            'first_party_title' => ['nullable', 'string', 'max:120'],
            'first_party_unit' => ['nullable', 'string', 'max:120'],
            'second_party_name' => ['required', 'string', 'max:150'],
            'second_party_nip' => ['nullable', 'string', 'max:60'],
            'second_party_title' => ['nullable', 'string', 'max:120'],
            'second_party_unit' => ['nullable', 'string', 'max:120'],
            'second_party_phone' => ['nullable', 'string', 'max:40'],
            'sync_stock' => ['nullable', 'boolean'],
            'first_party_signature' => ['nullable', 'string'],
            'second_party_signature' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'approver_name' => ['nullable', 'string', 'max:150'],
            'approver_nip' => ['nullable', 'string', 'max:60'],
            'approver_title' => ['nullable', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.serial_number' => ['nullable', 'string', 'max:120'],
            'items.*.accessories' => ['nullable', 'string', 'max:255'],
            'items.*.condition_status' => ['required', 'string', 'max:60'],
            'items.*.availability_status' => ['nullable', 'in:sedia,sedang_keluar,sedang_dipakai,sedang_dipinjam'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            'photo_captions' => ['nullable', 'array'],
            'photo_captions.*' => ['nullable', 'string', 'max:255'],
            'evidence_photos' => ['nullable', 'array'],
            'evidence_photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            'evidence_captions' => ['nullable', 'array'],
            'evidence_captions.*' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Pilih atau scan minimal 1 barang untuk Berita Acara ini.',
        ]);

        $user = Auth::user();
        $syncStock = $request->boolean('sync_stock', true);

        $doc = DB::transaction(function () use ($validated, $user, $syncStock, $request) {
            $docType = $validated['document_type'];
            $docNo = CodeGeneratorService::nextHandoverNumber($docType);
            $defaultTargetStatus = $docType === 'bast'
                ? ($validated['item_status_target'] ?? Item::STATUS_SEDANG_DIPINJAM)
                : Item::STATUS_SEDIA;

            $handover = HandoverDocument::create([
                'document_no' => $docNo,
                'document_type' => $docType,
                'loan_type' => $validated['loan_type'] ?? 'pinjam_pakai',
                'item_status_target' => $defaultTargetStatus,
                'related_bast_id' => $validated['related_bast_id'] ?? null,
                'document_date' => $validated['document_date'],
                'purpose' => $validated['purpose'] ?? null,
                'reference_letter_no' => $validated['reference_letter_no'] ?? null,
                'operation_location' => $validated['operation_location'] ?? null,
                'expected_return_date' => $validated['expected_return_date'] ?? null,
                'user_id' => $user->id,
                'first_party_name' => $validated['first_party_name'],
                'first_party_nip' => $validated['first_party_nip'] ?? null,
                'first_party_title' => $validated['first_party_title'] ?? null,
                'first_party_unit' => $validated['first_party_unit'] ?? null,
                'second_party_name' => $validated['second_party_name'],
                'second_party_nip' => $validated['second_party_nip'] ?? null,
                'second_party_title' => $validated['second_party_title'] ?? null,
                'second_party_unit' => $validated['second_party_unit'] ?? null,
                'second_party_phone' => $validated['second_party_phone'] ?? null,
                'status' => $docType === 'bast' ? 'active' : 'completed',
                'sync_stock' => $syncStock,
                'first_party_signature' => $validated['first_party_signature'] ?? null,
                'second_party_signature' => $validated['second_party_signature'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? HandoverDocument::defaultTermsAndConditions($docType),
                'approver_name' => $validated['approver_name'] ?? null,
                'approver_nip' => $validated['approver_nip'] ?? null,
                'approver_title' => $validated['approver_title'] ?? null,
            ]);

            foreach ($validated['items'] as $row) {
                $item = Item::lockForUpdate()->findOrFail($row['item_id']);
                $qty = (int) $row['quantity'];
                $rowAvailability = $docType === 'bap'
                    ? Item::STATUS_SEDIA
                    : ($row['availability_status'] ?? $defaultTargetStatus);

                HandoverItem::create([
                    'handover_document_id' => $handover->id,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'serial_number' => $row['serial_number'] ?? null,
                    'accessories' => $row['accessories'] ?? null,
                    'condition_status' => $row['condition_status'],
                    'availability_status' => $rowAvailability,
                    'notes' => $row['notes'] ?? null,
                ]);

                if ($syncStock) {
                    $stockBefore = (int) $item->stock;

                    if ($docType === 'bast') {
                        if ($stockBefore < $qty) {
                            throw ValidationException::withMessages([
                                'items' => "Stok barang \"{$item->name}\" tidak cukup untuk Berita Acara Serah Terima (Tersedia: {$stockBefore} {$item->unit}, Diminta: {$qty} {$item->unit}).",
                            ]);
                        }
                        $stockAfter = $stockBefore - $qty;
                        $movementType = 'out';
                        $sourceDest = "Serah Terima BAST ({$docNo}) ke {$handover->second_party_name} ({$handover->second_party_unit})";
                        $partnerName = $handover->second_party_name;
                    } else {
                        $stockAfter = $stockBefore + $qty;
                        $movementType = 'in';
                        $sourceDest = "Pengembalian Barang BAP ({$docNo}) dari {$handover->first_party_name}";
                        $partnerName = $handover->first_party_name;
                    }

                    StockMovement::create([
                        'reference_no' => CodeGeneratorService::nextScanReference($movementType),
                        'item_id' => $item->id,
                        'user_id' => $user->id,
                        'movement_type' => $movementType,
                        'quantity' => $qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'source_destination' => $sourceDest,
                        'partner_name' => $partnerName,
                        'scan_method' => 'handover',
                        'handover_document_id' => $handover->id,
                        'notes' => "Otomatis dari dokumen {$docNo} — Kondisi: {$row['condition_status']}",
                        'scanned_at' => now(),
                    ]);

                    $item->update([
                        'stock' => $stockAfter,
                        'condition_status' => $row['condition_status'],
                        'availability_status' => $rowAvailability,
                        'updated_by' => $user->id,
                    ]);
                } else {
                    // Tetap sinkronkan status ketersediaan barang meskipun stok tidak dikurangi
                    $item->update([
                        'condition_status' => $row['condition_status'],
                        'availability_status' => $rowAvailability,
                        'updated_by' => $user->id,
                    ]);
                }
            }

            if ($docType === 'bap' && !empty($validated['related_bast_id'])) {
                HandoverDocument::where('id', $validated['related_bast_id'])
                    ->update(['status' => 'returned']);
            }

            // Simpan Foto Bukti (Unlimited Photos)
            $this->saveEvidencePhotos($request, $handover, $user->id);

            $label = $docType === 'bast' ? 'Berita Acara Serah Terima (BAST)' : 'Berita Acara Pengembalian (BAP)';
            AccessLog::record(
                'activity',
                "Membuat dokumen {$label}: {$handover->document_no} ({$handover->first_party_name} <-> {$handover->second_party_name})",
                $user,
                $request
            );

            return $handover;
        });

        $typeTitle = $doc->document_type === 'bast'
            ? 'Berita Acara Serah Terima (BAST)'
            : 'Berita Acara Pengembalian (BAP)';

        return redirect()->route('handovers.show', $doc)
            ->with('success', "{$typeTitle} dengan nomor {$doc->document_no} berhasil diterbitkan & status barang telah disinkronkan!");
    }

    public function show(HandoverDocument $handover)
    {
        $handover->load(['user', 'items.item.category', 'relatedBast', 'returnDocuments', 'stockMovements.user', 'photos.uploader']);

        return view('handovers.show', compact('handover'));
    }

    public function print(HandoverDocument $handover)
    {
        $handover->load(['user', 'items.item.category', 'relatedBast', 'photos']);

        return view('handovers.print', compact('handover'));
    }

    public function uploadPhotos(Request $request, HandoverDocument $handover)
    {
        $request->validate([
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            'photo_captions' => ['nullable', 'array'],
            'photo_captions.*' => ['nullable', 'string', 'max:255'],
            'evidence_photos' => ['nullable', 'array'],
            'evidence_photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            'evidence_captions' => ['nullable', 'array'],
            'evidence_captions.*' => ['nullable', 'string', 'max:255'],
        ]);

        $count = $this->saveEvidencePhotos($request, $handover, Auth::id());

        if ($count === 0) {
            return back()->withErrors(['photos' => 'Pilih minimal 1 file foto bukti untuk diunggah.']);
        }

        return back()->with('success', "{$count} foto bukti dokumentasi berhasil ditambahkan ke dokumen {$handover->document_no}.");
    }

    public function destroy(Request $request, HandoverDocument $handover)
    {
        $docNo = $handover->document_no;
        $handover->delete();

        AccessLog::record(
            'activity',
            "Memindahkan Berita Acara {$docNo} ke Sampah (Soft Delete)",
            Auth::user(),
            $request
        );

        return redirect()->route('handovers.index')
            ->with('success', "Berita Acara {$docNo} berhasil dipindahkan ke Sampah. Anda dapat memulihkannya kapan saja di Menu Sampah.");
    }

    public function destroyPhoto(HandoverDocument $handover, HandoverPhoto $photo)
    {
        if (!empty($photo->photo_path) && str_starts_with($photo->photo_path, 'uploads/handovers/')) {
            $fullPath = public_path($photo->photo_path);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        $photo->delete();

        return back()->with('success', 'Foto bukti dokumentasi telah dihapus.');
    }

    private function saveEvidencePhotos(Request $request, HandoverDocument $handover, ?int $userId): int
    {
        $rawFiles = [];
        if ($request->hasFile('photos')) {
            $rawFiles[] = $request->file('photos');
        }
        if ($request->hasFile('evidence_photos')) {
            $rawFiles[] = $request->file('evidence_photos');
        }

        $allFiles = [];
        array_walk_recursive($rawFiles, function ($file) use (&$allFiles) {
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $allFiles[] = $file;
            }
        });

        if (empty($allFiles)) {
            return 0;
        }

        $uploadDir = public_path('uploads/handovers');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $captions = array_values(array_filter(
            array_merge(
                (array) $request->input('photo_captions', []),
                (array) $request->input('evidence_captions', [])
            ),
            function ($c) { return $c !== null && $c !== ''; }
        ));

        $saved = 0;
        foreach ($allFiles as $idx => $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'bukti_' . strtolower($handover->document_type) . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move($uploadDir, $filename);

            HandoverPhoto::create([
                'handover_document_id' => $handover->id,
                'photo_path' => 'uploads/handovers/' . $filename,
                'caption' => $captions[$idx] ?? ('Dokumentasi Bukti #' . ($saved + 1)),
                'uploaded_by' => $userId,
            ]);
            $saved++;
        }

        return $saved;
    }
}
