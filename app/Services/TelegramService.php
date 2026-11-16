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

    public function __construct(?string $botToken = null, ?string $defaultChatId = null)
    {
        $this->botToken = $botToken ?? (config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN'));
        $this->defaultChatId = $defaultChatId ?? (config('services.telegram.chat_id') ?: env('TELEGRAM_CHAT_ID'));
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
     * Keyboard Menu Tombol Bawah (Persistent Reply Keyboard)
     */
    public function getDefaultKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '🔍 Cari Barang Cepat'],
                    ['text' => '📦 5 Barang Teratas']
                ],
                [
                    ['text' => '🚨 Radar BAST Terlambat'],
                    ['text' => '📊 Rekap Scan Hari Ini']
                ],
                [
                    ['text' => '📋 Menu Bantuan'],
                    ['text' => '🆔 Info Chat ID']
                ]
            ],
            'resize_keyboard' => true,
            'is_persistent' => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Keyboard Pilihan Cepat Barang Berdasarkan Database Nyata
     */
    public function getQuickSearchKeyboard(): array
    {
        $items = Item::orderBy('name', 'asc')->limit(6)->pluck('name')->toArray();
        $rows = [];

        for ($i = 0; $i < count($items); $i += 2) {
            $row = [];
            $row[] = ['text' => $items[$i]];
            if (isset($items[$i + 1])) {
                $row[] = ['text' => $items[$i + 1]];
            }
            $rows[] = $row;
        }

        $rows[] = [['text' => '⬅️ Kembali ke Menu Utama']];

        return [
            'keyboard' => $rows,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Daftarkan daftar perintah ke Menu biru Telegram resmi
     */
    public function registerBotCommands(): bool
    {
        if (!$this->isConfigured()) return false;

        $commands = [
            ['command' => 'menu', 'description' => 'Tampilkan tombol menu pilihan utama'],
            ['command' => 'stok', 'description' => 'Pilih & cari stok barang gudang'],
            ['command' => 'katalog', 'description' => 'Lihat daftar 5 barang teratas'],
            ['command' => 'bast', 'description' => 'Radar dokumen BAST yang jatuh tempo'],
            ['command' => 'rekap', 'description' => 'Rekapitulasi scan hari ini'],
            ['command' => 'id', 'description' => 'Lihat Chat ID Telegram Anda'],
            ['command' => 'bantuan', 'description' => 'Panduan penggunaan bot'],
        ];

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/setMyCommands";
            $res = Http::withoutVerifying()->timeout(8)->post($url, [
                'commands' => json_encode($commands)
            ]);
            return $res->successful();
        } catch (\Throwable $e) {
            return false;
        }
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

            $markup = $replyMarkup ?: $this->getDefaultKeyboard();
            $payload['reply_markup'] = json_encode($markup);

            $response = Http::withoutVerifying()->timeout(8)->post($url, $payload);

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

        return $this->sendMessage($this->defaultChatId, $text, $parseMode, $this->getDefaultKeyboard());
    }

    /**
     * Cari barang berdasarkan nama, kode barcode, atau kode SKU
     */
    public function searchItems(string $query): string
    {
        $clean = trim($query);
        if (empty($clean)) {
            return "⚠️ Silakan masukkan nama barang atau barcode yang ingin dicari.\nContoh: <code>rompi</code> atau ketik angka barcode.";
        }

        $items = Item::query()
            ->where('barcode', $clean)
            ->orWhere('item_code', $clean)
            ->orWhere('name', 'LIKE', "%{$clean}%")
            ->limit(5)
            ->get();

        if ($items->isEmpty()) {
            return "❌ <b>BARANG TIDAK DITEMUKAN</b>\nTidak ada barang dengan nama/barcode: <code>" . e($clean) . "</code>.\n\n<i>💡 Pastikan ejaan benar atau gunakan tombol menu di bawah untuk memilih barang.</i>";
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

        $msg .= "<i>Data real-time sinkron dari database LogiScan Pro.</i>";
        return $msg;
    }

    /**
     * Dapatkan daftar 5 barang teratas di gudang
     */
    public function getTopItems(): string
    {
        $items = Item::orderBy('stock', 'desc')->limit(5)->get();

        if ($items->isEmpty()) {
            return "📦 <b>GUDANG KOSONG</b>\nBelum ada data barang yang tersimpan di sistem.";
        }

        $msg = "📦 <b>DAFTAR 5 BARANG SIAGA TERBANYAK DI GUDANG</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";

        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            $msg .= "<b>{$num}. " . e($item->name) . "</b>\n";
            $msg .= "   • Barcode: <code>" . e($item->barcode) . "</code>\n";
            $msg .= "   • Stok: <b>" . number_format($item->stock) . " " . e($item->unit) . "</b> (Rak: " . e($item->storage_location ?: 'Utama') . ")\n\n";
        }

        $msg .= "<i>Ketik nama barang lain untuk mencari secara spesifik.</i>";
        return $msg;
    }

    /**
     * Dapatkan daftar BAST yang jatuh tempo / terlambat
     */
    public function getOverdueHandovers(): string
    {
        $today = Carbon::today()->toDateString();
        $overdue = HandoverDocument::query()
            ->where('document_type', 'bast')
            ->where('status', 'active')
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', $today)
            ->with(['items.item', 'user'])
            ->orderBy('expected_return_date', 'asc')
            ->limit(5)
            ->get();

        if ($overdue->isEmpty()) {
            return "🟢 <b>RADAR BAST BERSIH!</b>\nSeluruh dokumen serah terima berada dalam batas waktu aman. Tidak ada barang dinas yang melewati jatuh tempo.";
        }

        $msg = "🚨 <b>PERINGATAN: " . $overdue->count() . " DOKUMEN BAST MELEWATI JATUH TEMPO!</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";

        foreach ($overdue as $idx => $doc) {
            $num = $idx + 1;
            $due = Carbon::parse($doc->expected_return_date);
            $diffDays = $due->diffInDays(Carbon::today());
            $penerima = $doc->second_party_name ?: 'Tidak tercatat';
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
        $today = Carbon::today()->toDateString();
        $inQty = (int) StockMovement::where('movement_type', 'in')->whereDate('scanned_at', $today)->sum('quantity');
        $inCount = StockMovement::where('movement_type', 'in')->whereDate('scanned_at', $today)->count();
        $outQty = (int) StockMovement::where('movement_type', 'out')->whereDate('scanned_at', $today)->sum('quantity');
        $outCount = StockMovement::where('movement_type', 'out')->whereDate('scanned_at', $today)->count();

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

        // Perintah /start, /menu, atau Kembali ke Menu
        if ($text === '/start' || $text === '/menu' || $text === '⬅️ Kembali ke Menu Utama') {
            $welcome = "Halo, <b>" . e($senderName) . "</b>! 🫡\n\n";
            $welcome .= "Silakan <b>klik tombol menu pilihan di bawah</b> untuk memilih aksi cepat tanpa perlu mengetik manual:\n\n";
            $welcome .= "• <b>🔍 Cari Barang Cepat:</b> Pilih langsung nama barang dari gudang\n";
            $welcome .= "• <b>📦 5 Barang Teratas:</b> Cek inventaris siaga terbanyak\n";
            $welcome .= "• <b>🚨 Radar BAST Terlambat:</b> Peringatan barang jatuh tempo\n";
            $welcome .= "• <b>📊 Rekap Scan Hari Ini:</b> Ringkasan mutasi masuk/keluar\n";

            return (bool) $this->sendMessage($chatId, $welcome, 'HTML', $this->getDefaultKeyboard());
        }

        // Tombol: 🔍 Cari Barang Cepat
        if ($text === '🔍 Cari Barang Cepat') {
            $prompt = "🔍 <b>PILIH BARANG GUDANG DI BAWAH INI:</b>\n\n";
            $prompt .= "Sentuh salah satu tombol barang di bawah untuk melihat sisa stok dan lokasi raknya secara instan, atau ketik langsung angka barcode barang.";

            return (bool) $this->sendMessage($chatId, $prompt, 'HTML', $this->getQuickSearchKeyboard());
        }

        // Tombol: 📦 5 Barang Teratas atau /katalog
        if ($text === '📦 5 Barang Teratas' || $text === '/katalog') {
            return (bool) $this->sendMessage($chatId, $this->getTopItems(), 'HTML', $this->getDefaultKeyboard());
        }

        // Tombol: 🚨 Radar BAST Terlambat atau /bast
        if ($text === '🚨 Radar BAST Terlambat' || $text === '/bast' || $text === '/radar' || strtolower($text) === 'bast') {
            return (bool) $this->sendMessage($chatId, $this->getOverdueHandovers(), 'HTML', $this->getDefaultKeyboard());
        }

        // Tombol: 📊 Rekap Scan Hari Ini atau /rekap
        if ($text === '📊 Rekap Scan Hari Ini' || $text === '/rekap' || $text === '/hariini' || strtolower($text) === 'rekap') {
            return (bool) $this->sendMessage($chatId, $this->getTodaySummary(), 'HTML', $this->getDefaultKeyboard());
        }

        // Tombol: 🆔 Info Chat ID atau /id
        if ($text === '🆔 Info Chat ID' || $text === '/id' || $text === '/chatid') {
            $idMsg = "🆔 <b>INFORMASI AKUN TELEGRAM ANDA:</b>\n";
            $idMsg .= "• Nama: <b>" . e($senderName) . "</b>\n";
            $idMsg .= "• Chat ID: <code>{$chatId}</code>\n\n";
            $idMsg .= "<i>Chat ID ini telah terdaftar resmi di sistem LogiScan Pro untuk menerima notifikasi mutasi otomatis.</i>";

            return (bool) $this->sendMessage($chatId, $idMsg, 'HTML', $this->getDefaultKeyboard());
        }

        // Tombol: 📋 Menu Bantuan atau /help
        if ($text === '📋 Menu Bantuan' || $text === '/help' || $text === '/bantuan') {
            $help = "📋 <b>PANDUAN PENGGUNAAN BOT LOGISCAN:</b>\n";
            $help .= "━━━━━━━━━━━━━━━━━━━━\n";
            $help .= "Komandan dapat langsung <b>menekan tombol menu di bawah layar</b> tanpa harus mengetik manual.\n\n";
            $help .= "<b>Jika ingin mencari barang spesifik:</b>\n";
            $help .= "• Cukup ketik potongan nama: <code>helm</code>, <code>rompi</code>, <code>nvg</code>\n";
            $help .= "• Atau ketik nomor barcode: <code>899202611111</code>\n\n";
            $help .= "<i>Bot akan langsung mencari dan membalas data dari database gudang.</i>";

            return (bool) $this->sendMessage($chatId, $help, 'HTML', $this->getDefaultKeyboard());
        }

        // Perintah /stok dengan query atau pencarian langsung (kata kunci / barcode)
        $searchQuery = $text;
        if (str_starts_with($text, '/stok ') || str_starts_with($text, '/cari ')) {
            $searchQuery = preg_replace('/^\/(stok|cari)\s+/', '', $text);
        }

        $searchResult = $this->searchItems($searchQuery);
        return (bool) $this->sendMessage($chatId, $searchResult, 'HTML', $this->getDefaultKeyboard());
    }
}
