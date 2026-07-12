# 🐳 Dokumentasi Docker & Setup Sistem

Dokumentasi ini menjelaskan secara komprehensif cara menjalankan sistem **Penilaian Kelayakan Laptop Bekas** menggunakan Docker. Sistem dirancang dengan arsitektur mikro/multi-service yang terdiri dari database MySQL, Backend Service, dan Evaluator/Fuzzy Service.

---

## 📊 Ringkasan Service & Port

Sistem dijalankan dengan tiga container utama yang dikonfigurasi melalui [docker-compose.yml](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/docker-compose.yml):

| Service | Nama Container | Port Host | Port Container | Peran / Deskripsi |
| :--- | :--- | :---: | :---: | :--- |
| **MySQL Database** | `mysql-database` | `3307` | `3306` | Database relasional utama untuk menyimpan semua data sistem. |
| **Backend Service** | `backend` | `8000` | `80` | API utama & manajemen data (Laravel 12). |
| **Evaluator Service** | `evaluator` | `8001` | `80` | Service khusus untuk perhitungan logika fuzzy (Laravel 12). |

> [!NOTE]
> Service Frontend Vue saat ini **tidak** dijalankan di dalam Docker Compose. Frontend dijalankan secara terpisah menggunakan Node.js/Vite di host local dan terhubung ke Backend Service melalui `http://localhost:8000/`.

---

## 📂 Struktur Docker

Konfigurasi container diatur oleh file-file berikut:
1. **[docker-compose.yml](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/docker-compose.yml)**: Orkestrasi multi-container untuk database, backend, dan fuzzy service.
2. **[BackendService/Dockerfile](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/BackendService/Dockerfile)**: Konfigurasi image PHP Apache untuk Backend Service.
3. **[EvaluatorService/Dockerfile](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/EvaluatorService/Dockerfile)**: Konfigurasi image PHP Apache untuk Evaluator Service.

### Rincian Infrastruktur
* **Volume `db_data`**: Digunakan untuk melakukan *data persistence* MySQL agar data di dalam database tetap aman dan tidak hilang meskipun container dihentikan atau dihapus.
* **Image Dasar Laravel (`php:8.4-apache`)**: Kedua service Laravel menggunakan Apache dengan modul `mod_rewrite` aktif, ekstensi `pdo_mysql` dan `zip` terpasang, serta konfigurasi DocumentRoot yang mengarah langsung ke folder `/public` masing-masing service Laravel.

---

## 🛠️ Prasyarat (Prerequisites)

Pastikan *environment* lokal Anda telah terpasang *tools* berikut:
* **Docker Desktop** atau **Docker Engine** & **Docker Compose**
* **Node.js** (rekomendasi versi LTS terbaru) untuk menjalankan Frontend
* **Composer** (opsional, jika ingin mengunduh dependency Laravel langsung dari mesin host)

### Verifikasi Instalasi *Tools*
Jalankan perintah berikut di terminal Anda untuk memeriksa kesiapan sistem:
```bash
docker --version
docker compose version
node --version
npm --version
composer --version
```

---

## ⚙️ Konfigurasi Environment (`.env`)

Sebelum menjalankan Docker Compose, kedua service Laravel membutuhkan file konfigurasi `.env`. 

### Langkah 1: Salin File Template `.env`
Jika belum memilikinya, salin dari file `.env.example`:
```bash
cp BackendService/.env.example BackendService/.env
cp EvaluatorService/.env.example EvaluatorService/.env
```

### Langkah 2: Sesuaikan Isi Konfigurasi `.env`

> [!IMPORTANT]
> Saat dijalankan di dalam Docker, hostname untuk database MySQL **bukan** `127.0.0.1` atau `localhost`, melainkan nama service Docker-nya, yaitu **`db`**. Begitu pula dengan koneksi antar-service.

#### 📝 Konfigurasi Penting di [BackendService/.env](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/BackendService/.env):
```env
APP_URL=http://localhost:8000

# Konfigurasi Database (Koneksi ke Container 'db')
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=manajemen_data_fuzzy
DB_USERNAME=root
DB_PASSWORD=

# Integrasi ke Evaluator Service (Koneksi ke Container 'evaluator')
FUZZY_SERVICE_URL=http://evaluator
```

#### 📝 Konfigurasi Penting di [EvaluatorService/.env](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/EvaluatorService/.env):
```env
APP_URL=http://localhost:8001

# Konfigurasi Database (Koneksi ke Container 'db')
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=manajemen_data_fuzzy
DB_USERNAME=root
DB_PASSWORD=
```

> [!TIP]
> **Memahami Jaringan Docker (Docker Networking):**
> * Dari **host/mesin lokal** Anda, MySQL dapat diakses melalui `localhost:3307`.
> * Dari **dalam container Laravel** (backend/evaluator), MySQL hanya dapat diakses melalui host `db` pada port default `3306`.
> * Dari **Backend container**, Evaluator Service diakses menggunakan URL `http://evaluator` (bukan `localhost:8001`).
> * Di dalam container, kata kunci `localhost` merujuk ke container itu sendiri, bukan ke laptop/host Anda.

---

## 📦 Dependency & Package Installation

### 1. Backend & Evaluator Service (Laravel)
Jika folder `vendor` belum tersedia di masing-masing service, unduh dependensi PHP terlebih dahulu.

**Opsi A: Menggunakan Composer lokal (jika terpasang di host):**
```bash
composer install --working-dir=BackendService
composer install --working-dir=EvaluatorService
```

**Opsi B: Menggunakan Docker Image Composer (jika host tidak memiliki Composer):**
```bash
docker run --rm -v "$PWD/BackendService:/app" composer:2 install
docker run --rm -v "$PWD/EvaluatorService:/app" composer:2 install
```

### 2. Frontend Service (Vue / Vite)
Masuk ke direktori frontend dan pasang dependensi Node:
```bash
cd FrontendService
npm install
```

---

## 🚀 Menjalankan Container

Jalankan seluruh service menggunakan Docker Compose dari direktori utama project:

```bash
# Membangun image dan menjalankan container di latar belakang (detached mode)
docker compose up -d --build
```

### Memantau Status Container & Log

* **Periksa Status Container:**
  ```bash
  docker compose ps
  ```
* **Melihat Log Semua Service:**
  ```bash
  docker compose logs -f
  ```
* **Melihat Log Service Spesifik:**
  ```bash
  docker compose logs -f backend
  docker compose logs -f evaluator
  docker compose logs -f db
  ```

---

## ⚡ Setup & Inisialisasi Laravel

Setelah container aktif dan berjalan, lakukan konfigurasi awal Laravel di dalam container:

### 1. Generate Application Key
```bash
docker compose exec backend php artisan key:generate
docker compose exec evaluator php artisan key:generate
```

### 2. Jalankan Migrasi & Database Seeder (Hanya pada Backend)
```bash
docker compose exec backend php artisan migrate --seed
```

*(Opsional) Jika Anda ingin menjalankan migrasi pada Evaluator Service secara terpisah:*
```bash
docker compose exec evaluator php artisan migrate
```

### 3. Bersihkan Cache Konfigurasi
Lakukan ini setiap kali Anda memodifikasi file `.env`:
```bash
docker compose exec backend php artisan config:clear
docker compose exec evaluator php artisan config:clear
```

---

## 💻 Menjalankan Frontend

Aplikasi Frontend berada di folder [FrontendService](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/FrontendService) dan dijalankan secara lokal di host Anda.

### 1. Konfigurasi Endpoint API
Pastikan file [FrontendService/.env.local](file:///Users/muhammadfadhil/Documents/Semester%207/Sistem_Penilaian_KelayakanLaptopBekas/FrontendService/.env.local) mengarah ke Backend Service:
```env
VITE_BASE_URL=http://localhost:8000/
```

### 2. Jalankan Server Dev Vite
```bash
cd FrontendService
npm run dev
```
Setelah berjalan, buka browser dan akses alamat default:
```text
http://localhost:5173
```

---

## 🌐 Daftar URL Akses Layanan

| Aplikasi | URL Akses | Deskripsi |
| :--- | :--- | :--- |
| **Frontend App** | [http://localhost:5173](http://localhost:5173) | Interface/Dashboard Aplikasi Web |
| **Backend API** | [http://localhost:8000](http://localhost:8000) | REST API & Dashboard Admin Laravel |
| **Evaluation API** | [http://localhost:8001](http://localhost:8001) | Endpoint Perhitungan Logika Fuzzy |
| **MySQL Database** | `localhost:3307` | Koneksi Client DB (seperti DBeaver/TablePlus) |

---

## 🔄 Flow Produksi Terbaru

```text
Frontend → BackendService → EvaluatorService → BackendService → Frontend
                         └→ Gemini AI (opsional)
```

- Frontend hanya memanggil BackendService.
- BackendService mengambil konfigurasi fuzzy dari database lalu memanggil EvaluatorService melalui `http://evaluator`.
- EvaluatorService tetap stateless dan tidak mengakses database.
- LCD dan keyboard dipilih melalui kondisi pemeriksaan dengan skor tetap; field API tetap `lcd` dan `keyboard`, sehingga perhitungan fuzzy tidak berubah.
- AI aktif otomatis bila `GEMINI_AI_ENABLED=true` dan `GEMINI_API_KEY` tersedia. Jika tidak tersedia/gagal, penilaian fuzzy tetap disimpan dan catatan AI menjadi `tidak ada catatan tambahan`.
- Kosakata AI dikelola pada menu **Pengaturan AI** (`/settings/ai-keywords`) melalui endpoint BackendService `/api/ai/keywords`.

## 🚀 Checklist Production

1. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` unik, dan `APP_URL` domain produksi.
2. Set database production; jangan gunakan password root kosong. Gunakan user database khusus dengan hak minimum.
3. Set `EVALUATOR_SERVICE_URL=http://evaluator` pada BackendService.
4. Set `GEMINI_AI_ENABLED=true` hanya jika `GEMINI_API_KEY` valid; jangan commit file `.env`.
5. Jalankan migrasi dan seed:
   ```bash
   docker compose exec backend php artisan migrate --force
   docker compose exec backend php artisan db:seed --force
   ```
6. Bersihkan dan cache konfigurasi:
   ```bash
   docker compose exec backend php artisan optimize:clear
   docker compose exec backend php artisan config:cache
   ```
7. Build frontend dengan `VITE_BASE_URL` yang mengarah ke BackendService/reverse proxy:
   ```bash
   cd FrontendService && npm ci && npm run build-only
   ```
8. Pastikan `storage` writable dan jalankan `php artisan storage:link` pada BackendService.
9. Jangan expose port MySQL `3307` dan Evaluator `8001` ke publik; akses evaluator hanya dari network internal.
10. Uji `GET /api/ai/status`, `GET /api/ai/keywords`, `GET /api/processors`, dan satu assessment sebelum traffic dibuka.
11. Backup volume/database sebelum migrasi dan siapkan rollback image serta migration plan.

> Catatan: `docker-compose.yml` saat ini adalah setup development karena memakai bind mount source dan port internal terbuka ke host. Untuk production gunakan compose/override production tanpa bind mount, tanpa port evaluator/database publik, dan letakkan Nginx/reverse proxy di depan BackendService.

## 🛠️ Perintah Operasional Lainnya

* **Menghentikan Container:**
  ```bash
  docker compose down
  ```
* **Menghentikan Container & Menghapus Volume Data (Reset Database):**
  ```bash
  docker compose down -v
  ```
  > [!WARNING]
  > Perintah `down -v` akan menghapus volume database `db_data`. Seluruh data yang tersimpan di MySQL akan **terhapus secara permanen**. Gunakan hanya jika Anda ingin meriset database ke kondisi awal.

* **Melakukan Build Ulang Tanpa Cache:**
  ```bash
  docker compose build --no-cache
  ```

* **Masuk ke Shell Container:**
  ```bash
  # Shell Backend Service
  docker compose exec backend bash

  # Shell Evaluator Service
  docker compose exec evaluator bash

  # Shell MySQL Command Line
  docker compose exec db mysql -u root manajemen_data_fuzzy
  ```

---

## 📋 Panduan Alur Cepat (Quick Start Flow)

Berikut adalah ringkasan langkah berurutan untuk menjalankan sistem dari awal hingga siap digunakan:

```bash
# 1. Persiapkan berkas .env
cp BackendService/.env.example BackendService/.env
cp EvaluatorService/.env.example EvaluatorService/.env

# 2. Pasang dependensi Laravel
composer install --working-dir=BackendService
composer install --working-dir=EvaluatorService

# 3. Jalankan container docker
docker compose up -d --build

# 4. Inisialisasi Laravel
docker compose exec backend php artisan key:generate
docker compose exec evaluator php artisan key:generate
docker compose exec backend php artisan migrate --seed

# 5. Pasang dependensi & jalankan Frontend
cd FrontendService
npm install
npm run dev
```

---

## 🔍 Panduan Pemecahan Masalah (Troubleshooting)

### ❌ Backend Tidak Dapat Terhubung ke Database
* **Penyebab:** Kesalahan pengisian host database pada `.env`.
* **Solusi:** Pastikan `DB_HOST` bernilai `db` dan `DB_PORT` bernilai `3306` pada `BackendService/.env`. Jangan gunakan `127.0.0.1` atau `localhost` di dalam container.

### ❌ Backend Gagal Memanggil Evaluator Service
* **Penyebab:** Konfigurasi inter-container network salah.
* **Solusi:** Pastikan `FUZZY_SERVICE_URL` di `BackendService/.env` diset ke `http://evaluator`. Nama `evaluator` adalah nama service resmi yang terdaftar di `docker-compose.yml` sehingga dikenali dalam jaringan internal Docker.

### ❌ Konflik Port (Port Already In Use)
* **Penyebab:** Port `8000`, `8001`, atau `3307` di komputer host Anda sedang digunakan oleh aplikasi lain.
* **Solusi:** Buka `docker-compose.yml` dan sesuaikan port host sebelah kiri sebelum titik dua.
  ```yaml
  ports:
    - "8010:80"  # Mengubah port host menjadi 8010 jika port 8000 bentrok
  ```

### ❌ Request dari Klien Eksternal Gagal (CORS / Failed to Fetch)
* **Penyebab:** Klien dijalankan dari `file://` protocol atau ada masalah dengan preflight request.
* **Solusi:** CORS sudah dikonfigurasi permissif di kedua service (`allowed_origins => ['*']`). Untuk pengujian dari HTML statis, gunakan web server lokal:
  ```bash
  npx serve ./folder-html-anda
  # atau
  python3 -m http.server 8080
  ```
  Buka `http://localhost:8080` (bukan `file:///...`). Pastikan field API sesuai: `lcd`, `battery`, `keyboard`, `ram` (bukan `lcd_input`, dll).

### ❌ Perubahan Berkas `.env` Tidak Berefek
* **Penyebab:** Laravel melakukan caching konfigurasi.
* **Solusi:** Bersihkan cache dengan menjalankan perintah berikut:
  ```bash
  docker compose exec backend php artisan config:clear
  docker compose exec evaluator php artisan config:clear
  ```

### ❌ Error Terkait Hak Akses / Permission pada Laravel Storage
* **Penyebab:** Container tidak memiliki hak akses menulis ke folder `storage` atau `bootstrap/cache` di host OS.
* **Solusi:** Jalankan perintah perizinan hak akses berikut:
  ```bash
  docker compose exec backend chmod -R 775 storage bootstrap/cache
  docker compose exec evaluator chmod -R 775 storage bootstrap/cache
  ```

---

## 💡 Catatan Pengembangan (Development Notes)

* **Bind Mount Volume:** Source code Laravel di host di-mount langsung ke dalam container via volume. Segala perubahan kode yang Anda lakukan di text editor host akan langsung ter-update di dalam container secara real-time tanpa perlu me-rebuild image.
* **Arsitektur Berorientasi Layanan (SOA):** Backend Service bertindak sebagai orkestrator data utama bagi frontend, sedangkan Evaluator Service terisolasi khusus untuk melayani komputasi logika fuzzy pendukung keputusan kelayakan laptop bekas.
