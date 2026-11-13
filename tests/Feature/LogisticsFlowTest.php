<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\HandoverDocument;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LogisticsFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_records_access_log_and_opens_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@logiscan.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('access_logs', [
            'user_email' => 'admin@logiscan.id',
            'event_type' => 'login',
        ]);

        $dash = $this->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('LogiScan Pro');
    }

    public function test_user_can_create_item_with_barcode_and_creator_is_recorded(): void
    {
        $user = User::where('email', 'rina@logiscan.id')->firstOrFail();
        $this->actingAs($user);

        $response = $this->post('/items', [
            'name' => 'Scanner Zebra DS2208 Unit Uji',
            'barcode' => '899202699999',
            'item_type' => 'asset',
            'unit' => 'Unit',
            'stock' => 10,
            'min_stock' => 2,
            'storage_location' => 'Rak Uji Coba',
            'condition_status' => 'Baik',
        ]);

        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseHas('items', [
            'barcode' => '899202699999',
            'created_by' => $user->id,
            'stock' => 10,
        ]);
    }

    public function test_scan_in_and_out_updates_stock_and_records_scanning_user(): void
    {
        $user = User::where('email', 'dedi@logiscan.id')->firstOrFail();
        $item = Item::firstOrFail();
        $initialStock = $item->stock;

        $this->actingAs($user);

        // Scan Masuk (+5)
        $resIn = $this->post('/scan/process', [
            'barcode' => $item->barcode,
            'movement_type' => 'in',
            'quantity' => 5,
            'source_destination' => 'Vendor Test Masuk',
            'partner_name' => 'Kurir Test',
            'scan_method' => 'scanner_device',
        ]);
        $resIn->assertRedirect();
        $this->assertEquals($initialStock + 5, $item->fresh()->stock);

        // Scan Keluar (-3)
        $resOut = $this->post('/scan/process', [
            'barcode' => $item->barcode,
            'movement_type' => 'out',
            'quantity' => 3,
            'source_destination' => 'Divisi Test Keluar',
            'partner_name' => 'Penerima Test',
            'scan_method' => 'camera',
        ]);
        $resOut->assertRedirect();
        $this->assertEquals($initialStock + 2, $item->fresh()->stock);

        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'movement_type' => 'out',
            'quantity' => 3,
        ]);
    }

    public function test_bast_and_bap_handover_flow_works_and_syncs_stock(): void
    {
        $user = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $item = Item::firstOrFail();
        $startStock = $item->stock;

        $this->actingAs($user);

        // 1. Buat BAST (Serah Terima 2 unit + Rincian Pinjam Pakai + Foto Bukti Unlimited)
        $resBast = $this->post('/handovers', [
            'document_type' => 'bast',
            'loan_type' => 'pinjam_pakai',
            'item_status_target' => 'sedang_dipinjam',
            'document_date' => now()->toDateString(),
            'expected_return_date' => now()->addDays(7)->toDateString(),
            'purpose' => 'Operasi Pengamanan VVIP Gegana',
            'reference_letter_no' => 'SPRIN/99/XI/2026/TOC',
            'operation_location' => 'Mako Korps Brimob Kelapa Dua',
            'terms_conditions' => "1. Wajib menjaga keutuhan alat.\n2. Dilarang memindahtangankan.",
            'approver_name' => 'KOMPOL HENDRA WIJAYA',
            'approver_nip' => '79010123',
            'approver_title' => 'KA TOC GEGANA',
            'first_party_name' => $user->name,
            'first_party_nip' => 'NIP-01',
            'first_party_title' => 'Kepala Gudang',
            'first_party_unit' => 'Logistik',
            'second_party_name' => 'Penerima BAST Uji',
            'second_party_nip' => 'NIP-02',
            'second_party_title' => 'Teknisi',
            'second_party_unit' => 'Operasional',
            'sync_stock' => 1,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 2,
                    'condition_status' => 'Baik',
                    'availability_status' => 'sedang_dipinjam',
                    'serial_number' => 'SN-TOC-001, SN-TOC-002',
                    'accessories' => 'Koper Hardcase + Charger + Kabel Data',
                    'notes' => 'Test serah terima pinjam pakai',
                ],
            ],
            'photos' => [
                UploadedFile::fake()->createWithContent('bukti1.jpg', file_get_contents(public_path('images/logo-toc.jpg'))),
                UploadedFile::fake()->createWithContent('bukti2.jpg', file_get_contents(public_path('images/logo-toc.jpg'))),
            ],
            'photo_captions' => [
                'Foto kondisi depan unit saat diserahkan',
                'Foto kelengkapan koper dan charger',
            ],
        ]);

        $resBast->assertRedirect();
        $this->assertEquals($startStock - 2, $item->fresh()->stock);
        $this->assertEquals('sedang_dipinjam', $item->fresh()->availability_status);

        $bastDoc = HandoverDocument::where('second_party_name', 'Penerima BAST Uji')->firstOrFail();
        $this->assertEquals('Operasi Pengamanan VVIP Gegana', $bastDoc->purpose);
        $this->assertEquals(2, $bastDoc->photos()->count());

        // Tambah foto bukti susulan di halaman detail BAST
        $resAddPhotos = $this->post(route('handovers.photos.store', $bastDoc), [
            'photos' => [
                UploadedFile::fake()->createWithContent('bukti3.jpg', file_get_contents(public_path('images/logo-toc.jpg'))),
            ],
            'photo_captions' => [
                'Foto serah terima di lapangan',
            ],
        ]);
        $resAddPhotos->assertRedirect();
        $this->assertEquals(3, $bastDoc->photos()->count());
        $this->get(route('handovers.show', $bastDoc))->assertStatus(200);

        // 2. Buat BAP (Pengembalian 2 unit dari BAST tersebut)
        $resBap = $this->post('/handovers', [
            'document_type' => 'bap',
            'related_bast_id' => $bastDoc->id,
            'loan_type' => 'pengembalian',
            'item_status_target' => 'sedia',
            'document_date' => now()->toDateString(),
            'first_party_name' => 'Penerima BAST Uji',
            'second_party_name' => $user->name,
            'sync_stock' => 1,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 2,
                    'condition_status' => 'Baik',
                    'availability_status' => 'sedia',
                    'serial_number' => 'SN-TOC-001, SN-TOC-002',
                    'accessories' => 'Lengkap sesuai BAST',
                    'notes' => 'Dikembalikan lengkap',
                ],
            ],
        ]);

        $resBap->assertRedirect();
        $this->assertEquals($startStock, $item->fresh()->stock);
        $this->assertEquals('sedia', $item->fresh()->availability_status);
        $this->assertEquals('returned', $bastDoc->fresh()->status);
    }

    public function test_all_pages_render_without_error(): void
    {
        $user = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $item = Item::firstOrFail();
        $handover = HandoverDocument::firstOrFail();

        $this->actingAs($user);

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/items')->assertStatus(200);
        $this->get('/barcode-studio')->assertStatus(200);
        $this->get('/scan')->assertStatus(200);
        $this->get('/scan/lookup?barcode=' . $item->barcode)->assertStatus(200)->assertJson(['found' => true]);
        $this->get('/reports')->assertStatus(200);
        $this->get('/reports/print')->assertStatus(200);
        $this->get('/reports/export-csv')->assertStatus(200);
        $this->get('/handovers')->assertStatus(200);
        $this->get('/handovers/create?type=bast')->assertStatus(200);
        $this->get('/handovers/create?type=bap')->assertStatus(200);
        $this->get('/handovers/' . $handover->id)->assertStatus(200);
        $this->get('/handovers/' . $handover->id . '/print')->assertStatus(200);
        $this->get('/access-logs')->assertStatus(200);
        $this->get('/profile')->assertStatus(200);
        $this->get('/trash')->assertStatus(200);

        // Guest page
        auth()->logout();
        $this->get('/forgot-password')->assertStatus(200);
    }

    public function test_handover_deletion_and_trash_restore_flow(): void
    {
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);

        // Buat dummy handover untuk dites hapus
        $handover = HandoverDocument::create([
            'document_no' => 'BAST-TEST-DEL-001',
            'document_type' => 'bast',
            'loan_type' => 'pinjam_pakai',
            'item_status_target' => 'sedang_dipinjam',
            'document_date' => now()->toDateString(),
            'first_party_name' => 'Petugas Satu',
            'second_party_name' => 'Penerima Dua',
            'user_id' => $admin->id,
            'status' => 'active',
        ]);

        // 1. Soft delete handover
        $resDelete = $this->delete(route('handovers.destroy', $handover));
        $resDelete->assertRedirect(route('handovers.index'));
        $this->assertSoftDeleted('handover_documents', ['id' => $handover->id]);

        // 2. Buka menu sampah
        $resTrash = $this->get('/trash');
        $resTrash->assertStatus(200);
        $resTrash->assertSee('BAST-TEST-DEL-001');

        // 3. Restore handover dari sampah
        $resRestore = $this->post(route('trash.handovers.restore', $handover->id));
        $resRestore->assertRedirect();
        $this->assertNotSoftDeleted('handover_documents', ['id' => $handover->id]);

        // 4. Force delete handover
        $this->delete(route('handovers.destroy', $handover));
        $resForce = $this->delete(route('trash.handovers.force-delete', $handover->id));
        $resForce->assertRedirect();
        $this->assertDatabaseMissing('handover_documents', ['id' => $handover->id]);
    }

    public function test_forgot_password_and_reset_flow(): void
    {
        $user = User::where('email', 'rina@logiscan.id')->firstOrFail();

        // 1. Ajukan reset via username / email
        $resRequest = $this->post('/forgot-password', [
            'identity' => 'rina.staff',
            'reset_channel' => 'email',
        ]);
        $resRequest->assertRedirect(route('password.reset'));

        // Cek OTP di session
        $otp = session('reset_demo_otp');
        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp));

        // 2. Submit password baru
        $resReset = $this->post('/reset-password', [
            'otp' => $otp,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $resReset->assertRedirect(route('login'));

        // 3. Login dengan password baru
        $resLogin = $this->post('/login', [
            'login' => 'rina.staff',
            'password' => 'newpassword123',
        ]);
        $resLogin->assertRedirect(route('dashboard'));
    }

    public function test_access_log_records_location_and_admin_only_visibility(): void
    {
        // 1. Login dengan koordinat lokasi
        $resLogin = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password123',
            'latitude' => -6.353372,
            'longitude' => 106.848831,
        ]);
        $resLogin->assertRedirect(route('dashboard'));

        $latestLog = AccessLog::latest('id')->firstOrFail();
        $this->assertEquals(-6.353372, round($latestLog->latitude, 6));
        $this->assertEquals(106.848831, round($latestLog->longitude, 6));
        $this->assertStringContainsString('maps?q=', $latestLog->google_maps_url);

        // 2. Admin melihat link Google Maps di halaman log akses
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);
        $resAdmin = $this->get('/access-logs');
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('📍 Buka Google Maps');

        // 3. Non-admin (staff) TIDAK melihat link Google Maps
        $staff = User::where('role', 'staff')->firstOrFail();
        $this->actingAs($staff);
        $resStaff = $this->get('/access-logs');
        $resStaff->assertStatus(200);
        $resStaff->assertDontSee('📍 Buka Google Maps');
    }
}
