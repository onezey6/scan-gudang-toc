# PRD (Product Requirements Document): LogiScan Pro — Sistem Manajemen Inventaris, Scan Barcode & Berita Acara Logistik (Laravel 12)

================================================================================
ZONA ATAS: SPESIFIKASI KEBUTUHAN PRODUK (BLUEPRINT AWAL)
================================================================================

## 1. Nama Projek & Problem Statement
- **Nama Aplikasi:** **LogiScan Pro** (Sistem Informasi Scan Barcode, Gudang Penyimpanan & Berita Acara Logistik) — *Nama aplikasi & instansi dapat disesuaikan melalui halaman Pengaturan*.
- **Masalah yang Diselesaikan (Problem Statement):**
  1. Pencatatan barang masuk dan keluar gudang secara manual sering memicu selisih stok dan keterlambatan pelaporan.
  2. Sulit melacak akuntabilitas petugas (siapa user yang pertama kali mendaftarkan barang ke sistem, serta siapa yang melakukan scan barang masuk dan barang keluar).
  3. Pembuatan kode barcode untuk barang baru yang belum memiliki label masih dilakukan terpisah dari sistem inventaris.
  4. Belum adanya rekam jejak keamanan mengenai riwayat akses (siapa saja yang login, logout, dan mengakses web beserta waktu serta perangkatnya).
  5. Proses pembuatan **Berita Acara Serah Terima (BAST)** dan **Berita Acara Pengembalian (BAP)** masih diketik manual di Word/Excel sehingga rawan tidak sinkron dengan stok gudang.

---

## 2. Peran Pengguna (Role & Hak Akses)
Sistem mendukung **2 peran pengguna** dengan pembagian hak akses yang jelas:

| Peran (Role) | Kode Role | Hak Akses & Tanggung Jawab Utama |
| :--- | :--- | :--- |
| **Admin Logistik** | `admin` | Akses penuh ke seluruh modul: Dashboard Analitik, Master Barang & Studio Pembuat Barcode, Scan Barcode Masuk/Keluar, Berita Acara (BAST & BAP), Laporan & Export (PDF/Excel), Manajemen Akun User/Petugas, History Akses Web (Audit Log Login/Logout), dan Pengaturan Profil. |
| **Petugas / Staff** | `staff` | Akses operasional harian: Dashboard, Tambah/Lihat Barang (tercatat otomatis sebagai pembuat barang), Studio Cetak Barcode, Scan Barcode Masuk/Keluar (tercatat otomatis sebagai petugas scan), Buat & Cetak Berita Acara (BAST & BAP), Lihat Laporan Transaksi, dan Pengaturan Profil Pribadi. |

---

## 3. Arsitektur & Lingkungan Kerja
- **Framework Backend:** **Laravel 12** (PHP 8.2+) di lingkungan XAMPP (`c:\xampp\htdocs\web scan logistik`).
- **Arsitektur Sistem:** **Modular MVC Laravel** (Controllers, Eloquent Models, Database Migrations, Seeders, Middleware Audit Akses, dan Blade Views terstruktur).
- **Database:** MySQL / MariaDB (XAMPP) / SQLite dengan dukungan Database Transaction (`DB::transaction` + `lockForUpdate`) untuk menjamin akurasi stok dan kode dokumen bebas tabrakan.
- **Desain Visual & UI/UX:**
  - **Gaya Visual:** **Desain Anti-AI (Human-Crafted Clean & Modern)** — Palet *Slate / Deep Navy* (`#0F172A`, `#1E293B`) dipadukan dengan aksen *Emerald* (`#059669`) untuk transaksi masuk, *Amber/Rose* untuk transaksi keluar/peringatan, latar belakang bersih (`#F8FAFC`), bayangan lembut, dan ikon vektor **SVG inline** tajam tanpa emoji berlebihan.
  - **Target Perangkat:** **Responsif Keduanya (HP & PC)** — Navigasi Sidebar proporsional di layar PC/Laptop, serta Bottom/Top Navigation + Hamburger Drawer di layar HP agar nyaman dibawa keliling gudang saat scan barcode.
- **Teknologi Pendukung Terintegrasi:**
  - **Generator Barcode:** Pembuatan Barcode 1D (`Code-128`) & QR Code secara instan yang dapat langsung dicetak sebagai label/stiker tempel.
  - **Dual-Mode Barcode Scanner:**
    1. **Kamera HP / Webcam Laptop** langsung dari browser (`html5-qrcode`).
    2. **Alat Scanner Fisik (USB / Bluetooth HID)** dengan pendeteksi input otomatis cepat + opsi ketik kode manual.
  - **Digital Signature Pad:** Kanvas tanda tangan langsung di layar sentuh HP atau mouse PC untuk dokumen BAST & BAP.

---

## 4. Alur Kerja Aplikasi (End-to-End User Flow)

### A. Alur Login & History Akses Keluar-Masuk Web (Fitur #4 & #7)
1. Pengguna membuka aplikasi dan diarahkan ke **Halaman Login** yang modern.
2. Saat pengguna melakukan **Login**, **Logout**, atau **Gagal Login**, sistem secara otomatis merekam ke tabel `access_logs` (Nama User, Role, Jenis Aktivitas, Alamat IP, Jenis Perangkat/Browser, dan Waktu Kejadian).
3. Di menu **History Akses Web**, pengguna/admin dapat memantau daftar riwayat keluar-masuk web lengkap dengan filter tanggal, jenis aktivitas (Login / Logout / Aktivitas Penting), dan pencarian nama user.

### B. Alur Tambah Barang & Mode Pembuat Barcode (Fitur #1 & #6)
1. Petugas/Admin membuka menu **Data Barang & Pembuat Barcode**.
2. Saat klik **Tambah Barang Baru**:
   - Sistem otomatis menyiapkan **Kode Barang** & **Kode Barcode** sekuensial (`BRG-YYYYMM-XXX`) atau petugas dapat mengklik tombol **"Generate Barcode Unik"** / memindai barcode pabrik yang sudah ada.
   - Memilih jenis pengelolaan barang:
     * **Barang Habis Pakai (Stok Massal):** Contoh ATK, kabel, material logistik.
     * **Barang Aset / Inventaris:** Contoh Laptop, HT, peralatan kerja yang bisa diserahterimakan dan dikembalikan.
   - Mengisi Nama Barang, Kategori, Satuan, Stok Awal, Batas Minimum Stok, dan Lokasi Rak/Penyimpanan.
   - Saat disimpan, sistem otomatis mengunci kolom **`created_by` (User Pembuat Barang)** sehingga di tabel dan detail barang selalu terlihat jelas siapa petugas yang menambahkan barang tersebut.
3. **Mode Studio Buat & Cetak Barcode:**
   - Pengguna dapat memilih satu atau beberapa barang, mengatur jumlah salinan stiker barcode per barang, memilih ukuran label (Kecil / Sedang / Besar), meninjau tampilan barcode (lengkap dengan Nama Barang, Kode Barcode, dan Lokasi Rak), lalu menekan **Cetak Label Barcode**.

### C. Alur Scan Barcode Masuk & Keluar ke Database Penyimpanan (Fitur #2 & #6)
1. Petugas membuka menu **Scan Barcode (Masuk / Keluar)**.
2. Memilih mode: **Scan Barang Masuk (Inbound)** atau **Scan Barang Keluar (Outbound)**.
3. Petugas mengarahkan kamera HP/Webcam ke barcode barang atau menembakkan alat scanner fisik.
4. Begitu barcode terbaca:
   - Sistem otomatis mengambil informasi barang dari database (Nama, Stok Saat Ini, Lokasi Rak, Satuan) dan memunculkan indikator visual + suara *beep*.
   - Petugas memasukkan **Jumlah (Qty)**, **Sumber/Tujuan (atau Nama Penerima/Pemasok)**, serta **Keterangan**.
5. Saat ditekan **Simpan Hasil Scan**:
   - Sistem mencatat transaksi ke tabel `stock_movements` dengan nomor referensi otomatis (`SCN-IN-YYYYMMDD-XXX` atau `SCN-OUT-YYYYMMDD-XXX`).
   - Sistem memperbarui stok di tabel `items` secara otomatis (Stok bertambah untuk Masuk, berkurang untuk Keluar; menolak otomatis jika stok keluar melebihi sisa stok gudang).
   - Sistem menyimpan **`user_id` Petugas Scan** agar selalu tercatat siapa user yang memasukkan atau mengeluarkan barang tersebut.

### D. Alur Laporan Barang Masuk & Keluar via Scan Barcode (Fitur #3 & #6)
1. Pengguna membuka menu **Laporan Barang Masuk & Keluar**.
2. Memfilter laporan berdasarkan:
   - Rentang Tanggal (Hari Ini, Minggu Ini, Bulan Ini, atau Tanggal Kustom),
   - Jenis Transaksi (Semua, Barang Masuk, Barang Keluar),
   - Kategori / Barang Tertentu,
   - Petugas yang Melakukan Scan (`user_id`).
3. Menampilkan kartu ringkasan (Total Transaksi Masuk, Total Unit Masuk, Total Transaksi Keluar, Total Unit Keluar) dalam grid 2–4 kolom yang rapi.
4. Menyediakan tombol **Export Excel/CSV** dan **Cetak Laporan PDF Resmi** lengkap dengan kolom: *No. Referensi, Waktu Scan, Kode Barcode, Nama Barang, Jenis (Masuk/Keluar), Jumlah, Stok Awal -> Stok Akhir, Tujuan/Sumber, dan Nama Petugas Scan*.

### E. Alur Berita Acara Serah Terima (BAST) & Berita Acara Pengembalian (BAP) (Fitur #8)
1. Pengguna membuka menu **Berita Acara (BAST & BAP)**.
2. **Membuat Berita Acara Serah Terima (BAST):**
   - Nomor surat otomatis: `BAST-YYYYMM-XXX`.
   - **Pihak Pertama (Yang Menyerahkan):** Otomatis terisi dari profil petugas yang sedang login (Nama, Jabatan, Divisi) dan tetap dapat disesuaikan.
   - **Pihak Kedua (Yang Menerima):** Mengisi Nama Penerima, NIP/ID, Jabatan, Unit/Divisi/Instansi, dan No. HP.
   - **Daftar Barang yang Diserahkan:** Petugas dapat memilih barang atau langsung **Scan Barcode** untuk menambahkan baris barang beserta jumlah, kondisi barang saat diserahkan (`Baik`, `Baru`, `Catatan Khusus`), dan opsi apakah otomatis memotong stok gudang.
   - **Tanda Tangan Digital (Signature Pad):** Pihak Pertama dan Pihak Kedua dapat langsung membubuhkan tanda tangan di layar (atau dikosongkan untuk tanda tangan basah setelah dicetak).
3. **Membuat Berita Acara Pengembalian (BAP):**
   - Nomor surat otomatis: `BAP-YYYYMM-XXX`.
   - Dapat memilih referensi dari dokumen **BAST** sebelumnya (sehingga daftar barang & identitas pihak otomatis terisi) atau membuat dokumen pengembalian mandiri.
   - Mencatat kondisi barang saat dikembalikan (`Baik / Lengkap`, `Rusak Ringan`, `Rusak Berat / Hilang`) dan otomatis mengembalikan jumlah stok ke penyimpanan gudang jika dipilih.
4. **Cetak Dokumen BAST & BAP:**
   - Menghasilkan tampilan cetak / PDF surat Berita Acara resmi lengkap dengan Kop Instansi, Nomor Dokumen, Narasi Pembuka Hari/Tanggal, Tabel Rincian Barang Ber-barcode, Catatan Kondisi, Barcode Verifikasi Dokumen, dan Kolom Tanda Tangan Kedua Pihak.

### F. Alur Pengaturan Profil & Manajemen Pengguna (Fitur #5)
1. Pada menu **Pengaturan Profil**, pengguna dapat memperbarui Nama Lengkap, Email, Nomor HP, Jabatan, Unit Kerja, Foto Profil/Inisial, serta mengganti Password dengan validasi keamanan.
2. Khusus **Admin**, tersedia menu **Manajemen Pengguna** untuk menambah/mengedit akun petugas baru serta melihat rekap aktivitas per petugas.

---

## 5. Desain Basis Data (Skema Tabel Relational)

### Tabel 1: `users` (Pengguna & Petugas Logistik)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID unik pengguna | `1` |
| `name` | VARCHAR(150) | Nama lengkap petugas | `Budi Santoso` |
| `email` | VARCHAR(150) | Email login unik | `admin@logiscan.id` |
| `username` | VARCHAR(100) | Username login unik | `admin.gudang` |
| `password` | VARCHAR(255) | Password terenkripsi (Bcrypt) | `$2y$12$...` |
| `role` | VARCHAR(30) | Peran akses (`admin` / `staff`) | `admin` |
| `job_title` | VARCHAR(100) | Jabatan petugas (untuk BAST/BAP) | `Kepala Gudang Logistik` |
| `department` | VARCHAR(100) | Divisi / Unit kerja | `Divisi Logistik & Umum` |
| `phone` | VARCHAR(30) | Nomor telepon / WhatsApp | `081234567890` |
| `avatar` | VARCHAR(255) | Path foto profil (nullable) | `avatars/user-1.png` |
| `is_active` | BOOLEAN | Status aktif akun | `true` |
| `last_login_at` | TIMESTAMP | Waktu login terakhir | `2026-11-12 22:30:00` |

### Tabel 2: `categories` (Kategori Barang)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID kategori | `1` |
| `code` | VARCHAR(30) | Kode kategori | `KAT-ELK` |
| `name` | VARCHAR(100) | Nama kategori barang | `Perangkat Elektronik & IT` |
| `description` | TEXT | Deskripsi kategori | `Laptop, HT, Scanner, Router` |

### Tabel 3: `items` (Master Barang & Barcode Penyimpanan)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID barang | `101` |
| `item_code` | VARCHAR(50) | Kode unik barang sekuensial | `BRG-202611-001` |
| `barcode` | VARCHAR(100) | Nilai angka/kode Barcode unik | `8992026110015` |
| `name` | VARCHAR(200) | Nama lengkap barang | `Handy Talky (HT) Motorola CP1660` |
| `category_id` | BIGINT FK | Relasi ke `categories.id` | `1` |
| `item_type` | VARCHAR(30) | `consumable` (Stok) / `asset` (Inventaris) | `asset` |
| `brand` | VARCHAR(100) | Merek / Tipe spesifikasi | `Motorola` |
| `unit` | VARCHAR(30) | Satuan hitung (`Unit`, `Pcs`, `Box`, `Roll`) | `Unit` |
| `stock` | INTEGER | Jumlah stok tersedia di penyimpanan | `15` |
| `min_stock` | INTEGER | Batas peringatan stok menipis | `3` |
| `storage_location` | VARCHAR(100) | Lokasi rak/gudang penyimpanan | `Rak A-02 / Gudang Utama` |
| `condition_status` | VARCHAR(50) | Kondisi (`Baik`, `Rusak Ringan`, `Perlu Cek`) | `Baik` |
| `description` | TEXT | Catatan spesifikasi barang | `Lengkap dengan charger dock` |
| `created_by` | BIGINT FK | **User yang menambahkan barang (`users.id`)** | `1` |
| `updated_by` | BIGINT FK | User terakhir yang memperbarui | `1` |
| `created_at` | TIMESTAMP | Waktu barang didaftarkan | `2026-11-12 08:15:00` |

### Tabel 4: `stock_movements` (Hasil Scan Barcode Barang Masuk & Keluar)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID transaksi scan | `501` |
| `reference_no` | VARCHAR(50) | Nomor transaksi otomatis | `SCN-OUT-20261112-001` |
| `item_id` | BIGINT FK | Relasi ke `items.id` | `101` |
| `user_id` | BIGINT FK | **User petugas yang melakukan scan (`users.id`)** | `2` |
| `movement_type` | VARCHAR(20) | Jenis mutasi (`in` = Masuk, `out` = Keluar) | `out` |
| `quantity` | INTEGER | Jumlah barang yang di-scan | `2` |
| `stock_before` | INTEGER | Sisa stok sebelum scan | `15` |
| `stock_after` | INTEGER | Sisa stok sesudah scan | `13` |
| `source_destination` | VARCHAR(200) | Asal pemasok (Masuk) / Tujuan unit (Keluar) | `Tim Operasional Lapangan` |
| `partner_name` | VARCHAR(150) | Nama pengantar / penerima barang | `Andi Pratama` |
| `scan_method` | VARCHAR(30) | Metode (`camera`, `scanner_device`, `manual`) | `camera` |
| `handover_document_id`| BIGINT FK | Relasi opsional ke Berita Acara (`nullable`) | `12` |
| `notes` | TEXT | Keterangan transaksi | `Pengeluaran untuk tugas proyek` |
| `scanned_at` | TIMESTAMP | Waktu presisi saat barcode di-scan | `2026-11-12 09:30:00` |

### Tabel 5: `handover_documents` (Berita Acara Serah Terima & Pengembalian)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID dokumen berita acara | `12` |
| `document_no` | VARCHAR(50) | Nomor surat otomatis (`BAST-...` / `BAP-...`) | `BAST-202611-001` |
| `document_type` | VARCHAR(20) | `bast` (Serah Terima) / `bap` (Pengembalian) | `bast` |
| `related_bast_id` | BIGINT FK | Referensi BAST asal jika dokumen adalah BAP | `null` |
| `document_date` | DATE | Tanggal Berita Acara | `2026-11-12` |
| `user_id` | BIGINT FK | User pembuat / Pihak Pertama (`users.id`) | `2` |
| `first_party_name` | VARCHAR(150) | Nama Pihak Pertama (Penyerah / Penerima Kembali) | `Rina Wijaya` |
| `first_party_nip` | VARCHAR(50) | NIP / ID Karyawan Pihak Pertama | `LOG-2024-008` |
| `first_party_title` | VARCHAR(100) | Jabatan Pihak Pertama | `Petugas Gudang Logistik` |
| `first_party_unit` | VARCHAR(100) | Divisi/Instansi Pihak Pertama | `Divisi Logistik` |
| `second_party_name` | VARCHAR(150) | Nama Pihak Kedua (Penerima / Pengembali) | `Andi Pratama` |
| `second_party_nip` | VARCHAR(50) | NIP / ID Karyawan Pihak Kedua | `OPS-2023-019` |
| `second_party_title` | VARCHAR(100) | Jabatan Pihak Kedua | `Koordinator Lapangan` |
| `second_party_unit` | VARCHAR(100) | Divisi/Instansi Pihak Kedua | `Divisi Operasional` |
| `second_party_phone` | VARCHAR(30) | Nomor HP Pihak Kedua | `081398765432` |
| `status` | VARCHAR(30) | `active` (Dipinjam/Diserahkan), `returned` (Sudah Kembali), `completed` | `active` |
| `first_party_signature` | LONGTEXT | Data Base64 tanda tangan Pihak Pertama | `data:image/png;base64,...` |
| `second_party_signature`| LONGTEXT | Data Base64 tanda tangan Pihak Kedua | `data:image/png;base64,...` |
| `notes` | TEXT | Catatan / klausul tambahan berita acara | `Barang diserahkan dalam keadaan baik` |

### Tabel 6: `handover_items` (Rincian Barang pada Berita Acara)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID baris item berita acara | `1` |
| `handover_document_id`| BIGINT FK | Relasi ke `handover_documents.id` | `12` |
| `item_id` | BIGINT FK | Relasi ke `items.id` | `101` |
| `quantity` | INTEGER | Jumlah unit yang diserahterimakan/dikembalikan | `2` |
| `condition_status` | VARCHAR(50) | Kondisi (`Baik`, `Rusak Ringan`, `Rusak Berat`) | `Baik` |
| `notes` | VARCHAR(255) | Catatan spesifik per barang (misal: No. Seri) | `SN: MTR-001 & MTR-002` |

### Tabel 7: `access_logs` (History Akses Keluar-Masuk Web & Audit Log)
| Nama Kolom | Tipe Data | Keterangan | Contoh Isi Data |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | ID riwayat akses | `1001` |
| `user_id` | BIGINT FK | Relasi ke `users.id` (nullable jika gagal login) | `1` |
| `user_name` | VARCHAR(150) | Snapshot nama user saat kejadian | `Budi Santoso` |
| `user_email` | VARCHAR(150) | Email/username yang digunakan | `admin@logiscan.id` |
| `user_role` | VARCHAR(30) | Role saat akses | `admin` |
| `event_type` | VARCHAR(40) | `login` (Masuk Web), `logout` (Keluar Web), `failed_login`, `activity` | `login` |
| `ip_address` | VARCHAR(45) | Alamat IP pengakses | `127.0.0.1` |
| `device_browser` | VARCHAR(150) | Ringkasan Browser & OS pengakses | `Chrome 130 / Windows 10` |
| `user_agent` | TEXT | String User-Agent lengkap | `Mozilla/5.0 ...` |
| `description` | VARCHAR(255) | Deskripsi aktivitas | `Berhasil login ke dalam sistem web` |
| `accessed_at` | TIMESTAMP | Waktu presisi kejadian | `2026-11-12 22:30:00` |

---

## 6. Aturan Validasi & Logika Khusus (Engineering Standards)
1. **Generator Nomor Unik Sekuensial Anti-Tabrakan (`MAX + 1` dengan DB Transaction Lock):**
   - Kode Barang: `BRG-YYYYMM-XXX` (contoh: `BRG-202611-001`) + Kode Barcode numerik standar 13 digit (`899` + `YYYYMM` + `XXXX`) atau kode kustom.
   - Kode Transaksi Scan Masuk: `SCN-IN-YYYYMMDD-XXX`.
   - Kode Transaksi Scan Keluar: `SCN-OUT-YYYYMMDD-XXX`.
   - Nomor Berita Acara Serah Terima: `BAST-YYYYMM-XXX`.
   - Nomor Berita Acara Pengembalian: `BAP-YYYYMM-XXX`.
2. **Proteksi Integritas Stok:**
   - Saat melakukan Scan Keluar (`out`) atau membuat BAST yang memotong stok, backend mengecek ketersediaan stok di dalam transaksi database (`lockForUpdate()`). Jika `quantity > stock`, transaksi ditolak dengan pesan peringatan yang jelas.
3. **Pelacakan Akuntabilitas User 100% Otomatis:**
   - Setiap penambahan barang wajib mengikat `Auth::id()` ke kolom `created_by`.
   - Setiap scan barang masuk/keluar wajib mengikat `Auth::id()` ke kolom `user_id` di `stock_movements`.
   - Setiap aksi Login dan Logout wajib memicu pencatatan otomatis ke `access_logs`.
4. **Search, Filter & Pagination Murni di Backend:**
   - Seluruh tabel (Master Barang, Riwayat Scan, Laporan Masuk/Keluar, Berita Acara, dan History Akses Web) diproses dengan pencarian dan filter di sisi server (Eloquent Query + Pagination) agar tetap ringan saat data mencapai ribuan baris.

---

## 7. Rencana Antarmuka (Daftar Halaman & Komponen)
1. **Halaman Login (`/login`):** Form login elegan dengan informasi akun demo siap pakai untuk pengujian cepat, indikator loading, dan proteksi keamanan.
2. **Dashboard Utama (`/dashboard`):**
   - Kartu Metrik Grid 2–4 kolom (Total Jenis Barang & Total Stok, Barang Masuk Hari Ini, Barang Keluar Hari Ini, BAST Aktif Belum Kembali, Peringatan Stok Menipis).
   - Tombol Aksi Cepat: *Scan Barcode Sekarang*, *Buat Barcode Barang Baru*, *Buat Berita Acara*.
   - Tabel Aktivitas Scan Terakhir & History Login Terkini.
3. **Master Barang & Mode Pembuat Barcode (`/items` & `/items/barcode-studio`):**
   - Daftar barang lengkap dengan gambar barcode langsung di tabel, informasi **Ditambahkan Oleh (Nama User)**, sisa stok, lokasi rak, dan filter.
   - Modal/Form Tambah & Edit Barang dengan tombol *Auto-Generate Barcode*.
   - **Studio Cetak Barcode**: Pilih banyak barang, atur jumlah stiker, pratinjau label barcode, dan cetak langsung.
4. **Terminal Scan Barcode Masuk & Keluar (`/scan`):**
   - Pemilih Mode (Tab **BARANG MASUK** berwarna Emerald vs **BARANG KELUAR** berwarna Amber/Rose).
   - Area Kamera Live Scanner (`html5-qrcode`) + Input Otomatis Alat Scanner USB/Bluetooth + Pencarian Cepat.
   - Form konfirmasi cepat (Jumlah, Sumber/Tujuan, Penerima/Pengirim, Catatan) & Tabel Riwayat Scan Hari Ini di bawahnya.
5. **Laporan Barang Masuk & Keluar (`/reports`):**
   - Filter tanggal, jenis mutasi (Masuk/Keluar), barang, dan petugas scan.
   - Tombol **Cetak PDF Laporan** & **Export Excel/CSV**.
6. **Berita Acara Serah Terima & Pengembalian (`/handovers`):**
   - Daftar dokumen BAST & BAP dengan status (`Diserahkan / Aktif`, `Sudah Dikembalikan`).
   - Form Pembuatan **BAST** & **BAP** (bisa tarik data otomatis dari BAST ke BAP, mendukung scan barcode untuk tambah baris barang, dan **Signature Pad** tanda tangan digital kedua pihak).
   - Halaman **Cetak Surat Resmi BAST / BAP** siap print/save PDF.
7. **History Akses Keluar-Masuk Web (`/access-logs`):**
   - Tabel audit waktu login, logout, gagal login, dan aktivitas penting beserta IP Address, Browser/Perangkat, Role, dan Nama User.
8. **Pengaturan Profil (`/profile`) & Manajemen User (`/users`):**
   - Ubah data diri, jabatan (otomatis terpakai di Berita Acara), foto/avatar, dan ubah password.
   - Kelola akun Admin & Petugas (khusus Admin).

---

## 8. Kriteria Pengujian (Verification Checklist)
- [ ] Pengguna dapat login dan logout, serta riwayat waktu masuk & keluar web langsung tercatat di menu **History Akses Web**.
- [ ] Saat membuat barang baru, barcode otomatis terbuat, tampil sebagai gambar grafik barcode yang bisa di-scan, dapat dicetak di **Studio Barcode**, dan mencatat nama user pembuatnya.
- [ ] Saat melakukan scan barcode (baik memilih Barang Masuk maupun Barang Keluar), transaksi tersimpan di database penyimpanan, stok barang otomatis bertambah/berkurang dengan benar, dan nama petugas scan tercatat.
- [ ] Menu **Laporan** dapat memfilter transaksi barang masuk & keluar berdasarkan tanggal/petugas serta dapat dicetak ke PDF atau diunduh ke Excel/CSV.
- [ ] Pengguna dapat membuat **Berita Acara Serah Terima (BAST)** dan **Berita Acara Pengembalian (BAP)** lengkap dengan tanda tangan digital dan mencetaknya dalam format surat resmi.
- [ ] Pengguna dapat memperbarui data diri dan password di menu **Pengaturan Profil**.

================================================================================
ZONA BAWAH: MASTER CONTEXT & LOGIKA SISTEM TERKINI (LIVING CONTEXT)
================================================================================

## 1. Status Arsitektur & Lingkungan Aktif Saat Ini
- **Status Projek:** **AKTIF & SIAP PAKAI (IMPLEMENTED & VERIFIED)** — Seluruh modul Laravel 12, database MySQL (`web_scan_logistik`), seeder akun/barang/transaksi, serta 35 pengujian otomatis telah lulus 100%.
- **Direktori Kerja:** `c:\xampp\htdocs\web scan logistik`
- **Stack Aktif:** Laravel 12 (`v12.72.0`), PHP 8.5 (XAMPP), MySQL (`web_scan_logistik`), Tailwind CSS, SVG Icons, `html5-qrcode`, `JsBarcode` (`CODE128`), HTML5 Canvas Signature Pad.

## 2. Skema Kolom Database Terkini yang Sedang Berjalan
- `users`, `categories`, `items` (dengan `photo_path` & `availability_status`), `stock_movements`, `handover_documents` (dengan kolom rincian Pinjam Pakai: `loan_type`, `item_status_target`, `purpose`, `reference_letter_no`, `operation_location`, `expected_return_date`, `terms_conditions`, `approver_name`, `approver_nip`, `approver_title`), `handover_items` (dengan `serial_number`, `accessories`, `availability_status`), `handover_photos` (galeri foto bukti BAST/BAP tanpa batas), `access_logs`.

## 3. Logika Bisnis & Formula Hitung yang Berlaku Saat Ini
- **Formula Mutasi Stok Scan Masuk (`in`):** `stock_after = stock_before + quantity`
- **Formula Mutasi Stok Scan Keluar (`out`):** `stock_after = stock_before - quantity` (Wajib `stock_before >= quantity`, dilindungi `DB::transaction` + `lockForUpdate()`)
- **Sinkronisasi Otomatis Status Ketersediaan Barang (`availability_status`):**
  - 4 Status resmi: `Sedia` (`sedia`), `Sedang Keluar` (`sedang_keluar`), `Sedang Dipakai` (`sedang_dipakai`), dan `Sedang Dipinjam` (`sedang_dipinjam`).
  - Saat BAST dibuat, status barang otomatis diperbarui sesuai status target BAST (`sedang_dipinjam` / `sedang_dipakai` / `sedang_keluar`).
  - Saat BAP (pengembalian) dibuat, status barang otomatis kembali menjadi `Sedia` (`sedia`).
  - Seluruh status tersinkronisasi secara real-time di **Dashboard**, **Master Barang**, dan **BAST & BAP**.
- **Integrasi BAST & BAP dengan Stok:**
  - Pembuatan BAST dapat sekaligus mencatat pengeluaran stok (`SCN-OUT-...`).
  - Pembuatan BAP atas BAST tersebut dapat sekaligus mengembalikan stok ke gudang (`SCN-IN-...`) dan mengubah status BAST menjadi `returned`.

## 4. Log Pembaruan Resmi
- **2026-11-12:** Penyusunan perdana `ZettBOT.md` dan dokumen spesifikasi `prd.md` berdasarkan wawancara kebutuhan 8 fitur utama sistem logistik barcode Laravel 12.
- **2026-11-12:** Implementasi penuh seluruh 8 fitur utama aplikasi **LogiScan Pro (Laravel 12)** beserta pengujian otomatis (`LogisticsFlowTest` — 5 passed, 35 assertions).
- **2026-11-12 (Revisi UI/UX Overhaul):** Peningkatan menyeluruh tampilan visual ke standar *Human-Crafted Enterprise UI* menggunakan tipografi **Plus Jakarta Sans** + **JetBrains Mono**, *Glassmorphism Header* dengan Jam Operasional Live (WIB), halaman Login *Split-Screen Showcase* dengan barcode interaktif & *Show/Hide Password*, grafik visual perbandingan Scan Masuk vs Keluar & distribusi stok per kategori di Dashboard, animasi garis laser scanner + tombol *quick stepper* (`+1`, `+5`, `+10`) di Terminal Scan, serta *Digital Logistics ID Card* pada halaman Profil.
- **2026-11-13 (Revisi Tema Cyber HUD):** Penerapan tema visual futuristik **Cyber HUD** dengan latar *Obsidian Grid* (`#03050A` / `#05070D`), aksen *Cyan Neon* (`#22D3EE`) & *Emerald Neon*, panel sudut potong (*corner-bracket HUD*), tombol pengalih tema instan (*Mode Cyber* &harr; *Mode Terang*), serta proteksi keterbacaan stiker barcode fisik dan dokumen cetak resmi BAST/BAP.
- **2026-11-13 (Revisi Halaman Login `LOGISTIK.TOC` & Logo Korps Brimob Polri TOC Gegana):** Desain ulang halaman Login (`/login`) mengikuti referensi visual kartu tunggal terpusat **`LOGISTIK.TOC` (`By xZeyyOne`)** lengkap dengan emblem bulat **KORPS BRIMOB POLRI — TACTICAL OPERATION CENTRE — GEGANA** (`public/images/logo-toc.jpg`) berbingkai cincin neon cyan dan indikator titik hijau aktif, kolom `IDENTITAS_USERNAME:` & `KATA_SANDI / PIN:` dengan ikon di sisi kanan, tombol gradasi cyan-biru **`BUKA_AKSES_SISTEM`**, footer resmi Korps Brimob Polri, serta penambahan akun operator `ilhamdhani` (`password123`).
- **2026-11-13 (Revisi 7 Fitur Operasional & UI iOS Bubble):**
  1. **Upload Foto Barang (`items.photo_path`) & Penampilan Multi-Modul:** Tambah/Edit foto barang beserta *live preview* dan *Lightbox Modal* pembesar foto di menu **Data Barang**, **Terminal Scan Barcode** (panel barang terdeteksi, klik cepat & riwayat scan), **Dashboard Analitik**, **Laporan Keluar/Masuk**, dan **Berita Acara (BAST & BAP)**.
  2. **Ukuran Barcode Tabel Data Barang Diperkecil:** Grafik barcode pada tabel Data Barang & Dashboard diubah ke ukuran *compact mini-badge* (`height: 18`, `width: 0.9`, `max-w-[108px]`).
  3. **Input Login Kosong:** Kolom `IDENTITAS_USERNAME:` dan `KATA_SANDI / PIN:` pada `/login` dikosongkan dari nilai default.
  4. **Efek Kedap-Kedip (`blink-xzeyy`):** Teks **`By xZeyyOne`** di bawah `LOGISTIK.TOC` pada halaman Login diberi efek animasi neon kedap-kedip.
  5. **Virtual Gelembung ala Sistem iOS (`#ios-cursor-bubble`):** Efek gelembung kaca melayang dan animasi membal (*spring cubic-bezier*) saat memilih atau menekan menu dan tombol.
  6. **Studio Cetak Barcode Tanpa Auto-Pilih:** Daftar barang di `/barcode-studio` tidak lagi tercentang otomatis saat dibuka (`0 Stiker`), sehingga murni petugas yang memilih barang mana yang akan di-print.
  7. **Fitur Sesi & Auto-Login / Auto-Redirect 15 Menit (`SESSION_LIFETIME=15`):** Sesi aktif 15 menit dengan penghitung waktu mundur live (`SESI: 15:00`) di header yang otomatis me-reset saat beraktivitas dan mengarahkan kembali ke halaman Login jika idle selama 15 menit.
- **2026-11-13 (Revisi 5 Fitur: Hapus Gelembung iOS, Upload Foto Bukti BAST/BAP Unlimited, 4 Status Barang Tersinkronisasi, & Format Pinjam Pakai Rinci):**
  1. **Penghapusan Virtual Gelembung iOS:** Menghapus elemen `#ios-cursor-bubble`, CSS efek gelembung, dan script pelacak kursor dari seluruh halaman utama dan halaman login agar interaksi klik kembali bersih dan responsif.
  2. **Fitur Upload Foto Bukti Dokumentasi Tanpa Batas (Unlimited) di BAST & BAP (`handover_photos`):** Mendukung multi-upload foto bukti tak terbatas beserta keterangan/caption saat membuat dokumen BAST/BAP, penambahan foto bukti susulan di halaman detail dokumen, tampilan *Lightbox*, serta lampiran cetak foto bukti di halaman Print/PDF.
  3. **4 Status Ketersediaan Barang (`Sedia`, `Sedang Keluar`, `Sedang Dipakai`, `Sedang Dipinjam`):** Ditambahkan pada tabel `items` (`availability_status`) lengkap dengan badge warna, filter pencarian, dan pilihan status pada form Tambah/Edit Barang.
  4. **Sinkronisasi Otomatis Status Barang di Dashboard & Menu BAST/BAP:** Panel 4 Kartu Status Ketersediaan Barang tampil sinkron secara real-time di **Dashboard** (`/dashboard`) dan **BAST & BAP** (`/handovers`), serta otomatis berubah saat BAST/BAP atau Scan Masuk/Keluar diterbitkan.
  5. **Format Pinjam Pakai (BAST & BAP) Lebih Rinci & Resmi:** Penambahan atribut Jenis Peruntukan (`Pinjam Pakai`, `Operasional Khusus`, `Inventaris Unit`, `Distribusi`), Keperluan/Nama Operasi, Nomor Surat Perintah (Sprin)/Referensi, Lokasi Penggunaan, Batas Waktu Pengembalian, Nomor Seri (S/N) & Kelengkapan Aksesoris per barang, 5 Pasal Ketentuan & Kewajiban Pinjam Pakai standar kedinasan, serta kolom Pejabat Mengetahui/Menyetujui (Atasan/Ka Unit) pada form dan cetakan PDF resmi.
- **2026-11-13 (Revisi Ikon Navigasi Menu Sesuai Fungsi Riil):**
  Pembaruan menyeluruh pada 8 ikon navigasi bilah samping (*app-sidebar*) dan laci navigasi ponsel (*mobile-drawer*) menjadi representasi visual SVG presisi tinggi (*high-fidelity functional icons*):
  1. `[01] Dashboard Analitik`: Ikon kuadran panel HUD analitik (menggantikan ikon rumah/home biasa).
  2. `[02] Scan Barcode (In/Out)`: Ikon sudut bidik laser scanner kamera (*laser viewfinder*) dengan pola barcode in/out.
  3. `[03] Data Barang & Gudang`: Ikon paket inventaris logistik & rak master barang berlakban dan berlabel.
  4. `[04] Studio Pembuat Barcode`: Ikon mesin pencetak label barcode (*label printer*) yang sedang mencetak stiker bergaris barcode siap pakai.
  5. `[05] Berita Acara (BAST & BAP)`: Ikon map dokumen dinas (*handover clipboard*) berklip dengan centang verifikasi dan pena tanda tangan.
  6. `[06] Laporan Keluar & Masuk`: Ikon grafik batang analitik mutasi arus keluar-masuk dengan panah tren.
  7. `[07] History Akses Web`: Ikon perisai keamanan (*security shield*) berpadu dengan jarum audit waktu login/logout.
  8. `[08] Setingan Profil & User`: Ikon identitas operator bertim (*user & team access control*).
  9. Penyertaan ikon dan label terstruktur pada laci navigasi ponsel (*Mobile Drawer*) yang sebelumnya belum berikon.
- **2026-11-13 (Revisi Pewarnaan Ikon Navigasi Menu - Vibrant Color-Coded Icons):**
  Penerapan palet warna berkarakter dan wadah mikro ikon berpendar (*glow container*) untuk setiap menu navigasi di Desktop Sidebar (`app-sidebar`), Mobile Drawer, dan Mobile Bottom Navbar:
  1. `[01] Dashboard Analitik`: **Cyan Neon** (`#22D3EE` / `bg-cyan-500/15`, `border-cyan-400/30`, `text-cyan-400`).
  2. `[02] Scan Barcode (In/Out)`: **Emerald Neon** (`#34D399` / `bg-emerald-500/15`, `border-emerald-400/30`, `text-emerald-400`).
  3. `[03] Data Barang & Gudang`: **Amber Gold** (`#FBBF24` / `bg-amber-500/15`, `border-amber-400/30`, `text-amber-400`).
  4. `[04] Studio Pembuat Barcode`: **Purple Violet** (`#A78BFA` / `bg-purple-500/15`, `border-purple-400/30`, `text-purple-400`).
  5. `[05] Berita Acara (BAST & BAP)`: **Sky Blue** (`#38BDF8` / `bg-sky-500/15`, `border-sky-400/30`, `text-sky-400`).
  6. `[06] Laporan Keluar & Masuk`: **Teal Mint** (`#2DD4BF` / `bg-teal-500/15`, `border-teal-400/30`, `text-teal-400`).
  7. `[07] History Akses Web`: **Rose Neon** (`#FB7185` / `bg-rose-500/15`, `border-rose-400/30`, `text-rose-400`).
  8. `[08] Setingan Profil & User`: **Fuchsia Pink** (`#E879F9` / `bg-fuchsia-500/15`, `border-fuchsia-400/30`, `text-fuchsia-400`).
  9. Efek interaktif *hover scale 110%* dengan pendar neon yang halus dan estetika Anti-AI berkelas tinggi.
- **2026-11-13 (Revisi Tata Letak Khusus Mobile HP vs Komputer PC — Adaptive Dual-View Experience):**
  Penyempurnaan arsitektur antarmuka adaptif dual-view untuk membedakan secara tegas pengalaman penggunaan di layar komputer (PC/Desktop) dan layar ponsel (HP/Smartphone):
  1. **Bilah Navigasi Bawah Khusus HP (Floating Mobile App Dock):** Dock bawah mengambang dengan latar kaca blur (*glassmorphism*), 4 tombol jempol berwarna, dan tombol tengah melingkar menonjol (*Floating Action Button / FAB*) untuk **SCAN BARCODE** berpendar neon emerald.
  2. **Header Mobile TOC Pintar:** Header mobile menampilkan emblem resmi bulat **TOC Gegana**, judul teks dinas, dan pill waktu mundur sesi aktif 15 menit (`#session-countdown-mobile`) yang sinkron dengan desktop.
  3. **Halaman Data Barang (`/items`) Dual Mode:** Tabel lebar multi-kolom tetap aktif untuk desktop (`hidden md:block`), sedangkan di ponsel (`md:hidden`) berganti otomatis menjadi **Kartu Cerdas Barang Mobile (Smart Mobile Cards)** dengan foto barang (dukungan Lightbox modal), badge status ketersediaan, SVG barcode, dan 3 tombol aksi cepat sentuh jempol (Scan, Stiker, Ubah).
  4. **Halaman Dashboard (`/dashboard`) Mobile Stream:** Tombol aksi hero adaptif 2x2 pada HP, dan riwayat mutasi scan serta registrasi barang terbaru berubah menjadi kartu alur mobile vertikal yang ringkas tanpa scroll horizontal.
  5. **Terminal Scan Barcode (`/scan`) Touch Stepper:** Tombol penyesuaian kuantiti cepat ramah jempol (`-1`, input, `+1`, `+5`, `+10`) dan riwayat scan mobile card stream.
  6. **Form Berita Acara BAST & BAP (`/handovers`):** Signature Pad digital dengan kontrol sentuh `touch-action: none` dan galeri upload multi-foto bukti dokumentasi adaptif.
- **2026-11-13 (Perbaikan Tata Letak Desktop PC — Normalisasi Tag Layout):**
  Mengeliminasi 8 baris duplikasi tag tombol drawer dan penutup header (`</div></header>`) pada `resources/views/layouts/app.blade.php`. Memulihkan hierarki pembungkus utama `<div class="flex-1 lg:pl-[272px] ...">` sehingga seluruh konten halaman (`<main>`) pada layar PC/Komputer kembali terbentang proporsional 100% dan tidak lagi tergencet di sisi kanan.
- **2026-11-13 (Fitur Auto-Start Kamera Pemindai pada Tombol SCAN):**
  Penerapan aktivasi kamera instan saat menekan tombol **SCAN** (Floating Action Button di navigasi bawah ponsel maupun tombol Scan di header):
  1. Tombol SCAN mengarahkan ke `/scan?camera=1` dengan pendeteksi otomatis pada `DOMContentLoaded` yang langsung menginisialisasi kamera belakang ponsel (`environment facingMode`).
  2. Handler in-page: Jika pengguna telah berada di halaman `/scan`, menekan tombol SCAN bawah langsung menyalakan/memfokuskan kamera tanpa me-reload halaman.
  3. Layar secara otomatis melakukan *smooth-scroll* ke jendela bidik kamera saat kamera aktif sehingga petugas gudang dapat langsung mengarahkan perangkat ke barcode fisik.
  4. Penyediaan penanganan error ramah (*graceful fallback*) dan polling kesiapan pustaka `Html5Qrcode`.
- **2026-11-13 (Penyempurnaan Navigasi Sidebar & Drawer - Clean Minimalist HUD):**
  Menghilangkan nomor indeks kurung siku `[01]`..`[08]` dan simbol garis miring ganda `//` pada seksi menu di Sidebar Desktop dan Mobile Drawer. Tampilan menjadi jauh lebih bersih, modern, dan memberikan ruang horizontal yang lebih lapang bagi label menu dan ikon berwarna.
- **2026-11-13 (Penyempurnaan Ketentuan Meminjam & Mengembalikan serta Upload Foto Bukti Tanpa Batas):**
  1. Pemisahan pasal kedinasan resmi antara **Ketentuan Meminjam (BAST - 6 Pasal)** dan **Ketentuan Pengembalian (BAP - 5 Pasal)** secara otomatis pada model `HandoverDocument`, form input, detail dokumen, dan cetakan fisik PDF/Print.
  2. Peningkatan sistem upload foto bukti dokumentasi fisik barang menjadi tanpa batasan kuantitas (*unlimited multi-photo upload*) dengan fitur multi-select langsung dari kamera HP/galeri, pratinjau thumbnail instan, penghitung jumlah foto terpilih (*total photos counter*), dan pemrosesan rekursif tangguh di `HandoverController`.
- **2026-11-13 (Perbaikan Tombol Buka Kamera & Viewfinder di HP Tanpa Kabel USB):**
  1. Memperbaiki fungsi `toggleCameraScanner()` dan `startCameraScanner()` pada `resources/views/scan/index.blade.php` agar saat tombol **Buka Kamera**, area kotak scanner, atau tombol **SCAN** bawah ditekan di HP (termasuk pada koneksi HTTP lokal `192.168.0.114:8000`), sistem langsung membuka **Kamera Belakang Bawaan HP** (`capture="environment"`) dalam 1 sentuhan tanpa gagal diam-diam.
  2. Mengganti tampilan kotak pemindai dari *"Sensor Alat Scanner USB Aktif"* menjadi **"Kamera Pemindai Barcode HP Siap — Sentuh untuk Buka Kamera Sekarang"** yang seluruh areanya dapat disentuh langsung.
  3. Menambahkan mesin pembaca barcode multi-engine (`BarcodeDetector` bawaan Chrome Android + `Html5Qrcode.scanFile` dengan kompresi gambar dan peningkat kontras otomatis) serta tombol pintas 1-klik ke **Mode Video Live (HTTPS)**.
- **2026-11-13 (Hotfix Parameter Route Hapus Foto Bukti Berita Acara):**
  Memperbaiki parameter route form tombol hapus foto bukti pada `resources/views/handovers/show.blade.php` baris 190 menjadi `route('handovers.photos.destroy', [$handover, $photo])`. Menyelesaikan kendala `UrlGenerationException` saat membuka halaman detail Berita Acara yang memiliki lampiran foto bukti.
- **2026-11-13 (Format Cetak Stiker Barcode Kertas Label Blueprint GL-22 & GL-06):**
  1. Penambahan pemilih format stiker di **Studio Pembuat Barcode** (`/items/barcode-studio`) yang mendukung ukuran standar kertas stiker Blueprint:
     - **BLUEPRINT GL-22 (64.5 x 47 mm):** 18 Label per lembar A4 (3 Kolom x 6 Baris), ukuran proporsional untuk stiker fisik peralatan & unit inventaris.
     - **BLUEPRINT GL-06 (96 x 93.1 mm):** 6 Label per lembar A4 (2 Kolom x 3 Baris), ukuran stiker besar kotak untuk dus kemasan/kontainer/rak gudang.
     - **Label Thermal Roll (50 x 30 mm):** Untuk cetak satuan pada printer barcode thermal roll.
     - **Kertas HVS A4 Fleksibel:** 3 kolom standar untuk cetak pada kertas HVS biasa lalu dipotong manual.
  2. Implementasi CSS `@media print` presisi milimeter (`@page`, `width: 193.5mm`, `grid-template-columns: repeat(3, 64.5mm)`, `grid-auto-rows: 47mm`) sehingga posisi cetak pas dengan pola pisau potong (*die-cut*) stiker Blueprint.
  3. Desain stiker bernuansa resmi kedinasan `LOGISTIK.TOC`, kode barang, nama barang tebal, barcode Code128 tajam pekat, nomor barcode, lokasi rak, dan badge `TOC GEGANA`.
  4. Penyertaan kalkulator lembar kertas otomatis (*sheets counter*), filter pencarian barang cepat, dan tombol otomatis paskan 1 lembar penuh A4 (*Quick Fill Sheet*).
- **2026-11-13 (Solusi Permanen `PostTooLargeException` HTTP 413 & Auto-Compress Foto HD di Berita Acara):**
  1. **Peningkatan Kapasitas Upload Server (`512 MB` & `500` File):** Menaikkan konfigurasi `post_max_size=512M`, `upload_max_filesize=512M`, dan `max_file_uploads=500` pada `C:\xampp\php\php.ini`, `public/.htaccess`, dan `public/.user.ini`, serta menaikkan batas validasi per file foto di `HandoverController` menjadi `50 MB` (`max:51200`).
  2. **Kompresi Otomatis Foto HD di Sisi Browser (Client-Side Smart HD Image Compression):** Mengimplementasikan `compressImageFile(file, 1600, 0.82)` berbasis HTML5 `<canvas>` dan `DataTransfer` pada `resources/views/handovers/create.blade.php` dan `resources/views/handovers/show.blade.php` sehingga foto kamera beresolusi besar (5–15 MB per foto) otomatis diringankan menjadi ~150–300 KB HD begitu dipilih, lengkap dengan indikator total ukuran MB yang siap diunggah.
- **2026-11-13 (Penerapan Background Cyberspace Grid Futuristik pada Halaman Login):**
  Memasang gambar pemandangan ruang siber digital (`public/images/bg-login.jpg`) sebagai latar belakang penuh halaman Login (`/login`) dengan mode *cover*, *fixed center-bottom*, perpaduan lapisan gelap transparan untuk kontras maksimal, serta efek kartu kaca semi-transparan (*glassmorphism*) yang menyatu secara estetik dengan pendar lantai grid neon biru.
- **2026-11-13 (Animasi Dinamis Background Bergerak, Partikel Melayang & Laser Grid di Halaman Login):**
  Mengimplementasikan efek latar belakang bergerak (*living dynamic cyberspace motion*):
  1. **Cinematic Cyber Float:** Animasi kamera melayang perlahan (*smooth ambient pan & zoom* 28s) pada layer gambar latar `cyber-bg-motion` dengan akselerasi perangkat keras 60 FPS.
  2. **Floating Cyber Stardust:** Mesin partikel canvas HTML5 ringan (~46 partikel bintik neon cyan dan sky blue) yang melayang ke atas dengan efek kedip (*twinkling pulses*) alami.
  3. **Holographic Grid Scan Beam:** Sapuan garis laser neon cyan yang bergerak lembut di atas permukaan lantai grid untuk mempertegas kedalaman 3D ruangan digital.
- **2026-11-13 (Implementasi 6 Fitur Baru: Hapus Berita Acara, Cetak PDF Multi-Halaman A4, Perbaikan Kartu Ketersediaan, Menu Sampah Recycle Bin, Lokasi Real-Time Admin Only, & Lupa Password):**
  1. **Fitur Hapus Berita Acara (Soft-Delete ke Sampah):** Penambahan aksi `Hapus` pada tabel daftar Berita Acara (`/handovers`) dan halaman detail (`/handovers/{id}`) yang memindahkan dokumen ke Kotak Sampah tanpa kehilangan data.
  2. **Format Cetak / Simpan PDF Berita Acara Rapi Multi-Halaman A4:** Standarisasi `@page { size: A4 portrait; margin: 14mm 15mm; }`, proteksi pemotongan baris `avoid-break` pada tabel, klausul kedinasan, blok tanda tangan, serta pemisahan halaman lampiran foto bukti dokumentasi (`page-break`) tanpa terpotong.
  3. **Perbaikan Tampilan Status Ketersediaan Barang Gudang:** Desain ulang kartu status (*Sedia*, *Sedang Keluar*, *Sedang Dipakai*, *Sedang Dipinjam*) di Dashboard dan Menu Berita Acara agar tampilan kartu proporsional, bebas error visual, teks tidak terpotong, serta penyelarasan warna kontras tinggi pada Mode Terang dan Mode Cyber HUD.
  4. **Menu Sampah (Recycle Bin / Kotak Sampah — `/trash`):** Modul penampung data terhapus dengan 2 tab (Berita Acara & Barang Inventaris), dilengkapi tombol **Pulihkan (Restore)**, **Hapus Permanen**, **Pulihkan Semua**, dan **Kosongkan Sampah**, serta indikator badge jumlah sampah di bilah menu sidebar dan drawer ponsel.
  5. **Deteksi Lokasi Real-Time GPS & Link Google Maps (Admin Only):** Menangkap koordinat geolokasi pengguna (`latitude` & `longitude`) saat login via `navigator.geolocation` dan menampilkannya sebagai tombol tautan **`📍 Buka Google Maps`** di menu **History Akses Web (`/access-logs`)** yang **hanya dapat dilihat oleh Administrator (Admin Only)**.
  6. **Fitur Lupa Password Mandiri di Halaman Login (`/forgot-password`):** Tombol pintas "Lupa Kata Sandi / PIN?" pada login, verifikasi akun via Email (Gmail) atau Nomor HP/WhatsApp tertaut, penerbitan kode verifikasi 6-digit (OTP), dan pembaruan kata sandi baru secara aman.
- **2026-11-13 (Hotfix Migrasi Database & Pengamanan Anti-Error pada Login `AccessLog`):**
  1. Menjalankan migrasi database tertunda (`2026_11_13_000006_add_soft_deletes_and_geo_location`) sehingga kolom `latitude`, `longitude`, `location_name`, dan `deleted_at` terdaftar resmi di MySQL.
  2. Menambahkan proteksi pengecekan kolom bertingkat (*defensive column check*) pada `App\Models\AccessLog::record()` sehingga proses login pengguna diproteksi 100% dan tidak akan pernah mengalami error 500 jika kolom geolokasi belum termigrasi.
- **2026-11-13 (Redesain Panel Status Ketersediaan Barang Berenergi Tinggi & Tabel Informasi Interaktif):**
  1. **High-Energy Tactical HUD Cards:** Mendesain ulang 4 kartu status (*Sedia*, *Sedang Keluar*, *Sedang Dipakai*, *Sedang Dipinjam*) dengan palet neon cerah menyala (Emerald, Amber Emas, Cyan Elektrik, Indigo Ungu), angka tebal bercahaya, badge radar ping aktif, indikator persentase (% dari total inventaris), dan micro progress-bar yang dinamis dan berenergi.
  2. **Interactive Telemetry Table Info:** Menambahkan tabel rincian informasi barang langsung di bawah kartu status dengan filter tab cepat instan (*Semua Barang*, *Sedia*, *Sedang Keluar*, *Sedang Dipakai*, *Sedang Dipinjam*) serta kolom pencarian terintegrasi. Mengklik salah satu kartu status langsung menyalakan kartu dan menyaring tabel barang secara real-time tanpa reload halaman.
