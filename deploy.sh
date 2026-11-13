#!/bin/bash
set -e

echo "=========================================================="
echo "  MEMULAI INSTALASI LOGISTIK.TOC DI PROXMOX DEBIAN 12     "
echo "=========================================================="

export DEBIAN_FRONTEND=noninteractive

# 1. Pasang Repositori Resmi PHP 8.4 (Ondrej Sury) untuk Debian 12
echo "Menyiapkan repositori PHP 8.4..."
apt update -y
apt install -y lsb-release apt-transport-https ca-certificates wget gnupg2 curl git unzip zip nginx mariadb-server
curl -sSLo /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
apt update -y

# 2. Install PHP 8.4 & Seluruh Ekstensi Laravel
echo "Menginstal PHP 8.4 dan ekstensi yang dibutuhkan..."
apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-mysql php8.4-curl \
    php8.4-gd php8.4-mbstring php8.4-xml php8.4-zip php8.4-bcmath

# 3. Pastikan Service Aktif
systemctl enable --now nginx
systemctl enable --now mariadb
systemctl enable --now php8.4-fpm

# 4. Setup Database MariaDB
mariadb -e "CREATE DATABASE IF NOT EXISTS web_scan_logistik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb -e "GRANT ALL PRIVILEGES ON web_scan_logistik.* TO 'root'@'localhost';"
mariadb -e "FLUSH PRIVILEGES;"

# 5. Install Composer jika belum ada
if ! command -v composer &> /dev/null; then
    echo "Mengunduh Composer..."
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm /tmp/composer-setup.php
fi

# 6. Salin Repositori Kode
APP_DIR="/var/www/logistik"
if [ -d "$APP_DIR" ]; then
    echo "Memperbarui repositori yang ada..."
    cd "$APP_DIR"
    git reset --hard
    git pull origin main
else
    echo "Mengunduh kode aplikasi dari GitHub..."
    git clone https://github.com/onezey6/scan-gudang-toc.git "$APP_DIR"
    cd "$APP_DIR"
fi

# 7. Salin Environment & Konfigurasi Database
SERVER_IP=$(hostname -I | awk '{print $1}')
cat <<EOF > "$APP_DIR/.env"
APP_NAME="LogiScan Pro"
APP_ENV=production
APP_KEY=base64:irhsaSAv90P4/EsJ0YK0yiFYIBhElo4qdYW8D2PGeew=
APP_DEBUG=false
APP_URL=http://$SERVER_IP
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_scan_logistik
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=15
SESSION_ENCRYPT=false

FILESYSTEM_DISK=local
CACHE_STORE=database
QUEUE_CONNECTION=database
EOF

# 8. Install Dependency PHP via Composer
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# 9. Restore Database Lengkap
if [ -f "$APP_DIR/database/dump_logistik.sql" ]; then
    echo "Mengimpor data database lengkap..."
    mariadb --binary-mode=1 -u root web_scan_logistik < "$APP_DIR/database/dump_logistik.sql" || true
fi

# Jalankan migrasi terbaru untuk memastikan tidak ada kolom terlewat
php artisan migrate --force

# Storage Link & Optimization
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Konfigurasi Batas Upload 512MB di PHP 8.4 FPM
sed -i 's/^upload_max_filesize =.*/upload_max_filesize = 512M/' /etc/php/8.4/fpm/php.ini
sed -i 's/^post_max_size =.*/post_max_size = 512M/' /etc/php/8.4/fpm/php.ini
sed -i 's/^memory_limit =.*/memory_limit = 512M/' /etc/php/8.4/fpm/php.ini
sed -i 's/^max_file_uploads =.*/max_file_uploads = 500/' /etc/php/8.4/fpm/php.ini

# 11. Konfigurasi Nginx
cat <<'EOF' > /etc/nginx/sites-available/logistik
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/logistik/public;
    index index.php index.html;

    server_name _;
    client_max_body_size 512M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

rm -f /etc/nginx/sites-enabled/default
ln -sf /etc/nginx/sites-available/logistik /etc/nginx/sites-enabled/logistik

# 12. Atur Hak Akses File (Permissions)
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# 13. Restart Web Server
systemctl restart php8.4-fpm
systemctl restart nginx

echo ""
echo "=========================================================="
echo "  SELAMAT! APLIKASI LOGISTIK.TOC BERHASIL DIPASANG!      "
echo "=========================================================="
echo ""
echo "  Aplikasi sekarang sudah aktif 24 jam non-stop."
echo "  Buka browser di HP / Laptop lain lalu akses:"
echo "  👉  http://$SERVER_IP"
echo ""
echo "  Akun Login:"
echo "  - Username: admin       | Sandi: password123"
echo "  - Username: ilhamdhani  | Sandi: password123"
echo "  - Username: rina.staff  | Sandi: password123"
echo "=========================================================="
