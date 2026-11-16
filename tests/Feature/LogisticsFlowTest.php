<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\HandoverDocument;
use App\Models\Item;
use App\Models\StockMovement;
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

    public function test_admin_can_delete_stock_movement_report_and_trashed_item_is_synced(): void
    {
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);

        // Buat item dan gerakannya
        $item = Item::firstOrFail();
        $movement = StockMovement::create([
            'reference_no' => 'SCN-TEST-DEL-001',
            'item_id' => $item->id,
            'user_id' => $admin->id,
            'movement_type' => 'in',
            'quantity' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
            'source_destination' => 'Test Source',
            'partner_name' => 'Test Partner',
            'scan_method' => 'manual',
            'notes' => 'Test note',
            'scanned_at' => now(),
        ]);

        // Hapus item (soft-delete)
        $item->delete();

        // Pastikan laporan tetap membaca nama item dan tidak null/strip
        $reportRes = $this->get('/reports');
        $reportRes->assertStatus(200);
        $reportRes->assertSee($item->name);
        $reportRes->assertSee('Tong Sampah');

        // Admin menghapus log laporan
        $delRes = $this->delete('/reports/' . $movement->id);
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
    }

    public function test_user_can_upload_profile_avatar(): void
    {
        $user = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($user);

        $fakeImage = UploadedFile::fake()->create('my-photo.jpg', 200, 'image/jpeg');

        $res = $this->put('/profile', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => $fakeImage,
        ]);

        $res->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertStringContainsString('uploads/avatars/', $user->avatar);
        $this->assertNotNull($user->avatar_url);
    }

    public function test_item_creation_works_even_when_all_existing_items_are_in_trash_without_unique_collision(): void
    {
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);

        // Soft-delete SEMUA item ke kotak sampah (seperti kondisi user: 8 item di trash)
        Item::all()->each(fn($i) => $i->delete());
        $this->assertEquals(0, Item::count());
        $this->assertGreaterThan(0, Item::onlyTrashed()->count());

        // Buka halaman tambah barang dan ambil kode otomatis
        $resPage = $this->get('/items');
        $resPage->assertStatus(200);

        // Ambil kode & barcode unik dari service
        $nextCode = \App\Services\CodeGeneratorService::nextItemCode();
        $nextBarcode = \App\Services\CodeGeneratorService::nextBarcodeValue();

        // Pastikan kode yang di-generate TIDAK bentrok dengan yang ada di trash
        $this->assertFalse(Item::withTrashed()->where('item_code', $nextCode)->exists());
        $this->assertFalse(Item::withTrashed()->where('barcode', $nextBarcode)->exists());

        // Tambah barang baru dengan kode otomatis tersebut
        $createRes = $this->post('/items', [
            'item_code' => $nextCode,
            'barcode' => $nextBarcode,
            'name' => 'Kamera Drone Taktis Baru',
            'item_type' => 'asset',
            'unit' => 'Unit',
            'stock' => 3,
            'min_stock' => 1,
            'storage_location' => 'Lemari Khusus',
            'condition_status' => 'Baik',
            'availability_status' => 'sedia',
        ]);

        $createRes->assertRedirect(route('items.index'));
        $createRes->assertSessionHasNoErrors();

        // Pastikan barang baru tersimpan di database
        $this->assertDatabaseHas('items', [
            'item_code' => $nextCode,
            'barcode' => $nextBarcode,
            'name' => 'Kamera Drone Taktis Baru',
            'deleted_at' => null,
        ]);
    }

    public function test_pwa_manifest_and_service_worker_exist(): void
    {
        $this->get('/manifest.json')
            ->assertStatus(200)
            ->assertJsonFragment(['short_name' => 'LOGISTIK.TOC']);

        $this->assertFileExists(public_path('sw.js'));
    }

    public function test_admin_can_download_database_backup_and_staff_is_denied(): void
    {
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);

        $res = $this->get(route('settings.backup-db'));
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/sql; charset=utf-8');

        // Staff non-admin should get 403
        $staff = User::where('role', 'staff')->first();
        if ($staff) {
            $this->actingAs($staff);
            $this->get(route('settings.backup-db'))->assertStatus(403);
        }
    }

    public function test_item_stock_card_renders_with_movements(): void
    {
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);

        $item = Item::withTrashed()->firstOrFail();
        if ($item->trashed()) {
            $item->restore();
        }

        $res = $this->get(route('items.stock-card', $item));
        $res->assertStatus(200);
        $res->assertSee('KARTU KENDALI PERPUTARAN STOK BARANG');
        $res->assertSee($item->barcode);
    }

    public function test_overdue_bast_calculation_and_dashboard_alert(): void
    {
        $admin = User::where('email', 'admin@logiscan.id')->firstOrFail();
        $this->actingAs($admin);

        $item = Item::withTrashed()->firstOrFail();
        if ($item->trashed()) {
            $item->restore();
        }

        // Buat BAST yang sengaja dibuat lewat tanggal jatuh tempo
        $overdueDoc = HandoverDocument::create([
            'document_no' => 'BAST-TEST-OVERDUE-001',
            'document_type' => 'bast',
            'loan_type' => 'pinjam_pakai',
            'document_date' => now()->subDays(10)->toDateString(),
            'expected_return_date' => now()->subDays(3)->toDateString(), // Terlambat 3 hari
            'first_party_name' => 'Petugas Gudang',
            'second_party_name' => 'Peminjam Telat',
            'second_party_phone' => '081234567899',
            'user_id' => $admin->id,
            'status' => 'active',
        ]);

        $this->assertTrue($overdueDoc->is_overdue);
        $this->assertEquals(3, $overdueDoc->days_overdue);
        $this->assertNotNull($overdueDoc->whatsapp_reminder_url);
        $this->assertStringContainsString('6281234567899', $overdueDoc->whatsapp_reminder_url);

        // Pastikan muncul di Dashboard
        $resDash = $this->get(route('dashboard'));
        $resDash->assertStatus(200);
        $resDash->assertSee('Radar Pengingat:');
        $resDash->assertSee('Peminjam Telat');
    }

    public function test_role_hierarchy_and_user_deletion_exclusive_to_super_admin(): void
    {
        $superAdmin = User::where('username', 'admin')->firstOrFail();
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertFalse($superAdmin->isStaff());
        $this->assertTrue($superAdmin->canDeleteUser());

        // 1. Buat User Admin Biasa & Staff untuk pengujian
        $adminBiasa = User::firstOrCreate(
            ['username' => 'test.admin.biasa'],
            [
                'name' => 'Admin Biasa Test',
                'email' => 'adminbiasa@logiscan.id',
                'role' => 'admin',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );
        $this->assertFalse($adminBiasa->isSuperAdmin());
        $this->assertTrue($adminBiasa->isAdmin());
        $this->assertFalse($adminBiasa->canDeleteUser());

        $staffUser = User::firstOrCreate(
            ['username' => 'test.staff.target'],
            [
                'name' => 'Staff Target Hapus',
                'email' => 'stafftarget@logiscan.id',
                'role' => 'staff',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        // 2. Uji: Admin Biasa DILARANG menghapus user (harus 403 Forbidden)
        $this->actingAs($adminBiasa);
        $resForbidden = $this->delete(route('profile.users.destroy', $staffUser));
        $resForbidden->assertStatus(403);
        $this->assertFalse($staffUser->fresh()->trashed());

        // 3. Uji: Admin Biasa DILARANG membuat akun ber-role super_admin
        $resCreateSuper = $this->post(route('profile.users.store'), [
            'name' => 'Fake Super Admin',
            'username' => 'fake.super',
            'email' => 'fakesuper@logiscan.id',
            'password' => 'password123',
            'role' => 'super_admin',
        ]);
        $resCreateSuper->assertSessionHasErrors('role');

        // 4. Uji: Super Admin DILARANG menghapus akunnya sendiri
        $this->actingAs($superAdmin);
        $resSelfDel = $this->delete(route('profile.users.destroy', $superAdmin));
        $resSelfDel->assertSessionHasErrors('user');
        $this->assertFalse($superAdmin->fresh()->trashed());

        // 5. Uji: Super Admin BERHASIL menghapus user target (Soft Delete)
        $resDelStaff = $this->delete(route('profile.users.destroy', $staffUser));
        $resDelStaff->assertRedirect();
        $this->assertTrue($staffUser->fresh()->trashed());

        // 6. Uji: Halaman Profil menampilkan badge dan tombol hapus dengan benar
        $resProfile = $this->get(route('profile.edit'));
        $resProfile->assertStatus(200);
        $resProfile->assertSee('👑 Super Admin');
        $resProfile->assertSee('Hapus Akun Pengguna (Hanya Super Admin)');

        // Bersihkan dummy users
        $adminBiasa->forceDelete();
        $staffUser->forceDelete();
    }

    public function test_login_rate_limiting_blocks_brute_force_attacks(): void
    {
        $loginTarget = 'admin@logiscan.id';

        // 5 kali percobaan login gagal dengan password salah
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->post('/login', [
                'login' => $loginTarget,
                'password' => 'wrongpassword' . $i,
            ]);
            $res->assertSessionHasErrors('login');
        }

        // Percobaan ke-6 harus otomatis DIBLOKIR sementara oleh sistem pertahanan Brute-Force
        $resBlocked = $this->post('/login', [
            'login' => $loginTarget,
            'password' => 'wrongpassword6',
        ]);
        $resBlocked->assertSessionHasErrors('login');
        $errorMsg = session('errors')->first('login');
        $this->assertStringContainsString('Terlalu banyak percobaan login gagal', $errorMsg);

        // Verifikasi tabel access_logs mencatat percobaan brute force yang diblokir
        $this->assertDatabaseHas('access_logs', [
            'user_email' => $loginTarget,
            'event_type' => 'brute_force_blocked',
        ]);
    }

    public function test_super_admin_can_update_user_job_title_and_role(): void
    {
        $superAdmin = User::where('role', 'super_admin')->firstOrFail();
        $targetUser = User::where('role', 'staff')->firstOrFail();

        $this->actingAs($superAdmin);

        $response = $this->put(route('profile.users.update', $targetUser), [
            'name' => $targetUser->name,
            'username' => $targetUser->username,
            'email' => $targetUser->email,
            'employee_id' => 'NRP-88091122',
            'job_title' => 'Kepala Seksi Logistik (Kasilog)',
            'department' => 'Detasemen Gegana Satbrimob',
            'role' => 'admin',
            'phone' => '0812-9988-7766',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertEquals('Kepala Seksi Logistik (Kasilog)', $targetUser->job_title);
        $this->assertEquals('Detasemen Gegana Satbrimob', $targetUser->department);
        $this->assertEquals('admin', $targetUser->role);
        $this->assertEquals('NRP-88091122', $targetUser->employee_id);

        // Verifikasi audit log
        $this->assertDatabaseHas('access_logs', [
            'user_id' => $superAdmin->id,
            'event_type' => 'activity',
        ]);
    }
}
