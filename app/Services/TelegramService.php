<?php

namespace App\Services;

use App\Models\HandoverDocument;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected ?string $defaultChatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        $this->defaultChatId = config('services.telegram.chat_id') ?: env('TELEGRAM_CHAT_ID');
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    public function getBotToken(): ?string
    {
        return $this->botToken;
    }

    public function getDefaultChatId(): ?string
    {
        return $this->defaultChatId;
    }

    /**
     * Kirim pesan teks ke chat tertentu di Telegram
     */
    public function sendMessage(string|int $chatId, string $text, string $parseMode = 'HTML', ?array $replyMarkup = null): array|bool
    {
        if (!$this->isConfigured()) {
            Log::info("TelegramService: Bot token belum dikonfigurasi.");
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true,
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(8)->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("TelegramService Send Error: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("TelegramService Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi ke Chat ID default (Admin / Grup Dinas)
     */
    public function sendNotification(string $text, string $parseMode = 'HTML'): array|bool
    {
        if (!$this->defaultChatId) {
            Log::info("TelegramService: TELEGRAM_CHAT_ID belum diisi di .env.");
            return false;
        }

        return $this->sendMessage($this->defaultChatId, $text, $parseMode);
    }

    /**
     * Cari barang berdasarkan nama, kode barcode, atau kode SKU
     */
    public function searchItems(string $query): string
    {
        $clean = trim($query);
        if (empty($clean)) {
            return "⚠️ Silakan masukkan nama barang atau barcode yang ingin dicari.\nContoh: <code>/stok rompi</code> atau ketik angka barcode.";
        }

        $items = Item::query()
            ->where('barcode', $clean)
            ->orWhere('item_code', $clean)
            ->orWhere('name', 'LIKE', "%{$clean}%")
            ->limit(5)
            ->get();

        if ($items->isEmpty()) {
            return "❌ <b>BARANG TIDAK DITEMUKAN</b>\nTidak ada barang dengan nama/barcode: <code>" . e($clean) . "</code>.\n\n<i>💡 Pastikan ejaan benar atau cari menggunakan sebagian nama saja (misal: 'helm' atau 'rompi').</i>";
        }

        $msg = "🔍 <b>DITEMUKAN {$items->count()} BARANG (Pencarian: " . e($clean) . ")</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";

        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            $statusIcon = $item->stock <= $item->min_stock ? '🔴 MENIPIS' : '🟢 SIAGA';
            $msg .= "<b>{$num}. " . e($item->name) . "</b>\n";
            $msg .= "🏷️ Barcode: <code>" . e($item->barcode) . "</code>\n";
            $msg .= "🔢 SKU: <code>" . e($item->item_code) . "</code>\n";
            $msg .= "📍 Rak: <b>" . e($item->storage_location ?: 'Belum diatur') . "</b>\n";
            $msg .= "📦 Stok Fisik: <b>" . number_format($item->stock) . " " . e($item->unit) . "</b> ({$statusIcon})\n";
            $msg .= "🛡️ Kondisi: " . e($item->condition_status ?: 'Baik') . "\n";
            $msg .= "────────────────────\n";
        }

        $msg .= "<i>Data real-time sinkron dari LogiScan Pro.</i>";
        return $msg;
    }

    /**
     * Dapatkan daftar BAST yang jatuh tempo / terlambat
     */
    public function getOverdueHandovers(): string
    {
        $today = Carbon::today()->toDateString();
        $overdue = HandoverDocument::query()
            ->where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->with(['recipientUser', 'items.item'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        if ($overdue->isEmpty()) {
            return "🟢 <b>RADAR BAST BERSIH!</b>\nSeluruh dokumen serah terima berada dalam batas waktu aman. Tidak ada barang dinas yang melewati jatuh tempo.";
        }

        $msg = "🚨 <b>PERINGATAN: " . $overdue->count() . " DOKUMEN BAST MELEWATI JATUH TEMPO!</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";

        foreach ($overdue as $idx => $doc) {
            $num = $idx + 1;
            $due = Carbon::parse($doc->due_date);
            $diffDays = $due->diffInDays(Carbon::today());
            $penerima = $doc->recipientUser ? $doc->recipientUser->name : ($doc->recipient_name ?: 'Tidak tercatat');
            $itemsList = $doc->items->map(fn($i) => ($i->item ? $i->item->name : 'Item') . " (" . $i->qty . ")")->implode(', ');

            $msg .= "<b>{$num}. " . e($doc->document_no) . "</b>\n";
            $msg .= "👤 Penerima: <b>" . e($penerima) . "</b>\n";
            $msg .= "📅 Jatuh Tempo: " . $due->format('d/m/Y') . " (<b>Terlambat {$diffDays} hari</b>)\n";
            $msg .= "📦 Materiil: <i>" . e(mb_strimwidth($itemsList, 0, 50, '...')) . "</i>\n";
            $msg .= "────────────────────\n";
        }

        $msg .= "<i>Harap segera menghubungi personel terkait untuk proses pengembalian materiil dinas.</i>";
        return $msg;
    }

    /**
     * Dapatkan rekapitulasi mutasi hari ini
     */
    public function getTodaySummary(): string
    {
        $today = Carbon::today();
        $inQty = StockMovement::where('type', 'in')->whereDate('created_at', $today)->sum('qty');
        $inCount = StockMovement::where('type', 'in')->whereDate('created_at', $today)->count();
        $outQty = StockMovement::where('type', 'out')->whereDate('created_at', $today)->sum('qty');
        $outCount = StockMovement::where('type', 'out')->whereDate('created_at', $today)->count();

        $msg = "📊 <b>REKAPITULASI SCAN GUDANG HARI INI</b>\n";
        $msg .= "📅 Tanggal: " . Carbon::now()->translatedFormat('l, d F Y') . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📥 <b>Barang Masuk (Inbound):</b>\n";
        $msg .= "   • Total: <b>+" . number_format($inQty) . " unit</b> ({$inCount} transaksi)\n\n";
        $msg .= "📤 <b>Barang Keluar (Outbound):</b>\n";
        $msg .= "   • Total: <b>-" . number_format($outQty) . " unit</b> ({$outCount} transaksi)\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "<i>Seluruh pergerakan stok tercatat resmi di database LogiScan Pro.</i>";

        return $msg;
    }

    /**
     * Tangani pesan masuk dari Telegram (Webhook atau Polling)
     */
    public function handleIncomingMessage(array $message): bool
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');
        $senderName = $message['from']['first_name'] ?? 'Petugas';

        if (!$chatId || empty($text)) {
            return false;
        }

        // Perintah /start
        if ($text === '/start') {
            $welcome = "Halo, <b>" . e($senderName) . "</b>! 🫡\n\n";
            $welcome .= "Selamat datang di <b>LogiScan Bot</b> (Pusat Pengendalian Logistik TOC Satbrimob).\n";
            $welcome .= "Saya siap membantu pengecekan stok gudang dan notifikasi mutasi secara real-time.\n\n";
            $welcome .= "<b>Cara Penggunaan Cepat:</b>\n";
            $welcome .= "• Ketik langsung nama barang: <code>rompi</code>, <code>helm</code>, dll\n";
            $welcome .= "• Ketik nomor barcode: <code>899202600001</code>\n";
            $welcome .= "• Ketik <code>/bast</code> untuk cek barang jatuh tempo\n";
            $welcome .= "• Ketik <code>/rekap</code> untuk mutasi hari ini\n";
            $welcome .= "• Ketik <code>/id</code> untuk melihat Chat ID Anda\n\n";
            $welcome .= "<i>ID Chat Anda: <code>{$chatId}</code></i>";

            return (bool) $this->sendMessage($chatId, $welcome);
        }

        // Perintah /help atau /bantuan
        if ($text === '/help' || $text === '/bantuan') {
            $help = "📋 <b>PANDUAN PERINTAH BOT LOGISCAN:</b>\n";
            $help .= "━━━━━━━━━━━━━━━━━━━━\n";
            $help .= "🔍 <code>/stok [nama/barcode]</code> — Cari stok dan lokasi rak\n";
            $help .= "🚨 <code>/bast</code> — Radar BAST yang melewati jatuh tempo\n";
            $help .= "📊 <code>/rekap</code> — Rekapitulasi scan barang masuk/keluar hari ini\n";
            $help .= "🆔 <code>/id</code> — Tampilkan Chat ID Telegram Anda\n";
            $help .= "❓ <code>/bantuan</code> — Tampilkan pesan ini\n\n";
            $help .= "<i>Tips: Anda bisa langsung mengetik nama barang tanpa perintah (misal: cukup ketik 'helm').</i>";

            return (bool) $this->sendMessage($chatId, $help);
        }

        // Perintah /id
        if ($text === '/id' || $text === '/chatid') {
            $idMsg = "🆔 <b>INFORMASI CHAT ID ANDA:</b>\n";
            $idMsg .= "Chat ID: <code>{$chatId}</code>\n\n";
            $idMsg .= "<i>Masukkan angka di atas ke dalam TELEGRAM_CHAT_ID di file .env jika ingin menerima notifikasi otomatis di chat ini.</i>";

            return (bool) $this->sendMessage($chatId, $idMsg);
        }

        // Perintah /bast atau /radar
        if ($text === '/bast' || $text === '/radar' || strtolower($text) === 'bast') {
            return (bool) $this->sendMessage($chatId, $this->getOverdueHandovers());
        }

        // Perintah /rekap atau /hariini
        if ($text === '/rekap' || $text === '/hariini' || strtolower($text) === 'rekap') {
            return (bool) $this->sendMessage($chatId, $this->getTodaySummary());
        }

        // Perintah /stok atau pencarian langsung (kata kunci / barcode)
        $searchQuery = $text;
        if (str_starts_with($text, '/stok ') || str_starts_with($text, '/cari ')) {
            $searchQuery = preg_replace('/^\/(stok|cari)\s+/', '', $text);
        }

        $searchResult = $this->searchItems($searchQuery);
        return (bool) $this->sendMessage($chatId, $searchResult);
    }
}
