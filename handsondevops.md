# 📘 Hands-on Runbook & SOP DevOps: Panduan Praktik Lapangan (Native Baremetal LEMP)
### *Implementasi Ringan & Simpel: VPS 1 Core CPU, 2 GB RAM, Nginx, PHP 8.4-FPM, TiDB Cloud Serverless & Cloudflare (Tanpa Docker & Tanpa Kubernetes)*

**Target Project:** `UKK Presensi & Agenda Siswa / Sekolah (coba_laravel2 / ukkmuhyiyuddin)`  
**Repository GitHub:** `https://github.com/muhyiyuddin87-creator/ukkmuhyiyuddin`  
**Target Domain Resmi:** `tugasukk.web.id` *(dan `www.tugasukk.web.id`)*  
**Database Utama:** **TiDB Cloud Serverless** *(Region: AWS Singapore `ap-southeast-1`)*  
**Spesifikasi Server Target:** 1 VPS Ubuntu 24.04 LTS (1 vCPU, 2 GB RAM, 20–40 GB SSD/NVMe)  

---

## 💡 Mengapa Kombinasi VPS 2 GB RAM + TiDB Cloud Sangat Sempurna?

1. **Hemat RAM Server Drastis (Zero Database Overhead):**
   Database lokal (MySQL/MariaDB) biasanya memakan 500 MB – 1 GB RAM sendiri. Dengan **TiDB Cloud Serverless**, database berjalan di cloud terkelola (AWS Singapore), sehingga **0 MB RAM VPS** terpakai untuk database engine.
2. **Kapasitas Ramah Beban 1 Core CPU:**
   Tanpa overhead Docker/Kubernetes (yang memakan ~800 MB), VPS 2 GB RAM Anda dialokasikan penuh untuk **Nginx**, **PHP 8.4-FPM**, dan **Redis Cache**, menyisakan **~1.4 GB RAM bebas** untuk melayani ratusan siswa/guru yang login dan absen secara serentak.
3. **Gratis & Skalabilitas Otomatis:**
   TiDB Cloud Serverless menyediakan kuota gratis bulanan (Free Tier 5 GiB Storage & 50M Request Units), sangat melimpah untuk kebutuhan ujian UKK dan operasional sekolah.

---

## 📋 LEMBAR KERJA PARAMETER (Isi Sebelum Mulai Eksekusi)

Catat parameter berikut sebelum menjalankan instalasi di server:

| Parameter Variabel | Nilai Riil Produksi | Keterangan |
| :--- | :--- | :--- |
| **Domain Utama** | `tugasukk.web.id` | Cloudflare DNS Proxied (Orange Cloud) |
| **Subdomain Alternatif** | `www.tugasukk.web.id` | CNAME mengarah ke `tugasukk.web.id` |
| **IP Publik VPS** | `45.118.133.148` *(atau IP VPS Anda)* | Ubuntu 24.04 LTS |
| **User SSH VPS** | `root` | Akses terminal remote |
| **Direktori Aplikasi** | `/var/www/ukkmuhyiyuddin` | Path lokasi source code di VPS |
| **Versi PHP** | `PHP 8.4-FPM` | Wajib untuk Laravel 13 & Symfony 8 |
| **Host TiDB Cloud** | `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` | TiDB Serverless AWS Singapore |
| **Port TiDB Cloud** | `4000` | Port standar MySQL TLS TiDB |
| **Nama Database** | `db_tugasukk` | Database terisolasi di TiDB Cloud |
| **User TiDB Cloud** | `4VKzw2H5oPCeXer.root` *(atau user Anda)* | User akses cluster TiDB Cloud |
| **Password TiDB Cloud** | *Password yang Anda buat di TiDB* | Disimpan aman di file `.env` |
| **Alokasi RAM VPS** | `2 GB Fisik + 2 GB Swap` | Total 4 GB virtual memory |
| **Alokasi CPU VPS** | `1 Core vCPU` | Tuning worker process = 1 |

---

## 📑 DAFTAR MODUL PRAKTIK
- [MODUL 1: Persiapan VPS & Setup Swap 2 GB (Bantalan Memori)](#modul-1-persiapan-vps--setup-swap-2-gb-bantalan-memori)
- [MODUL 2: Setup Database TiDB Cloud Serverless & Cloudflare DNS](#modul-2-setup-database-tidb-cloud-serverless--cloudflare-dns)
- [MODUL 3: Instalasi LEMP Stack Lengkap (Nginx, PHP 8.4, Redis, Node.js, Python3)](#modul-3-instalasi-lemp-stack-lengkap-nginx-php-84-redis-nodejs-python3)
- [MODUL 4: Tuning Nginx & PHP-FPM Khusus Domain tugasukk.web.id](#modul-4-tuning-nginx--php-fpm-khusus-domain-tugasukkmyid)
- [MODUL 5: Clone Project & Setup Konfigurasi Laravel (.env Produksi)](#modul-5-clone-project--setup-konfigurasi-laravel-env-produksi)
- [MODUL 6: Background Queue Worker & Task Scheduler (Systemd & Cron)](#modul-6-background-queue-worker--task-scheduler-systemd--cron)
- [MODUL 7: Skrip Automasi Update 1-Klik (`deploy.sh`)](#modul-7-skrip-automasi-update-1-klik-deploysh)
- [MODUL 8: Pengujian Beban (Stress Test k6 ke tugasukk.web.id)](#modul-8-pengujian-beban-stress-test-k6-ke-tugasukkmyid)
- [MODUL 9: Runbook Troubleshooting & Emergency Recovery](#modul-9-runbook-troubleshooting--emergency-recovery)

---

## MODUL 1: Persiapan VPS & Setup Swap 2 GB (Bantalan Memori)

🖥️ `[LOKASI: TERMINAL VPS (SSH)]`

Masuk ke VPS Anda via SSH:
```bash
ssh root@45.118.133.148
```

### Langkah 1.1: Update Sistem Operasi & Paket Dasar
```bash
apt update && apt upgrade -y
apt install -y curl wget git htop ufw fail2ban unzip software-properties-common ca-certificates
```

### Langkah 1.2: Buat Swap Memory 2 GB (Pencegah Crash)
> [!IMPORTANT]
> Swap memory 2 GB memastikan proses kompilasi asset (`npm run build`) dan instalasi library (`composer install`) tidak pernah gagal karena kehabisan RAM.
```bash
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap sw 0 0' | tee -a /etc/fstab
```
Verifikasi bahwa swap sudah aktif:
```bash
free -h
```

### Langkah 1.3: Tuning Kernel Linux untuk Efisiensi RAM 2 GB
```bash
cat << 'EOF' | tee -a /etc/sysctl.conf
vm.swappiness=10
net.core.somaxconn=1024
fs.file-max=2097152
EOF
sysctl -p
```

### Langkah 1.4: Konfigurasi Keamanan Firewall UFW
```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp    # SSH Terminal
ufw allow 80/tcp    # HTTP Web
ufw allow 443/tcp   # HTTPS Web
ufw --force enable
```

---

## MODUL 2: Setup Database TiDB Cloud Serverless & Cloudflare DNS

### Langkah 2.1: Buat Database di TiDB Cloud Serverless 🌐 `[LOKASI: BROWSER CLOUD]`
1. Buka browser dan login ke [**TiDB Cloud Console**](https://tidbcloud.com/).
2. Masuk ke cluster Serverless Anda (Region: AWS Singapore `ap-southeast-1`).
3. Buka menu **SQL Editor**, lalu jalankan query SQL untuk membuat database:
   ```sql
   CREATE DATABASE db_tugasukk;
   ```
4. Klik tombol **Connect** di pojok kanan atas dashboard TiDB Cloud:
   * Pilih koneksi: **General** / **MySQL CLI**.
   * Catat detail berikut:
     * **Host:** `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`
     * **Port:** `4000`
     * **User:** `4VKzw2H5oPCeXer.root` *(atau user akun Anda)*
     * **Password:** *(Password cluster yang Anda atur)*
     * **SSL CA:** `/etc/ssl/certs/ca-certificates.crt`

---

### Langkah 2.2: Konfigurasi DNS & SSL Domain `tugasukk.web.id` di Cloudflare 🌐 `[LOKASI: BROWSER CLOUD]`
1. Login ke dashboard [**Cloudflare**](https://dash.cloudflare.com/).
2. Pilih domain **`tugasukk.web.id`**.
3. Buka menu **DNS** ➔ **Records**, lalu tambahkan 2 buah DNS Record:
   * **Record 1 (Domain Utama):**
     * **Type:** `A`
     * **Name:** `@` (mewakili `tugasukk.web.id`)
     * **IPv4 address:** `45.118.133.148` *(IP VPS Anda)*
     * **Proxy status:** **Proxied (Orange Cloud)** ✅
   * **Record 2 (Subdomain WWW):**
     * **Type:** `CNAME`
     * **Name:** `www`
     * **Target:** `tugasukk.web.id`
     * **Proxy status:** **Proxied (Orange Cloud)** ✅
4. Buka menu **SSL/TLS**:
   * Set mode enkripsi SSL ke: **`Full`** (atau **`Flexible`**).
   > [!NOTE]
   > Mode **Full** memastikan Cloudflare mengenkripsi lalu lintas pengunjung menggunakan sertifikat SSL HTTPS gratis otomatis dari Cloudflare Edge.

---

## MODUL 3: Instalasi LEMP Stack Lengkap (Nginx, PHP 8.4, Redis, Node.js, Python3)

🖥️ `[LOKASI: TERMINAL VPS (SSH)]`

### Langkah 3.1: Install Nginx Web Server
```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

### Langkah 3.2: Install PHP 8.4 & Seluruh Ekstensi Pendukung
```bash
# Tambahkan repositori resmi PHP Ondrej
add-apt-repository -y ppa:ondrej/php
apt update

# Install PHP 8.4 FPM beserta modul lengkap Laravel 13 & PhpSpreadsheet
apt install -y \
    php8.4-fpm \
    php8.4-cli \
    php8.4-mysql \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-zip \
    php8.4-gd \
    php8.4-curl \
    php8.4-intl \
    php8.4-bcmath \
    php8.4-opcache \
    php8.4-redis

systemctl enable php8.4-fpm
systemctl start php8.4-fpm
```

### Langkah 3.3: Install Composer
```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer --version
```

### Langkah 3.4: Install Node.js 22 LTS & NPM (Untuk Kompilasi Tailwind CSS v4)
```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs
node -v && npm -v
```

### Langkah 3.5: Install Python 3 & `pdfplumber` (Engine Ekstraksi Jadwal Waka)
Fitur import jadwal pelajaran PDF di Waka Dashboard mengeksekusi script [`app/Support/extract_pdf_schedules.py`](file:///c:/laragon/www/coba_laravel2/app/Support/extract_pdf_schedules.py) menggunakan binary Python:
```bash
apt install -y python3 python3-pip python3-pdfplumber
# Jika paket python3-pdfplumber tidak ditemukan di apt, gunakan pip:
python3 -m pip install --break-system-packages pdfplumber
```

### Langkah 3.6: Install & Batasi RAM Redis Server (128 MB)
```bash
apt install -y redis-server

# Batasi Redis agar tidak memakan RAM VPS lebih dari 128 MB
cat << 'EOF' >> /etc/redis/redis.conf
maxmemory 128mb
maxmemory-policy allkeys-lru
EOF

systemctl restart redis-server
systemctl enable redis-server
```

---

## MODUL 4: Tuning Nginx & PHP-FPM Khusus Domain tugasukk.web.id

🖥️ `[LOKASI: TERMINAL VPS (SSH)]`

### Langkah 4.1: Tuning PHP-FPM Pool (`/etc/php/8.4/fpm/pool.d/www.conf`)
Buka file pool:
```bash
nano /etc/php/8.4/fpm/pool.d/www.conf
```
Sesuaikan konfigurasi process manager:
```ini
pm = dynamic
pm.max_children = 12
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 4
pm.max_requests = 500
```

### Langkah 4.2: Tuning Opcache & Limit PHP (`/etc/php/8.4/fpm/conf.d/99-custom.ini`)
```bash
cat << 'EOF' > /etc/php/8.4/fpm/conf.d/99-custom.ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 120
date.timezone = Asia/Jakarta
expose_php = Off

[opcache]
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0
opcache.save_comments = 1
EOF

systemctl restart php8.4-fpm
```

### Langkah 4.3: Konfigurasi Virtual Host Nginx untuk `tugasukk.web.id`
Buat file konfigurasi Nginx:
```bash
cat << 'EOF' > /etc/nginx/sites-available/tugasukk.web.id
server {
    listen 80;
    server_name tugasukk.web.id www.tugasukk.web.id;

    root /var/www/ukkmuhyiyuddin/public;
    index index.php index.html;

    # Izinkan upload foto presensi, jurnal, & file jadwal hingga 50MB
    client_max_body_size 50M;

    # Kompresi Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Cache untuk foto selfie presensi & bukti jurnal
    location ~* ^/storage/ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, max-age=31536000, immutable";
        try_files $uri =404;
    }

    # Cache untuk build frontend Tailwind CSS v4 & Vite
    location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|svg|woff2|woff)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }

    # FastCGI PHP-FPM
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 60s;
    }

    # Amankan file sensitif (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
```

Aktifkan konfigurasi Nginx:
```bash
ln -s /etc/nginx/sites-available/tugasukk.web.id /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx
```

---

## MODUL 5: Clone Project & Setup Konfigurasi Laravel (.env Produksi)

🖥️ `[LOKASI: TERMINAL VPS (SSH)]`

### Langkah 5.1: Clone Repositori Git
```bash
git clone https://github.com/muhyiyuddin87-creator/ukkmuhyiyuddin.git /var/www/ukkmuhyiyuddin
cd /var/www/ukkmuhyiyuddin
```

### Langkah 5.2: Atur Izin Akses Folder
```bash
chown -R www-data:www-data /var/www/ukkmuhyiyuddin
chmod -R 775 /var/www/ukkmuhyiyuddin/storage /var/www/ukkmuhyiyuddin/bootstrap/cache
```

### Langkah 5.3: Install Dependensi PHP & Kompilasi Frontend
```bash
# Install library PHP produksi
composer install --no-dev --optimize-autoloader --no-interaction

# Install modul Node & kompilasi Tailwind CSS v4
npm install
npm run build
```

### Langkah 5.4: Konfigurasi File `.env` Produksi
Buat file `.env`:
```bash
nano /var/www/ukkmuhyiyuddin/.env
```
Isi konfigurasi berikut *(sesuaikan password TiDB Cloud Anda)*:
```env
APP_NAME="UKK Presensi & Agenda Siswa"
APP_ENV=production
APP_KEY=base64:oS8Ns2XUHL20VC1MoIWXd5bKw0GcDjt+94CTruepMKE=
APP_DEBUG=false
APP_URL=https://tugasukk.web.id

LOG_CHANNEL=stack
LOG_LEVEL=error

# ==========================================
# KONEKSI DATABASE TIDB CLOUD SERVERLESS
# ==========================================
DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=db_tugasukk
DB_USERNAME=4VKzw2H5oPCeXer.root
DB_PASSWORD=PasswordTiDBAnda
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt

# ==========================================
# SESSION, CACHE & QUEUE ENGINE (REDIS)
# ==========================================
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

FILESYSTEM_DISK=public
```

### Langkah 5.5: Migrasi Database TiDB & Seeding Akun Demo
```bash
# 1. Hubungkan symlink storage publik untuk foto upload
php artisan storage:link

# 2. Migrasi seluruh tabel Laravel ke TiDB Cloud
php artisan migrate --force

# 3. Jalankan seeding akun demo
php artisan db:seed --force

# 4. Kunci cache konfigurasi & routing untuk performa maksimal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 🔑 Akun Demo yang Siap Langsung Digunakan:
Buka di browser: 👉 **`https://tugasukk.web.id/login`** *(atau lokal: `http://127.0.0.1:8000/login`)*

| Peran (Role) | Username | Password Default | Keterangan & Hak Akses |
| :--- | :--- | :--- | :--- |
| **Staf Kesiswaan 1** | `kesiswaan1` *(atau `kesiswaan`)* | `kesiswaan123` | Verifikasi & persetujuan surat izin siswa (Role 4) |
| **Orang Tua Siswa** | `ortu.muhyiyuddin` *(atau `ortu`)* | `ortu123` | Memantau absensi & jurnal siswa **MUHYIYUDDIN** (Role 6) |
| **Wali Kelas** | `rosyida.ramadhani.s.pd` | `guru123` | **Wali Kelas XII RPL 2** (ROSYIDA RAMADHANI, S.Pd) & Pengajar (Role 2) |
| **Siswa Utama** | `muhyiyuddin` *(atau `siswa`)* | `siswa123` | Siswa **MUHYIYUDDIN** (Kelas XII RPL 2), presensi & pengajuan izin (Role 3) |
| **Waka Kurikulum** | `waka` | `waka123` | Manajemen jadwal, import excel siswa/guru, import PDF jadwal (Role 1) |
| **Guru Pengajar** | `guru` | `guru123` | Pengisian agenda mengajar (jurnal) & presensi kelas (Role 2) |

---

## MODUL 6: Background Queue Worker & Task Scheduler (Systemd & Cron)

🖥️ `[LOKASI: TERMINAL VPS (SSH)]`

### Langkah 6.1: Buat Systemd Service untuk Queue Worker
```bash
cat << 'EOF' > /etc/systemd/system/ukk-worker.service
[Unit]
Description=Laravel Queue Worker UKK
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/ukkmuhyiyuddin/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --memory=128

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable --now ukk-worker.service
```

### Langkah 6.2: Setup Cron Job untuk Laravel Scheduler
```bash
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/ukkmuhyiyuddin && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

---

## MODUL 7: Skrip Automasi Update 1-Klik (`deploy.sh`)

Skrip [`deploy.sh`](file:///c:/laragon/www/coba_laravel2/deploy.sh) telah dibuat di root repository.  
Setiap kali Anda selesai melakukan `git push` dari laptop lokal, perbarui aplikasi di VPS cukup dengan 1 baris perintah:

```bash
bash /var/www/ukkmuhyiyuddin/deploy.sh
```

Isi dari skrip tersebut secara otomatis akan:
1. Menyalakan maintenance mode.
2. Melakukan `git pull origin main`.
3. Menjalankan `composer install` & `npm run build`.
4. Menjalankan migrasi database baru ke TiDB Cloud.
5. Me-refresh cache config/route/view.
6. Me-restart queue worker & reload PHP-FPM.
7. Membuka kembali website dalam kondisi siap pakai.

---

## MODUL 8: Pengujian Beban (Stress Test k6 ke tugasukk.web.id)

🧪 `[LOKASI: TERMINAL LAPTOP / PENGUJI]`

Jalankan pengujian beban dari laptop Anda untuk menguji ketahanan server 1 Core & 2 GB RAM dengan database TiDB Cloud:

```bash
k6 run --vus 150 --duration 1m k6-load-test.js
```

Target URL otomatis menguji:
1. `GET https://tugasukk.web.id/` (Ekspektasi: status **302 Redirect** ke `/login`).
2. `GET https://tugasukk.web.id/login` (Ekspektasi: status **200 OK** dalam response time **< 150ms**).

---

## MODUL 9: Runbook Troubleshooting & Emergency Recovery

| Masalah yang Ditemui | Penyebab Umum | Solusi Cepat |
| :--- | :--- | :--- |
| **`Access Denied` / `SSL connection error` ke TiDB Cloud** | Path file sertifikat CA belum ada di `.env` | Pastikan di `.env` terpasang: `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt`. |
| **Error `No module named 'pdfplumber'` saat Import Jadwal PDF** | Library python belum terpasang di sistem Ubuntu | Jalankan di terminal: `python3 -m pip install --break-system-packages pdfplumber`. |
| **Cloudflare Error 522 (Connection Timed Out)** | Port 80 atau 443 terblokir oleh firewall VPS | Pastikan port terbuka di UFW: `ufw allow 80/tcp && ufw allow 443/tcp`. |
| **Cloudflare Error 526 (Invalid SSL Certificate)** | Mode SSL di Cloudflare disetel ke *Full (strict)* | Di Cloudflare Dashboard ➔ Menu **SSL/TLS** ➔ Ganti mode ke **Full** (atau **Flexible**). |
| **`npm run build` mati tiba-tiba (Exit Code 137)** | RAM fisik penuh saat build asset Tailwind CSS v4 | Periksa status Swap dengan `free -h`. Pastikan Swap 2 GB sudah aktif (Langkah 1.2). |
| **Foto Presensi / Dokumen Izin 404 Not Found** | Symlink storage publik belum terbentuk | Jalankan `php artisan storage:link` dan pastikan permission folder benar: `chown -R www-data:www-data storage/`. |
| **Nginx 502 Bad Gateway** | Service PHP 8.4-FPM mati | Jalankan `systemctl restart php8.4-fpm` dan cek log dengan `tail -f /var/log/nginx/error.log`. |
