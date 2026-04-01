# POS RS Delta Surya

Aplikasi Point of Sales (POS) untuk RS Delta Surya yang dibangun menggunakan Laravel.

## 🚀 Update Terakhir
- **Otomatisasi Laporan**: Penambahan Cron Job untuk mengirim laporan transaksi harian (Excel) ke email `interview.deltasurya@yopmail.com` setiap jam **01.00 dini hari**.
- **Integrasi Docker**: Penambahan service `scheduler` di `docker-compose.yml` agar tugas terjadwal berjalan otomatis di container.
- **Log Aktivitas**: Pencatatan otomatis ke tabel log setiap kali laporan berhasil dikirim oleh sistem.

## Panduan Instalasi & Setup

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di lingkungan lokal Anda.

### 1. Persiapan Environment
Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya (terutama bagian Database jika tidak menggunakan Docker).

```bash
cp .env.example .env
```

### 2. Instalasi Dependensi (Composer & NPM)
Jalankan perintah berikut untuk menginstal library PHP dan paket JavaScript yang dibutuhkan:

```bash
# Instal library PHP
composer install

# Instal paket JavaScript
npm install

# Build aset (opsional jika menggunakan Docker)
npm run build
```

### 3. Generate Application Key
Generate kunci aplikasi Laravel agar enkripsi berjalan dengan benar:

```bash
php artisan key:generate
```

### 4. Menjalankan Aplikasi dengan Docker
Aplikasi ini sudah dikonfigurasi menggunakan Docker. Jalankan perintah berikut untuk membangun dan menjalankan semua service (App, Webserver, Postgres, Adminer, Node):

```bash
docker-compose up -d --build
```

### 5. Setup Database (Migrate & Seed)
Setelah container berjalan, lakukan migrasi database dan pengisian data awal (seeder):

```bash
# Menjalankan migrasi database
docker-compose exec app php artisan migrate

# Mengisi data awal (Seeder)
docker-compose exec app php artisan db:seed
```

### 6. Menjalankan Cron Job (Laravel Scheduler)
Aplikasi ini memiliki tugas terjadwal (seperti pengiriman laporan transaksi harian).

**Menjalankan Manual:**
Gunakan perintah ini untuk menjalankan pengiriman laporan kemarin secara manual:
```bash
docker-compose exec app php artisan report:send-yesterday
```

**Development (Local):**
Jalankan perintah ini di terminal terpisah untuk mensimulasikan cron job:
```bash
docker-compose exec app php artisan schedule:work
```

**Production:**
Tambahkan entri berikut ke crontab server Anda:
```bash
* * * * * cd /path-ke-project && docker-compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

---

## Cara Menjalankan Aplikasi

Setelah semua langkah di atas selesai, Anda dapat mengakses aplikasi melalui browser:

- **Aplikasi Utama**: [http://localhost:8000](http://localhost:8000)
- **Vite Dev Server**: Berjalan otomatis di background via Docker.

### Akses Adminer (Database GUI)
Untuk mengelola database secara visual, buka Adminer di:
- **URL**: [http://localhost:8080](http://localhost:8080)

**Detail Login Adminer:**
- **System**: `PostgreSQL`
- **Server**: `postgres`
- **Username**: `laravel`
- **Password**: `secret`
- **Database**: `laravel`

---

## Akun Login Default

Gunakan akun berikut untuk mencoba aplikasi setelah melakukan `db:seed`:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `superadmin@gmail.com` | `12345678` |
| **Kasir** | `kasir@gmail.com` | `12345678` |
| **Marketing** | `marketing@gmail.com` | `12345678` |

---

## Perintah Penting Lainnya

- **Menghentikan Docker**: `docker-compose down`
- **Melihat Log**: `docker-compose logs -f app`
- **Terminal Container**: `docker-compose exec app bash`
