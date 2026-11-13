#!/bin/bash
set -e

echo "=========================================================="
echo "  MEMULAI INSTALASI LOGISTIK.TOC DI PROXMOX DEBIAN 12     "
echo "=========================================================="

# 1. Update & Install Paket Esensial
export DEBIAN_FRONTEND=noninteractive
apt update -y
apt install -y curl git unzip zip nginx mariadb-server \
    php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl \
    php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath

# 2. Pastikan Service Aktif
systemctl enable --now nginx
systemctl enable --now mariadb
systemctl enable --now php8.2-fpm

# 3. Setup Database MariaDB
mariadb -e "CREATE DATABASE IF NOT EXISTS web_scan_logistik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb -e "GRANT ALL PRIVILEGES ON web_scan_logistik.* TO 'root'@'localhost';"
mariadb -e "FLUSH PRIVILEGES;"

# 4. Install Composer jika belum ada
if ! command -v composer &> /dev/null; then
    echo "Mengunduh Composer..."
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm /tmp/composer-setup.php
fi

# 5. Clone Repositori Kode
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

# 6. Salin Environment & Konfigurasi Database
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

# 7. Install Dependency PHP via Composer
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# 8. Restore Database Lengkap
if [ -f "$APP_DIR/database/dump_logistik.sql" ]; then
    echo "Mengimpor data database lengkap..."
    mariadb web_scan_logistik < "$APP_DIR/database/dump_logistik.sql"
fi

# Jalankan migrasi terbaru untuk memastikan tidak ada kolom terlewat
php artisan migrate --force

# Storage Link & Optimization
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Konfigurasi Batas Upload 512MB di PHP-FPM
sed -i 's/^upload_max_filesize =.*/upload_max_filesize = 512M/' /etc/php/8.2/fpm/php.ini
sed -i 's/^post_max_size =.*/post_max_size = 512M/' /etc/php/8.2/fpm/php.ini
sed -i 's/^memory_limit =.*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
sed -i 's/^max_file_uploads =.*/max_file_uploads = 500/' /etc/php/8.2/fpm/php.ini

# 10. Konfigurasi Nginx
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
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
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

# 11. Atur Hak Akses File (Permissions)
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# 12. Restart Web Server
systemctl restart php8.2-fpm
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
