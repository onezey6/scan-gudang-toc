<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi (Indonesian Validation Language Lines)
    |--------------------------------------------------------------------------
    */

    'accepted' => ':Attribute harus disetujui.',
    'accepted_if' => ':Attribute harus disetujui ketika :other adalah :value.',
    'active_url' => ':Attribute bukan URL yang valid.',
    'after' => ':Attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'array' => ':Attribute harus berupa array.',
    'before' => ':Attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':Attribute harus memiliki antara :min dan :max item.',
        'file' => ':Attribute harus berukuran antara :min dan :max kilobita.',
        'numeric' => ':Attribute harus bernilai antara :min dan :max.',
        'string' => ':Attribute harus berisi antara :min dan :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password saat ini salah.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_equals' => ':Attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':Attribute tidak cocok dengan format :format.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus terdiri dari :digits digit.',
    'digits_between' => ':Attribute harus terdiri dari antara :min dan :max digit.',
    'email' => ':Attribute harus berupa alamat surel (email) yang valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'file' => ':Attribute harus berupa berkas.',
    'filled' => ':Attribute wajib diisi.',
    'gt' => [
        'array' => ':Attribute harus memiliki lebih dari :value item.',
        'file' => ':Attribute harus berukuran lebih dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai lebih besar dari :value.',
        'string' => ':Attribute harus berisi lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':Attribute harus memiliki :value item atau lebih.',
        'file' => ':Attribute harus berukuran minimal :value kilobita.',
        'numeric' => ':Attribute harus bernilai minimal :value.',
        'string' => ':Attribute harus berisi minimal :value karakter.',
    ],
    'image' => ':Attribute harus berupa gambar (JPG, PNG, WEBP).',
    'in' => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute tidak ada di dalam :other.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'ip' => ':Attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':Attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':Attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':Attribute harus berupa string JSON yang valid.',
    'lt' => [
        'array' => ':Attribute harus memiliki kurang dari :value item.',
        'file' => ':Attribute harus berukuran kurang dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai kurang dari :value.',
        'string' => ':Attribute harus berisi kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':Attribute tidak boleh memiliki lebih dari :value item.',
        'file' => ':Attribute tidak boleh lebih besar dari :value kilobita.',
        'numeric' => ':Attribute harus bernilai maksimal :value.',
        'string' => ':Attribute harus berisi maksimal :value karakter.',
    ],
    'max' => [
        'array' => ':Attribute tidak boleh memiliki lebih dari :max item.',
        'file' => ':Attribute tidak boleh lebih besar dari :max kilobita.',
        'numeric' => ':Attribute tidak boleh lebih besar dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa berkas berformat: :values.',
    'mimetypes' => ':Attribute harus berupa berkas bertipe: :values.',
    'min' => [
        'array' => ':Attribute harus memiliki minimal :min item.',
        'file' => ':Attribute minimal berukuran :min kilobita.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal berisi :min karakter.',
    ],
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':Attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':Attribute yang diberikan telah bocor dalam pelanggaran data. Pilih :attribute yang berbeda.',
    ],
    'present' => ':Attribute wajib ada.',
    'required' => ':Attribute wajib diisi.',
    'required_if' => ':Attribute wajib diisi bila :other bernilai :value.',
    'required_unless' => ':Attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with' => ':Attribute wajib diisi bila :values tersedia.',
    'required_without' => ':Attribute wajib diisi bila :values tidak tersedia.',
    'same' => ':Attribute dan :other harus cocok.',
    'size' => [
        'array' => ':Attribute harus mengandung :size item.',
        'file' => ':Attribute harus berukuran :size kilobita.',
        'numeric' => ':Attribute harus berukuran :size.',
        'string' => ':Attribute harus berisi :size karakter.',
    ],
    'string' => ':Attribute harus berupa teks.',
    'timezone' => ':Attribute harus berupa zona waktu yang valid.',
    'unique' => ':Attribute sudah digunakan pada data lain (termasuk di Kotak Sampah).',
    'uploaded' => ':Attribute gagal diunggah.',
    'url' => ':Attribute harus berupa format URL yang valid.',
    'uuid' => ':Attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Nama Kustom Atribut Validasi
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'item_code' => 'Kode Barang',
        'barcode' => 'Kode Barcode',
        'name' => 'Nama Barang / Nama Lengkap',
        'category_id' => 'Kategori',
        'item_type' => 'Tipe Barang',
        'brand' => 'Merek',
        'unit' => 'Satuan',
        'stock' => 'Stok',
        'min_stock' => 'Stok Minimum',
        'storage_location' => 'Lokasi Penyimpanan',
        'condition_status' => 'Kondisi Barang',
        'availability_status' => 'Status Ketersediaan',
        'description' => 'Deskripsi / Keterangan',
        'photo' => 'Foto Barang',
        'avatar' => 'Foto Profil',
        'username' => 'Username Login',
        'email' => 'Alamat Email',
        'password' => 'Kata Sandi / Password',
        'current_password' => 'Password Lama',
        'phone' => 'Nomor HP',
        'job_title' => 'Jabatan',
        'department' => 'Divisi / Departemen',
        'employee_id' => 'NIP / ID Pegawai',
    ],

];
