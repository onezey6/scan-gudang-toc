<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_telegram_service_searches_items_by_name_and_barcode(): void
    {
        $admin = User::first();
        $item = Item::create([
            'name' => 'Rompi Taktis Level IV',
            'barcode' => '899202611111',
            'item_code' => 'BRG-TAC-001',
            'item_type' => 'asset',
            'unit' => 'Pcs',
            'stock' => 15,
            'min_stock' => 2,
            'storage_location' => 'Rak B2 (Perlindungan)',
            'condition_status' => 'Baik',
            'created_by' => $admin?->id,
        ]);

        $service = app(TelegramService::class);

        // Cari via Nama Barang
        $resName = $service->searchItems('Rompi');
        $this->assertStringContainsString('DITEMUKAN', $resName);
        $this->assertStringContainsString('Rompi Taktis Level IV', $resName);

        // Cari via Barcode Barang
        $resBarcode = $service->searchItems($item->barcode);
        $this->assertStringContainsString('DITEMUKAN', $resBarcode);
        $this->assertStringContainsString($item->barcode, $resBarcode);
        $this->assertStringContainsString($item->storage_location, $resBarcode);

        // Cari barang tidak ada
        $resNotFound = $service->searchItems('BarangFiktifGaib9999');
        $this->assertStringContainsString('BARANG TIDAK DITEMUKAN', $resNotFound);
    }

    public function test_telegram_service_handles_commands_and_returns_responses(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $service = new class extends TelegramService {
            public function isConfigured(): bool
            {
                return true;
            }
            public function getBotToken(): ?string
            {
                return 'fake-token-for-test';
            }
        };

        // Test /start
        $startHandled = $service->handleIncomingMessage([
            'chat' => ['id' => 123456],
            'text' => '/start',
            'from' => ['first_name' => 'Budi'],
        ]);
        $this->assertTrue($startHandled);

        // Test /help
        $helpHandled = $service->handleIncomingMessage([
            'chat' => ['id' => 123456],
            'text' => '/help',
            'from' => ['first_name' => 'Budi'],
        ]);
        $this->assertTrue($helpHandled);

        // Test /id
        $idHandled = $service->handleIncomingMessage([
            'chat' => ['id' => 123456],
            'text' => '/id',
            'from' => ['first_name' => 'Budi'],
        ]);
        $this->assertTrue($idHandled);

        // Test search via text
        $searchHandled = $service->handleIncomingMessage([
            'chat' => ['id' => 123456],
            'text' => 'rompi',
            'from' => ['first_name' => 'Budi'],
        ]);
        $this->assertTrue($searchHandled);
    }

    public function test_telegram_webhook_endpoint_returns_200_ok(): void
    {
        $response = $this->postJson('/api/telegram/webhook', [
            'update_id' => 9999,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/start',
                'from' => ['first_name' => 'Komandan'],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }

    public function test_profile_page_displays_telegram_bot_card_for_admin(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin);

        $response = $this->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Bot Telegram LogiScan');
        $response->assertSee('php artisan telegram:poll');
        $response->assertSee('Tes Kirim Pesan ke Telegram');
    }

    public function test_telegram_test_endpoint_validates_unconfigured_token(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin);

        config(['services.telegram.bot_token' => null]);

        $response = $this->postJson(route('telegram.test'), []);
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
