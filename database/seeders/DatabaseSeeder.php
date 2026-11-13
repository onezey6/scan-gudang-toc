<?php

namespace Database\Seeders;

use App\Models\AccessLog;
use App\Models\Category;
use App\Models\HandoverDocument;
use App\Models\HandoverItem;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'ilhamdhani'],
            [
                'name' => 'Ilham Dhani',
                'email' => 'ilhamdhani@logiscan.id',
                'employee_id' => 'NRP-TOC-001',
                'role' => 'admin',
                'job_title' => 'Operator Logistik TOC',
                'department' => 'Tactical Operation Centre (TOC) - Gegana',
                'phone' => '0812-0000-0001',
                'is_active' => true,
                'last_login_at' => now()->subMinutes(5),
                'password' => Hash::make('password123'),
            ]
        );

        if (User::where('username', 'admin')->exists()) {
            return;
        }

        // 1. Buat Akun Pengguna (Admin & Petugas Logistik)
        $admin = User::create([
            'name' => 'Budi Santoso',
            'username' => 'admin',
            'email' => 'admin@logiscan.id',
            'employee_id' => 'NIP-LOG-001',
            'role' => 'admin',
            'job_title' => 'Kepala Gudang & Logistik',
            'department' => 'Divisi Logistik & Inventaris',
            'phone' => '0812-3456-7890',
            'is_active' => true,
            'last_login_at' => now()->subMinutes(15),
            'password' => Hash::make('password123'),
        ]);

        $staffRina = User::create([
            'name' => 'Rina Wijaya',
            'username' => 'rina.staff',
            'email' => 'rina@logiscan.id',
            'employee_id' => 'NIP-LOG-002',
            'role' => 'staff',
            'job_title' => 'Petugas Gudang & Scan Barcode',
            'department' => 'Divisi Logistik & Inventaris',
            'phone' => '0813-8899-1122',
            'is_active' => true,
            'last_login_at' => now()->subHours(2),
            'password' => Hash::make('password123'),
        ]);

        $staffDedi = User::create([
            'name' => 'Dedi Kurniawan',
            'username' => 'dedi.staff',
            'email' => 'dedi@logiscan.id',
            'employee_id' => 'NIP-LOG-003',
            'role' => 'staff',
            'job_title' => 'Petugas Distribusi Logistik',
            'department' => 'Divisi Operasional Gudang',
            'phone' => '0812-9988-7766',
            'is_active' => true,
            'last_login_at' => now()->subHours(5),
            'password' => Hash::make('password123'),
        ]);

        // 2. Buat Kategori Barang
        $catElektronik = Category::create([
            'code' => 'KAT-ELK',
            'name' => 'Perangkat Elektronik & Komunikasi',
            'description' => 'HT, Laptop, Barcode Scanner, Tablet Operasional',
        ]);

        $catJaringan = Category::create([
            'code' => 'KAT-NET',
            'name' => 'Perangkat Jaringan & Kelistrikan',
            'description' => 'Kabel LAN, Router, Switch, Genset Portable',
        ]);

        $catK3 = Category::create([
            'code' => 'KAT-APD',
            'name' => 'Peralatan Kerja & Keselamatan (K3)',
            'description' => 'Helm Proyek, Rompi Safety, Sepatu Boot, Toolset',
        ]);

        $catATK = Category::create([
            'code' => 'KAT-OPS',
            'name' => 'Material Kemasan & Operasional Gudang',
            'description' => 'Lakban, Kertas Label Thermal, Wrapping Roll, Box',
        ]);

        // 3. Buat Master Barang dengan Barcode & Pencatatan User Pembuat (created_by)
        $ym = now()->format('Ym');
        $itemsData = [
            [
                'item_code' => "BRG-{$ym}-001",
                'barcode' => "899{$ym}0001",
                'name' => 'Handy Talky (HT) Motorola CP1660 VHF',
                'category_id' => $catElektronik->id,
                'item_type' => 'asset',
                'brand' => 'Motorola',
                'unit' => 'Unit',
                'stock' => 14,
                'min_stock' => 4,
                'storage_location' => 'Rak A-01 (Loker Aset)',
                'condition_status' => 'Baik',
                'description' => 'Termasuk antena, baterai Li-Ion, dan desktop charger.',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
            [
                'item_code' => "BRG-{$ym}-002",
                'barcode' => "899{$ym}0002",
                'name' => 'Wireless Barcode Scanner Honeywell 1472G 2D',
                'category_id' => $catElektronik->id,
                'item_type' => 'asset',
                'brand' => 'Honeywell',
                'unit' => 'Unit',
                'stock' => 8,
                'min_stock' => 2,
                'storage_location' => 'Rak A-02 (Loker Aset)',
                'condition_status' => 'Baik',
                'description' => 'Scanner 1D/2D Bluetooth & cradle USB untuk meja gudang.',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
            [
                'item_code' => "BRG-{$ym}-003",
                'barcode' => "899{$ym}0003",
                'name' => 'Laptop Operasional Lenovo ThinkPad L14 Gen 4',
                'category_id' => $catElektronik->id,
                'item_type' => 'asset',
                'brand' => 'Lenovo',
                'unit' => 'Unit',
                'stock' => 5,
                'min_stock' => 2,
                'storage_location' => 'Lemari Besi B-01',
                'condition_status' => 'Baik',
                'description' => 'Core i5, RAM 16GB, SSD 512GB untuk tim audit & lapangan.',
                'created_by' => $staffRina->id,
                'updated_by' => $staffRina->id,
            ],
            [
                'item_code' => "BRG-{$ym}-004",
                'barcode' => "899{$ym}0004",
                'name' => 'Kabel UTP Cat6 Belden Roll (305 Meter)',
                'category_id' => $catJaringan->id,
                'item_type' => 'consumable',
                'brand' => 'Belden',
                'unit' => 'Box',
                'stock' => 18,
                'min_stock' => 5,
                'storage_location' => 'Rak C-01 (Gudang Material)',
                'condition_status' => 'Baik',
                'description' => 'Kabel jaringan gigabit indoor abu-abu.',
                'created_by' => $staffRina->id,
                'updated_by' => $staffRina->id,
            ],
            [
                'item_code' => "BRG-{$ym}-005",
                'barcode' => "899{$ym}0005",
                'name' => 'Helm Safety Proyek MSA V-Gard Putih + Fastrac',
                'category_id' => $catK3->id,
                'item_type' => 'asset',
                'brand' => 'MSA',
                'unit' => 'Pcs',
                'stock' => 24,
                'min_stock' => 10,
                'storage_location' => 'Rak D-02 (Area K3)',
                'condition_status' => 'Baik',
                'description' => 'Helm pelindung standar SNI/ANSI untuk kunjungan gudang & lapangan.',
                'created_by' => $staffDedi->id,
                'updated_by' => $staffDedi->id,
            ],
            [
                'item_code' => "BRG-{$ym}-006",
                'barcode' => "899{$ym}0006",
                'name' => 'Kertas Label Stiker Barcode Thermal 50x30mm (1000 Pcs)',
                'category_id' => $catATK->id,
                'item_type' => 'consumable',
                'brand' => 'Zebra Label',
                'unit' => 'Roll',
                'stock' => 3,
                'min_stock' => 5,
                'storage_location' => 'Rak E-01 (Logistik Cetak)',
                'condition_status' => 'Baik',
                'description' => 'Stiker direct thermal tahan air untuk label barcode barang.',
                'created_by' => $staffDedi->id,
                'updated_by' => $staffDedi->id,
            ],
        ];

        $createdItems = [];
        foreach ($itemsData as $data) {
            $createdItems[] = Item::create($data);
        }

        $ymd = now()->format('Ymd');

        // 4. Buat Riwayat Transaksi Scan Barcode Masuk & Keluar (stock_movements)
        StockMovement::create([
            'reference_no' => "SCN-IN-{$ymd}-001",
            'item_id' => $createdItems[0]->id,
            'user_id' => $admin->id,
            'movement_type' => 'in',
            'quantity' => 16,
            'stock_before' => 0,
            'stock_after' => 16,
            'source_destination' => 'Penerimaan Pengadaan Vendor PT Mitra Komunikasi',
            'partner_name' => 'Hendra Wijaya (Kurir Vendor)',
            'scan_method' => 'scanner_device',
            'notes' => 'Barang masuk diterima lengkap dan sudah ditempel barcode.',
            'scanned_at' => now()->subHours(6),
        ]);

        StockMovement::create([
            'reference_no' => "SCN-IN-{$ymd}-002",
            'item_id' => $createdItems[3]->id,
            'user_id' => $staffRina->id,
            'movement_type' => 'in',
            'quantity' => 22,
            'stock_before' => 0,
            'stock_after' => 22,
            'source_destination' => 'Pengiriman Gudang Pusat Jakarta',
            'partner_name' => 'Wahyu Saputra',
            'scan_method' => 'camera',
            'notes' => 'Scan barang masuk via kamera HP oleh petugas Rina.',
            'scanned_at' => now()->subHours(4),
        ]);

        StockMovement::create([
            'reference_no' => "SCN-OUT-{$ymd}-001",
            'item_id' => $createdItems[3]->id,
            'user_id' => $staffDedi->id,
            'movement_type' => 'out',
            'quantity' => 4,
            'stock_before' => 22,
            'stock_after' => 18,
            'source_destination' => 'Instalasi Jaringan Gedung Gudang Blok B',
            'partner_name' => 'Fajar Nugroho (Teknisi IT)',
            'scan_method' => 'scanner_device',
            'notes' => 'Pengeluaran material kabel LAN melalui scan barcode.',
            'scanned_at' => now()->subHours(3),
        ]);

        // 5. Buat Contoh Dokumen Berita Acara Serah Terima (BAST) & Pengembalian (BAP)
        $bast1 = HandoverDocument::create([
            'document_no' => "BAST-{$ym}-001",
            'document_type' => 'bast',
            'document_date' => now()->toDateString(),
            'user_id' => $staffRina->id,
            'first_party_name' => $staffRina->name,
            'first_party_nip' => $staffRina->employee_id,
            'first_party_title' => $staffRina->job_title,
            'first_party_unit' => $staffRina->department,
            'second_party_name' => 'Andi Pratama, S.T.',
            'second_party_nip' => 'NIP-OPS-104',
            'second_party_title' => 'Koordinator Tim Lapangan',
            'second_party_unit' => 'Divisi Operasional & Distribusi',
            'second_party_phone' => '0811-2233-4455',
            'status' => 'active',
            'sync_stock' => true,
            'notes' => 'Serah terima perangkat komunikasi HT dan Helm Safety untuk kegiatan operasional distribusi.',
        ]);

        HandoverItem::create([
            'handover_document_id' => $bast1->id,
            'item_id' => $createdItems[0]->id,
            'quantity' => 2,
            'condition_status' => 'Baik',
            'notes' => 'Unit HT #01 & #02 lengkap dengan baterai cadangan',
        ]);

        HandoverItem::create([
            'handover_document_id' => $bast1->id,
            'item_id' => $createdItems[4]->id,
            'quantity' => 2,
            'condition_status' => 'Baik',
            'notes' => 'Helm Safety Putih ukuran standar',
        ]);

        StockMovement::create([
            'reference_no' => "SCN-OUT-{$ymd}-002",
            'item_id' => $createdItems[0]->id,
            'user_id' => $staffRina->id,
            'movement_type' => 'out',
            'quantity' => 2,
            'stock_before' => 16,
            'stock_after' => 14,
            'source_destination' => "Serah Terima BAST ({$bast1->document_no}) ke Andi Pratama, S.T.",
            'partner_name' => 'Andi Pratama, S.T.',
            'scan_method' => 'handover',
            'handover_document_id' => $bast1->id,
            'notes' => "Otomatis dari dokumen {$bast1->document_no}",
            'scanned_at' => now()->subHours(2),
        ]);

        // Contoh BAST ke-2 yang sudah dikembalikan melalui BAP
        $bast2 = HandoverDocument::create([
            'document_no' => "BAST-{$ym}-002",
            'document_type' => 'bast',
            'document_date' => now()->subDay()->toDateString(),
            'user_id' => $admin->id,
            'first_party_name' => $admin->name,
            'first_party_nip' => $admin->employee_id,
            'first_party_title' => $admin->job_title,
            'first_party_unit' => $admin->department,
            'second_party_name' => 'Siti Rahmawati',
            'second_party_nip' => 'NIP-AUD-012',
            'second_party_title' => 'Staff Audit Stok Opname',
            'second_party_unit' => 'Divisi Pengawasan Internal',
            'second_party_phone' => '0812-5566-7788',
            'status' => 'returned',
            'sync_stock' => true,
            'notes' => 'Peminjaman Laptop & Scanner untuk kegiatan Stock Opname Bulanan.',
        ]);

        HandoverItem::create([
            'handover_document_id' => $bast2->id,
            'item_id' => $createdItems[1]->id,
            'quantity' => 1,
            'condition_status' => 'Baik',
            'notes' => 'Scanner Honeywell + Dongle USB',
        ]);

        $bap1 = HandoverDocument::create([
            'document_no' => "BAP-{$ym}-001",
            'document_type' => 'bap',
            'related_bast_id' => $bast2->id,
            'document_date' => now()->toDateString(),
            'user_id' => $admin->id,
            'first_party_name' => 'Siti Rahmawati',
            'first_party_nip' => 'NIP-AUD-012',
            'first_party_title' => 'Staff Audit Stok Opname',
            'first_party_unit' => 'Divisi Pengawasan Internal',
            'second_party_name' => $admin->name,
            'second_party_nip' => $admin->employee_id,
            'second_party_title' => $admin->job_title,
            'second_party_unit' => $admin->department,
            'second_party_phone' => $admin->phone,
            'status' => 'completed',
            'sync_stock' => true,
            'notes' => 'Barang telah dikembalikan ke gudang logistik dalam kondisi baik dan berfungsi normal.',
        ]);

        HandoverItem::create([
            'handover_document_id' => $bap1->id,
            'item_id' => $createdItems[1]->id,
            'quantity' => 1,
            'condition_status' => 'Baik',
            'notes' => 'Dikembalikan lengkap beserta cradle charger',
        ]);

        // 6. Buat Riwayat History Akses Keluar-Masuk Web (access_logs)
        AccessLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'user_email' => $admin->email,
            'user_role' => 'admin',
            'event_type' => 'login',
            'ip_address' => '127.0.0.1',
            'device_browser' => 'Google Chrome • Windows',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0',
            'description' => 'Berhasil masuk (Login) ke dalam web aplikasi sebagai ADMIN',
            'accessed_at' => now()->subHours(6),
        ]);

        AccessLog::create([
            'user_id' => $staffRina->id,
            'user_name' => $staffRina->name,
            'user_email' => $staffRina->email,
            'user_role' => 'staff',
            'event_type' => 'login',
            'ip_address' => '192.168.1.24',
            'device_browser' => 'Google Chrome • Android Mobile',
            'user_agent' => 'Mozilla/5.0 (Linux; Android 14) Chrome/130.0 Mobile',
            'description' => 'Berhasil masuk (Login) ke dalam web aplikasi sebagai STAFF',
            'accessed_at' => now()->subHours(4),
        ]);

        AccessLog::create([
            'user_id' => $staffRina->id,
            'user_name' => $staffRina->name,
            'user_email' => $staffRina->email,
            'user_role' => 'staff',
            'event_type' => 'activity',
            'ip_address' => '192.168.1.24',
            'device_browser' => 'Google Chrome • Android Mobile',
            'user_agent' => 'Mozilla/5.0 (Linux; Android 14) Chrome/130.0 Mobile',
            'description' => "Scan Barcode BARANG MASUK (SCN-IN-{$ymd}-002): Kabel UTP Cat6 Belden Roll sebanyak 22 Box",
            'accessed_at' => now()->subHours(4)->addMinutes(10),
        ]);

        AccessLog::create([
            'user_id' => $staffDedi->id,
            'user_name' => $staffDedi->name,
            'user_email' => $staffDedi->email,
            'user_role' => 'staff',
            'event_type' => 'login',
            'ip_address' => '192.168.1.35',
            'device_browser' => 'Microsoft Edge • Windows',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edg/130.0',
            'description' => 'Berhasil masuk (Login) ke dalam web aplikasi sebagai STAFF',
            'accessed_at' => now()->subHours(3)->addMinutes(5),
        ]);

        AccessLog::create([
            'user_id' => $staffDedi->id,
            'user_name' => $staffDedi->name,
            'user_email' => $staffDedi->email,
            'user_role' => 'staff',
            'event_type' => 'logout',
            'ip_address' => '192.168.1.35',
            'device_browser' => 'Microsoft Edge • Windows',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edg/130.0',
            'description' => 'Keluar (Logout) dari sesi web aplikasi',
            'accessed_at' => now()->subHours(1),
        ]);
    }
}
