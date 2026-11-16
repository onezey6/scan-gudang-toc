<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll {--timeout=20 : Waktu long-polling dalam detik}';
    protected $description = 'Jalankan listener bot Telegram secara lokal (long polling tanpa butuh webhook/domain publik)';

    public function handle(TelegramService $telegram): int
    {
        if (!$telegram->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN belum diisi di file .env!');
            $this->line('1. Buka Telegram dan chat ke @BotFather untuk buat bot baru.');
            $this->line('2. Salin token API dan masukkan ke TELEGRAM_BOT_TOKEN di .env.');
            return self::FAILURE;
        }

        $botToken = $telegram->getBotToken();
        $this->info("=================================================");
        $this->info("🚀 LOGISCAN TELEGRAM BOT POLLER AKTIF");
        $this->info("=================================================");
        $this->line("Mode: Long Polling (Lokal / XAMPP Siaga)");
        $this->line("Menunggu pesan dari pengguna di Telegram... (Tekan Ctrl+C untuk berhenti)\n");

        $offset = 0;
        $timeout = (int) $this->option('timeout');

        while (true) {
            try {
                $response = Http::timeout($timeout + 5)->get("https://api.telegram.org/bot{$botToken}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => $timeout,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $updates = $data['result'] ?? [];

                    foreach ($updates as $update) {
                        $offset = $update['update_id'] + 1;

                        if (isset($update['message'])) {
                            $msg = $update['message'];
                            $text = $msg['text'] ?? '';
                            $sender = $msg['from']['first_name'] ?? 'User';
                            $chatId = $msg['chat']['id'] ?? '';

                            $time = now()->format('H:i:s');
                            $this->line("<fg=cyan>[{$time}]</> Pesan dari <fg=yellow>{$sender}</> (Chat ID: {$chatId}): \"<fg=white>{$text}</>\"");

                            $handled = $telegram->handleIncomingMessage($msg);
                            if ($handled) {
                                $this->info("  ↳ Berhasil memproses & membalas pesan.");
                            }
                        }
                    }
                } else {
                    $this->warn("Koneksi ke Telegram gagal (Status {$response->status()}). Mencoba ulang...");
                    sleep(3);
                }
            } catch (\Throwable $e) {
                $this->error("Error polling: " . $e->getMessage());
                sleep(3);
            }
        }

        return self::SUCCESS;
    }
}
