<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Endpoint penerima pesan webhook dari Telegram
     */
    public function handle(Request $request, TelegramService $telegram): JsonResponse
    {
        $payload = $request->all();

        if (isset($payload['message'])) {
            $telegram->handleIncomingMessage($payload['message']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Endpoint untuk tes kirim pesan dari dashboard profile (admin only)
     */
    public function sendTest(Request $request, TelegramService $telegram): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$telegram->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum diatur di file .env!'
            ], 422);
        }

        $chatId = $request->input('chat_id') ?: $telegram->getDefaultChatId();
        if (!$chatId) {
            return response()->json([
                'success' => false,
                'message' => 'Chat ID Telegram tujuan belum diisi!'
            ], 422);
        }

        $time = now()->translatedFormat('l, d F Y - H:i:s');
        $user = auth()->user()->name;
        $testMsg = "🔔 <b>TES KONEKSI BOT TELEGRAM BERHASIL!</b>\n";
        $testMsg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $testMsg .= "Sistem <b>LogiScan Pro</b> berhasil terhubung dengan akun Telegram Anda.\n\n";
        $testMsg .= "👤 Penguji: <b>{$user}</b>\n";
        $testMsg .= "⏰ Waktu: {$time} WIB\n";
        $testMsg .= "🛡️ Status: 🟢 <b>Koneksi Siaga Penuh</b>\n";
        $testMsg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $testMsg .= "<i>Bot siap mengirimkan notifikasi mutasi scan dan melayani pencarian stok.</i>";

        $result = $telegram->sendMessage($chatId, $testMsg);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan uji coba berhasil terkirim ke Telegram Anda!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pesan. Pastikan Token Bot benar dan Anda sudah pernah menekan tombol /start di bot tersebut.'
        ], 500);
    }
}
