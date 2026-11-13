<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\HandoverDocument;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'handovers');

        $deletedHandovers = HandoverDocument::onlyTrashed()
            ->with(['user', 'items.item'])
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'handovers_page')
            ->withQueryString();

        $deletedItems = Item::onlyTrashed()
            ->with(['category', 'creator'])
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'items_page')
            ->withQueryString();

        $totalTrashedHandovers = HandoverDocument::onlyTrashed()->count();
        $totalTrashedItems = Item::onlyTrashed()->count();
        $totalTrash = $totalTrashedHandovers + $totalTrashedItems;

        return view('trash.index', compact(
            'deletedHandovers',
            'deletedItems',
            'totalTrashedHandovers',
            'totalTrashedItems',
            'totalTrash',
            'activeTab'
        ));
    }

    public function restoreHandover(Request $request, $id)
    {
        $handover = HandoverDocument::onlyTrashed()->findOrFail($id);
        $docNo = $handover->document_no;
        $handover->restore();

        AccessLog::record(
            'activity',
            "Memulihkan Berita Acara {$docNo} dari Sampah",
            Auth::user(),
            $request
        );

        return back()->with('success', "Berita Acara \"{$docNo}\" berhasil dipulihkan.");
    }

    public function forceDeleteHandover(Request $request, $id)
    {
        $handover = HandoverDocument::onlyTrashed()->with('photos')->findOrFail($id);
        $docNo = $handover->document_no;

        foreach ($handover->photos as $photo) {
            if (!empty($photo->photo_path) && File::exists(public_path($photo->photo_path))) {
                File::delete(public_path($photo->photo_path));
            }
        }

        $handover->forceDelete();

        AccessLog::record(
            'activity',
            "Menghapus permanen Berita Acara {$docNo} dari Sampah",
            Auth::user(),
            $request
        );

        return back()->with('success', "Berita Acara \"{$docNo}\" telah dihapus secara permanen.");
    }

    public function restoreItem(Request $request, $id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $name = $item->name;
        $item->restore();

        AccessLog::record(
            'activity',
            "Memulihkan barang \"{$name}\" dari Sampah",
            Auth::user(),
            $request
        );

        return back()->with('success', "Barang \"{$name}\" berhasil dipulihkan.");
    }

    public function forceDeleteItem(Request $request, $id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $name = $item->name;

        if (!empty($item->photo_path) && File::exists(public_path($item->photo_path))) {
            File::delete(public_path($item->photo_path));
        }

        $item->forceDelete();

        AccessLog::record(
            'activity',
            "Menghapus permanen barang \"{$name}\" dari Sampah",
            Auth::user(),
            $request
        );

        return back()->with('success', "Barang \"{$name}\" telah dihapus secara permanen.");
    }

    public function restoreAll(Request $request)
    {
        HandoverDocument::onlyTrashed()->restore();
        Item::onlyTrashed()->restore();

        AccessLog::record(
            'activity',
            "Memulihkan seluruh data (Berita Acara & Barang) dari Sampah",
            Auth::user(),
            $request
        );

        return back()->with('success', "Semua data di Sampah berhasil dipulihkan.");
    }

    public function emptyTrash(Request $request)
    {
        $trashedDocs = HandoverDocument::onlyTrashed()->with('photos')->get();
        foreach ($trashedDocs as $doc) {
            foreach ($doc->photos as $photo) {
                if (!empty($photo->photo_path) && File::exists(public_path($photo->photo_path))) {
                    File::delete(public_path($photo->photo_path));
                }
            }
            $doc->forceDelete();
        }

        $trashedItems = Item::onlyTrashed()->get();
        foreach ($trashedItems as $item) {
            if (!empty($item->photo_path) && File::exists(public_path($item->photo_path))) {
                File::delete(public_path($item->photo_path));
            }
            $item->forceDelete();
        }

        AccessLog::record(
            'activity',
            "Mengosongkan seluruh isi Sampah secara permanen",
            Auth::user(),
            $request
        );

        return back()->with('success', "Seluruh data di Sampah telah dihapus permanen.");
    }
}
