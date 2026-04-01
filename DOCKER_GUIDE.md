# Panduan Menjalankan Aplikasi POS RS Delta Surya (Docker)

Panduan ini berisi langkah-langkah untuk menjalankan aplikasi menggunakan Docker dengan PHP 8.5 dan PostgreSQL 17.

## Prerequisites
- Docker Desktop sudah terinstal dan berjalan.
- Port yang harus tersedia di host: **8000** (Nginx), **8080** (Adminer), **5434** (PostgreSQL).

## Langkah Instalasi

### 1. Persiapan Environment
Salin file `.env.example` menjadi `.env` jika belum ada:
```bash
cp .env.example .env
```

### 2. Build dan Jalankan Container
Jalankan perintah berikut untuk membangun image dan menjalankan semua layanan di background:
```bash
docker-compose up -d --build
```

### 3. Setup Aplikasi (Pertama Kali Saja)
Setelah container berjalan, jalankan perintah ini untuk menginstal dependensi dan mengatur key:
```bash
# Generate Application Key
docker-compose exec app php artisan key:generate

# Jalankan Migrasi Database
docker-compose exec app php artisan migrate
```

## Akses Layanan

| Layanan | URL / Alamat | Keterangan |
| :--- | :--- | :--- |
| **Aplikasi Laravel** | [http://localhost:8000](http://localhost:8000) | Web utama |
| **Adminer (Database GUI)** | [http://localhost:8080](http://localhost:8080) | Pengelola Database |
| **PostgreSQL Host** | `localhost:5434` | Untuk koneksi dari DB Client (DBeaver, TablePlus, dll) |

### Detail Login Adminer (Database)
Buka [http://localhost:8080](http://localhost:8080) dan masukkan detail berikut:
- **System**: `PostgreSQL`
- **Server**: `postgres`
- **Username**: `laravel`
- **Password**: `secret`
- **Database**: `laravel`

## Perintah Penting Docker

- **Menghentikan Container**: `docker-compose stop`
- **Menjalankan Kembali**: `docker-compose start`
- **Melihat Log Aplikasi**: `docker-compose logs -f app`
- **Masuk ke Terminal Container**: `docker-compose exec app bash`
- **Menghapus Semua Container**: `docker-compose down`

## Manajemen Migrasi Database
Karena PHP berjalan di dalam Docker, gunakan perintah ini di terminal Windows Anda:

- **Membuat File Migrasi Baru**:
  ```bash
  docker-compose exec app php artisan make:migration create_nama_tabel_table
  ```
- **Menjalankan Migrasi ke Database**:
  ```bash
  docker-compose exec app php artisan migrate
  ```
- **Rollback (Batal) Migrasi Terakhir**:
  ```bash
  docker-compose exec app php artisan migrate:rollback
  ```
- **Hapus Semua Data & Ulangi Migrasi**:
  ```bash
  docker-compose exec app php artisan migrate:fresh
  ```

## Troubleshooting
- **Port Conflict**: Jika muncul error `Bind for 0.0.0.0:XXXX failed`, artinya port tersebut sudah dipakai aplikasi lain (seperti Laragon atau PostgreSQL bawaan Windows). Ubah port di file `docker-compose.yml` pada bagian `ports`.
- **Vite/NPM**: Container `node` akan menjalankan `npm run dev` secara otomatis. Jika ada perubahan CSS/JS, Vite akan melakukan hot-reload.
