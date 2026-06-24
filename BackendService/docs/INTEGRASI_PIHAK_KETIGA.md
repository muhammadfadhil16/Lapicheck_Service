# Panduan Integrasi Pihak Ketiga — BackendService

**Versi:** 1.0 | **Tanggal:** 24 Juni 2026

Dokumen ini memandu pengembang eksternal untuk mengintegrasikan sistem penilaian kelayakan laptop bekas ke dalam aplikasi mereka tanpa melalui frontend asli.

---

## Daftar Isi

1. [Prasyarat](#1-prasyarat)
2. [Arsitektur & Alur Data](#2-arsitektur--alur-data)
3. [Persiapan Lingkungan](#3-persiapan-lingkungan)
4. [Alur Integrasi Langkah demi Langkah](#4-alur-integrasi-langkah-demi-langkah)
5. [Referensi API Lengkap](#5-referensi-api-lengkap)
6. [Contoh Kode Integrasi](#6-contoh-kode-integrasi)
7. [Penanganan Error](#7-penanganan-error)
8. [Tips & Troubleshooting](#8-tips--troubleshooting)

---

## 1. Prasyarat

Sebelum memulai integrasi, pastikan Anda memiliki:

- **URL Base BackendService** (contoh: `http://localhost:8000` atau `https://api.domain.com`)
- **Akses ke database** (jika perlu mengelola master data processor)
- **API Key Gemini** (opsional, hanya jika ingin menggunakan fitur AI)

### Spesifikasi Teknis

| Item | Keterangan |
|------|-----------|
| Protokol | HTTP/HTTPS |
| Format Data | JSON (`application/json`) atau `multipart/form-data` |
| Autentikasi | Tidak ada (public API untuk kemudahan integrasi) |
| CORS | `Access-Control-Allow-Origin: *` |

---

## 2. Arsitektur & Alur Data

```
Sistem Eksternal (Aplikasi Anda)
    │
    │  HTTP POST /api/assessments  (JSON)
    ▼
┌──────────────────────────────────────────────────────┐
│                 BackendService (:8000)                │
│                                                      │
│  1. Validasi input                                   │
│  2. Ambil benchmark processor dari database          │
│  3. Ambil kurva fuzzy, aturan, threshold dari DB     │
│  4. Kirim ke EvaluatorService (POST :8001)           │
│  5. Hitung estimated_price                           │
│  6. (Opsional) Panggil Gemini AI                     │
│  7. Simpan ke database                               │
│  8. Kembalikan response                              │
└──────────────────────┬───────────────────────────────┘
                       │
          ┌────────────┼────────────┐
          ▼            ▼            ▼
   MySQL (:3306)  Evaluator    Gemini AI
                  Service      (gemini-2.5-flash)
                  (:8001)
```

### Alur Permintaan Penilaian Baru

```
[Your App] ──POST /api/assessments──► [BackendService]
                                          │
                          ┌───────────────┼───────────────┐
                          ▼               ▼               ▼
                     fuzzy_configs    fuzzy_rules    fuzzy_thresholds
                     (kurva)          (243 aturan)   (batas kelayakan)
                          │               │               │
                          └───────┬───────┘               │
                                  ▼                       │
                     EvaluatorService ────────────────────►│
                                  │                        │
                                  ▼                        ▼
                            final_score              threshold
                                  │                        │
                                  ◄────────── result ──────┘
                                          │
                              ┌───────────┴───────────┐
                              ▼                       ▼
                      Hitung estimasi           Gemini AI
                      harga                     (opsional)
                              │                       │
                              ◄──── simpan ke DB ─────►
                                          │
                          ◄── response JSON ──►
```

---

## 3. Persiapan Lingkungan

### 3.1 Pastikan BackendService Berjalan

```bash
# Cek kesehatan service
curl http://localhost:8000/api/assessments

# Response yang diharapkan (200 OK):
# { "status": "success", "data": { "data": [], ... } }
```

### 3.2 Pastikan Master Data Processor Tersedia

Penilaian membutuhkan data processor. Cek daftar processor yang tersedia:

```bash
curl http://localhost:8000/api/processors
```

Jika masih kosong, Anda perlu menambahkan processor terlebih dahulu (untuk testing):

```bash
curl -X POST http://localhost:8000/api/processors \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Intel Core i5-1135G7",
    "benchmark_score": 8742,
    "category": "Sedang"
  }'
```

> **Catatan:** Endpoint `POST /api/processors` bersifat **testing only**. Untuk produksi, data processor dikelola melalui seeder atau admin panel.

### 3.3 Konfigurasi CORS (Jika Akses dari Browser)

BackendService sudah dikonfigurasi mengizinkan semua origin (`*`). Jika Anda mengalami masalah CORS:

```bash
# Verifikasi header CORS
curl -I -X OPTIONS http://localhost:8000/api/assessments \
  -H "Origin: https://app-anda.com" \
  -H "Access-Control-Request-Method: POST"

# Response harus mengandung:
# Access-Control-Allow-Origin: *
# Access-Control-Allow-Methods: *
# Access-Control-Allow-Headers: *
```

---

## 4. Alur Integrasi Langkah demi Langkah

### Ringkasan Cepat (4 Langkah)

| Langkah | Aksi | Endpoint | Tujuan |
|---------|------|----------|--------|
| 1 | Dapatkan daftar processor | `GET /api/processors` | Untuk referensi `processor_id` |
| 2 | Kirim penilaian baru | `POST /api/assessments` | Membuat penilaian |
| 3 | (Opsional) Lihat detail | `GET /api/assessments/{id}` | Cek hasil lengkap |
| 4 | (Opsional) Filter riwayat | `GET /api/assessments?search=&start_date=&end_date=` | Cari penilaian |

### Langkah 1: Dapatkan Daftar Processor

Dapatkan data processor untuk referensi:

**Request:**
```bash
GET /api/processors
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Intel Core i5-1135G7",
      "benchmark_score": 8742,
      "category": "Sedang"
    },
    {
      "id": 2,
      "name": "AMD Ryzen 5 5600H",
      "benchmark_score": 14000,
      "category": "Tinggi"
    }
  ]
}
```

### Langkah 2: Kirim Penilaian Baru

Ini adalah langkah utama untuk mendapat skor kelayakan laptop.

**Contoh Request Sederhana (tanpa AI):**

```bash
curl -X POST http://localhost:8000/api/assessments \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "laptop_name": "Lenovo ThinkPad X230",
    "lcd": 80,
    "battery": 75,
    "keyboard": 85,
    "ram": 8,
    "market_price": 3000000,
    "processor_id": 1
  }'
```

**Contoh Request dengan Processor Baru:**

```bash
curl -X POST http://localhost:8000/api/assessments \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "laptop_name": "Lenovo ThinkPad X230",
    "lcd": 80,
    "battery": 75,
    "keyboard": 85,
    "ram": 8,
    "market_price": 3000000,
    "processor_name": "Intel Core i5-8350U",
    "processor_input": 8200,
    "use_ai": true
  }'
```

### Langkah 3: Lihat Detail Penilaian

Setelah mendapat `id` dari response Langkah 2:

```bash
GET /api/assessments/{id}
```

### Langkah 4: Filter Riwayat Penilaian

```bash
# Cari berdasarkan nama atau ID
GET /api/assessments?search=John

# Filter periode (format: YYYY-MM-DD, zona WIB)
GET /api/assessments?start_date=2026-06-01&end_date=2026-06-24

# Kombinasi
GET /api/assessments?search=Lenovo&start_date=2026-01-01&end_date=2026-12-31&page=1
```

---

## 5. Referensi API Lengkap

### 5.1 `POST /api/assessments` — Buat Penilaian Baru

**Endpoint:** `POST /api/assessments`
**Content-Type:** `application/json` atau `multipart/form-data`

#### Field Request

| Field | Tipe | Required | Deskripsi |
|-------|------|----------|-----------|
| `customer_name` | string | ✅ | Nama pelanggan/pemilik laptop |
| `laptop_name` | string | ✅ | Nama/model laptop |
| `lcd` | integer | ✅ | Kondisi layar (0–100) |
| `battery` | integer | ✅ | Kesehatan baterai (0–100) |
| `keyboard` | integer | ✅ | Kondisi keyboard (0–100) |
| `ram` | numeric | ✅ | Kapasitas RAM dalam GB (contoh: 8, 16, 32) |
| `market_price` | integer | ✅ | Harga pasar laptop baru saat ini (dalam Rupiah) |
| `processor_id` | integer | kondisi | ID dari tabel `processors`. **Wajib** jika `processor_name` tidak diisi |
| `processor_name` | string | kondisi | Nama processor baru. **Wajib** jika `processor_id` tidak diisi |
| `processor_input` | numeric | kondisi | Skor benchmark PassMark processor baru. **Wajib** jika `processor_id` tidak diisi |
| `description` | string | ❌ | Catatan kondisi fisik laptop (dikirim ke Gemini AI jika relevan) |
| `use_ai` | boolean | ❌ | Aktifkan analisis AI Gemini (default: `false`) |
| `images` | file[] | ❌ | File gambar laptop, maksimal 3 file (jpeg/png, max 2MB per file) |

> **PENTING:** Nama field adalah `lcd`, `battery`, `keyboard`, `ram`, `processor_id` — **bukan** `lcd_input`, `battery_input`, `processor_input` (kecuali untuk processor baru).

#### Response Sukses (201 Created)

```json
{
  "status": "success",
  "message": "Penilaian dan gambar berhasil disimpan.",
  "data": {
    "id": 1,
    "customer_name": "John Doe",
    "laptop_name": "Lenovo ThinkPad X230",
    "lcd_input": 80,
    "battery_input": 75,
    "processor_input": 8742,
    "keyboard_input": 85,
    "ram_input": 8,
    "final_score": 72.45,
    "status": "Cukup Layak",
    "market_price": 3000000,
    "estimated_price": 2173500,
    "description": null,
    "ai_conclusion": "tidak ada catatan tambahan",
    "processor_id": 1,
    "processor": {
      "id": 1,
      "name": "Intel Core i5-1135G7",
      "benchmark_score": 8742,
      "category": "Sedang"
    },
    "images": [],
    "description_ignored": false,
    "ai_used": false,
    "ai_warning": null,
    "created_at": "2026-06-24T10:00:00.000000Z",
    "updated_at": "2026-06-24T10:00:00.000000Z"
  }
}
```

#### Penjelasan Field Response

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | ID unik penilaian |
| `final_score` | float | Skor kelayakan akhir (0–100) dari fuzzy logic |
| `status` | string | `Tidak Layak` (0–65), `Cukup Layak` (65–85), `Layak` (85–100) |
| `estimated_price` | integer | `floor(market_price * (final_score / 100))` |
| `description_ignored` | boolean | `true` jika deskripsi tidak relevan dan tidak dikirim ke AI |
| `ai_used` | boolean | `true` jika AI Gemini berhasil digunakan |
| `ai_warning` | string|null | Pesan peringatan jika AI gagal (null jika berhasil/tidak digunakan) |

### 5.2 `GET /api/assessments` — Daftar Riwayat Penilaian

**Endpoint:** `GET /api/assessments`

**Parameter Query:**

| Parameter | Tipe | Required | Deskripsi |
|-----------|------|----------|-----------|
| `search` | string | ❌ | Cari berdasarkan `customer_name`, `laptop_name`, atau `id` |
| `start_date` | string | ❌ | Filter tanggal awal (format: `YYYY-MM-DD`, zona WIB) |
| `end_date` | string | ❌ | Filter tanggal akhir (format: `YYYY-MM-DD`, zona WIB) |
| `page` | integer | ❌ | Halaman pagination (default: 1, 10 data per halaman) |

**Response (200 OK):**

```json
{
  "status": "success",
  "data": {
    "data": [
      {
        "id": 1,
        "customer_name": "John Doe",
        "laptop_name": "Lenovo ThinkPad X230",
        "lcd_input": 80,
        "battery_input": 75,
        "processor_input": 8742,
        "keyboard_input": 85,
        "ram_input": 8,
        "final_score": 72.45,
        "status": "Cukup Layak",
        "market_price": 3000000,
        "estimated_price": 2173500,
        "description": null,
        "ai_conclusion": "tidak ada catatan tambahan",
        "processor_id": 1,
        "processor": {
          "id": 1,
          "name": "Intel Core i5-1135G7",
          "benchmark_score": 8742,
          "category": "Sedang"
        },
        "images": [],
        "created_at": "2026-06-24T10:00:00.000000Z",
        "updated_at": "2026-06-24T10:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  }
}
```

### 5.3 `GET /api/assessments/{id}` — Detail Penilaian

**Endpoint:** `GET /api/assessments/{id}`

**Response (200 OK):**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "customer_name": "John Doe",
    "laptop_name": "Lenovo ThinkPad X230",
    "lcd_input": 80,
    "battery_input": 75,
    "processor_input": 8742,
    "keyboard_input": 85,
    "ram_input": 8,
    "final_score": 72.45,
    "status": "Cukup Layak",
    "market_price": 3000000,
    "estimated_price": 2173500,
    "description": null,
    "ai_conclusion": "tidak ada catatan tambahan",
    "processor": {
      "id": 1,
      "name": "Intel Core i5-1135G7",
      "benchmark_score": 8742,
      "category": "Sedang"
    },
    "images": [],
    "created_at": "2026-06-24T10:00:00.000000Z",
    "updated_at": "2026-06-24T10:00:00.000000Z"
  }
}
```

### 5.4 `DELETE /api/assessments/{id}` — Hapus Penilaian

**Endpoint:** `DELETE /api/assessments/{id}`

**Response (200 OK):**

```json
{
  "status": "success",
  "message": "Data penilaian berhasil dihapus."
}
```

---

## 6. Contoh Kode Integrasi

### 6.1 JavaScript (Fetch API)

```javascript
async function createAssessment(data) {
  const response = await fetch('http://localhost:8000/api/assessments', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(JSON.stringify(error));
  }

  return response.json();
}

// Contoh penggunaan
const payload = {
  customer_name: "John Doe",
  laptop_name: "Lenovo ThinkPad X230",
  lcd: 80,
  battery: 75,
  keyboard: 85,
  ram: 8,
  market_price: 3000000,
  processor_id: 1,
  description: "LCD mulus, keyboard berfungsi normal, baterai masih tahan 2 jam",
  use_ai: true
};

createAssessment(payload)
  .then(result => {
    console.log('Skor:', result.data.final_score);
    console.log('Status:', result.data.status);
    console.log('Harga Estimasi:', result.data.estimated_price);
  })
  .catch(err => console.error('Gagal:', err));
```

### 6.2 JavaScript (Axios)

```javascript
import axios from 'axios';

const API_BASE = 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE,
  headers: { 'Content-Type': 'application/json' }
});

// Buat penilaian
async function createAssessment(payload) {
  const { data } = await api.post('/assessments', payload);
  return data;
}

// Ambil daftar riwayat
async function getAssessments(params = {}) {
  const { data } = await api.get('/assessments', { params });
  return data;
}

// Ambil detail
async function getAssessmentDetail(id) {
  const { data } = await api.get(`/assessments/${id}`);
  return data;
}

// Hapus
async function deleteAssessment(id) {
  const { data } = await api.delete(`/assessments/${id}`);
  return data;
}

// Contoh
(async () => {
  const result = await createAssessment({
    customer_name: "John Doe",
    laptop_name: "Lenovo ThinkPad X230",
    lcd: 80,
    battery: 75,
    keyboard: 85,
    ram: 8,
    market_price: 3000000,
    processor_id: 1
  });

  console.log(`Skor: ${result.data.final_score} — ${result.data.status}`);
  console.log(`Harga: Rp ${result.data.estimated_price.toLocaleString()}`);
})();
```

### 6.3 Python (requests)

```python
import requests

API_BASE = "http://localhost:8000/api"

def create_assessment(data):
    response = requests.post(f"{API_BASE}/assessments", json=data)
    response.raise_for_status()
    return response.json()

def get_assessments(search=None, start_date=None, end_date=None):
    params = {}
    if search: params["search"] = search
    if start_date: params["start_date"] = start_date
    if end_date: params["end_date"] = end_date

    response = requests.get(f"{API_BASE}/assessments", params=params)
    response.raise_for_status()
    return response.json()

# Contoh penggunaan
payload = {
    "customer_name": "John Doe",
    "laptop_name": "Lenovo ThinkPad X230",
    "lcd": 80,
    "battery": 75,
    "keyboard": 85,
    "ram": 8,
    "market_price": 3000000,
    "processor_id": 1,
    "use_ai": True
}

result = create_assessment(payload)
print(f"Skor: {result['data']['final_score']}")
print(f"Status: {result['data']['status']}")
print(f"Harga Estimasi: Rp {result['data']['estimated_price']:,}")
```

### 6.4 PHP (Guzzle)

```php
<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://localhost:8000/api',
    'headers'  => ['Content-Type' => 'application/json'],
]);

// Buat penilaian
$response = $client->post('/assessments', [
    'json' => [
        'customer_name' => 'John Doe',
        'laptop_name'   => 'Lenovo ThinkPad X230',
        'lcd'           => 80,
        'battery'       => 75,
        'keyboard'      => 85,
        'ram'           => 8,
        'market_price'  => 3000000,
        'processor_id'  => 1,
    ]
]);

$result = json_decode($response->getBody(), true);
echo "Skor: {$result['data']['final_score']}\n";
echo "Status: {$result['data']['status']}\n";
echo "Harga: Rp " . number_format($result['data']['estimated_price'], 0, ',', '.') . "\n";

// Ambil daftar riwayat
$assessments = $client->get('/assessments', [
    'query' => ['search' => 'John', 'page' => 1]
]);
print_r(json_decode($assessments->getBody(), true));
```

### 6.5 Golang

```go
package main

import (
    "bytes"
    "encoding/json"
    "fmt"
    "net/http"
)

type AssessmentRequest struct {
    CustomerName string `json:"customer_name"`
    LaptopName   string `json:"laptop_name"`
    LCD          int    `json:"lcd"`
    Battery      int    `json:"battery"`
    Keyboard     int    `json:"keyboard"`
    RAM          int    `json:"ram"`
    MarketPrice  int    `json:"market_price"`
    ProcessorID  int    `json:"processor_id"`
    UseAI        bool   `json:"use_ai,omitempty"`
}

type APIResponse struct {
    Status string `json:"status"`
    Data   struct {
        FinalScore     float64 `json:"final_score"`
        Status         string  `json:"status"`
        EstimatedPrice int     `json:"estimated_price"`
    } `json:"data"`
}

func main() {
    payload := AssessmentRequest{
        CustomerName: "John Doe",
        LaptopName:   "Lenovo ThinkPad X230",
        LCD:          80,
        Battery:      75,
        Keyboard:     85,
        RAM:          8,
        MarketPrice:  3000000,
        ProcessorID:  1,
    }

    body, _ := json.Marshal(payload)
    resp, err := http.Post(
        "http://localhost:8000/api/assessments",
        "application/json",
        bytes.NewBuffer(body),
    )
    if err != nil {
        panic(err)
    }
    defer resp.Body.Close()

    var result APIResponse
    json.NewDecoder(resp.Body).Decode(&result)

    fmt.Printf("Skor: %.2f\n", result.Data.FinalScore)
    fmt.Printf("Status: %s\n", result.Data.Status)
    fmt.Printf("Harga Estimasi: Rp %d\n", result.Data.EstimatedPrice)
}
```

### 6.6 Java (Spring Boot — RestTemplate)

```java
import org.springframework.web.client.RestTemplate;
import org.springframework.http.*;

public class BackendServiceClient {

    private final RestTemplate restTemplate = new RestTemplate();
    private final String baseUrl = "http://localhost:8000/api";

    public AssessmentResponse createAssessment(AssessmentRequest request) {
        HttpHeaders headers = new HttpHeaders();
        headers.setContentType(MediaType.APPLICATION_JSON);

        HttpEntity<AssessmentRequest> entity = new HttpEntity<>(request, headers);

        ResponseEntity<AssessmentResponse> response = restTemplate.exchange(
            baseUrl + "/assessments",
            HttpMethod.POST,
            entity,
            AssessmentResponse.class
        );

        return response.getBody();
    }

    // Contoh class DTO
    public static class AssessmentRequest {
        public String customer_name;
        public String laptop_name;
        public int lcd;
        public int battery;
        public int keyboard;
        public double ram;
        public int market_price;
        public int processor_id;
        public boolean use_ai;
    }

    public static class AssessmentResponse {
        public String status;
        public Data data;
        public static class Data {
            public double final_score;
            public String status;
            public int estimated_price;
        }
    }

    public static void main(String[] args) {
        BackendServiceClient client = new BackendServiceClient();

        AssessmentRequest req = new AssessmentRequest();
        req.customer_name = "John Doe";
        req.laptop_name = "Lenovo ThinkPad X230";
        req.lcd = 80;
        req.battery = 75;
        req.keyboard = 85;
        req.ram = 8;
        req.market_price = 3000000;
        req.processor_id = 1;

        AssessmentResponse res = client.createAssessment(req);
        System.out.println("Skor: " + res.data.final_score);
        System.out.println("Status: " + res.data.status);
        System.out.println("Harga: Rp " + res.data.estimated_price);
    }
}
```

### 6.7 cURL (Testing Langsung)

```bash
# 1. Buat penilaian dengan processor_id
curl -X POST http://localhost:8000/api/assessments \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "laptop_name": "Lenovo ThinkPad X230",
    "lcd": 80,
    "battery": 75,
    "keyboard": 85,
    "ram": 8,
    "market_price": 3000000,
    "processor_id": 1,
    "use_ai": true
  }'

# 2. Buat penilaian dengan processor baru
curl -X POST http://localhost:8000/api/assessments \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Jane Doe",
    "laptop_name": "Acer Swift 3",
    "lcd": 90,
    "battery": 60,
    "keyboard": 95,
    "ram": 16,
    "market_price": 5000000,
    "processor_name": "Intel Core i7-1165G7",
    "processor_input": 12345
  }'

# 3. Lihat daftar penilaian
curl "http://localhost:8000/api/assessments?search=Lenovo&page=1"

# 4. Lihat detail
curl http://localhost:8000/api/assessments/1

# 5. Hapus penilaian
curl -X DELETE http://localhost:8000/api/assessments/1
```

---

## 7. Penanganan Error

### 7.1 Format Error Response

**Validation Error (422):**
```json
{
  "message": "lcd harus diantara 0 dan 100.",
  "errors": {
    "lcd": ["lcd harus diantara 0 dan 100."]
  }
}
```

**Internal Server Error (500):**
```json
{
  "status": "error",
  "message": "Gagal memproses penilaian: [detail error]"
}
```

**Not Found (404):**
```json
{
  "message": "Data penilaian tidak ditemukan."
}
```

### 7.2 Daftar Error Umum

| HTTP Code | Penyebab | Solusi |
|-----------|----------|--------|
| 422 | Field required tidak diisi | Cek validasi field |
| 422 | `lcd` tidak dalam range 0–100 | Kirim nilai 0–100 |
| 422 | `processor_id` tidak valid | Pastikan ID ada di tabel `processors` |
| 422 | `processor_id` dan `processor_name` keduanya kosong | Isi salah satu |
| 422 | File gambar > 2MB | Kompres file |
| 422 | Lebih dari 3 file gambar | Kurangi jumlah file |
| 500 | EvaluatorService tidak merespons | Cek koneksi ke EvaluatorService |
| 500 | Database error | Cek koneksi database |

### 7.3 Deteksi Masalah AI

Response selalu menyertakan tiga field untuk membantu debugging AI:

```json
{
  "data": {
    "ai_used": false,
    "description_ignored": true,
    "ai_warning": "Deskripsi tidak mengandung kata kunci laptop, tidak dikirim ke Gemini."
  }
}
```

| Skenario | `ai_used` | `description_ignored` | `ai_warning` |
|----------|-----------|----------------------|--------------|
| AI tidak diminta (`use_ai: false`) | `false` | `false` | `null` |
| Deskripsi tidak relevan | `false` | `true` | Pesan penjelasan |
| Gemini gagal (timeout/error) | `false` | `false` | Pesan error |
| AI berhasil | `true` | `false` | `null` |

### 7.4 Strategi Retry

Jika mendapat error 500 (EvaluatorService timeout), lakukan retry:

```javascript
async function createAssessmentWithRetry(data, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await createAssessment(data);
      return response;
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      console.log(`Retry ${i + 1}/${maxRetries}...`);
      await new Promise(r => setTimeout(r, 1000 * (i + 1)));
    }
  }
}
```

---

## 8. Tips & Troubleshooting

### 8.1 Masalah Koneksi

```
Gejala: Request timeout atau ECONNREFUSED
Solusi:
  - Pastikan BackendService berjalan: docker ps | grep backend
  - Cek port: curl http://localhost:8000/api/assessments
  - Jika di server produksi, ganti localhost dengan domain/IP server
```

### 8.2 Masalah CORS (Khusus Browser)

```
Gejala: "No 'Access-Control-Allow-Origin' header" di browser
Solusi:
  - Pastikan mengakses port yang benar (8000)
  - Coba dari web server (bukan file://)
  - Cek dengan curl -I untuk verifikasi header
```

### 8.3 Skor Selalu Rendah

```
Gejala: final_score selalu di bawah 50 meskipun input tinggi
Kemungkinan:
  - Processor benchmark terlalu rendah → gunakan processor dengan
    benchmark_score > 10000 untuk skor Processor "Tinggi"
  - Input LCD/battery/keyboard di bawah 65 → masuk kategori "buruk"
  - Cek threshold di tabel fuzzy_thresholds (default: 65 dan 85)
```

### 8.4 Perubahan Threshold Tanpa Deploy

Threshold status dapat diubah langsung dari database tanpa deploy ulang:

```sql
UPDATE fuzzy_thresholds SET value = 70.00 WHERE name = 'tidak_layak_batas';
UPDATE fuzzy_thresholds SET value = 90.00 WHERE name = 'layak_batas';
```

Perubahan ini langsung生效 pada penilaian berikutnya.

### 8.5 Best Practices

1. **Gunakan `processor_id` jika memungkinkan** — lebih cepat dan konsisten
2. **Cache daftar processor** — daftar processor jarang berubah, bisa di-cache
3. **Set `use_ai: false` jika tidak perlu narasi** — lebih cepat dan hemat kuota API
4. **Gunakan retry logic** untuk menangani timeout sesaat
5. **Validasi input di sisi klien** sebelum mengirim ke API
6. **Monitor `ai_warning`** — jika sering muncul, periksa API key Gemini

---

## Lampiran: Format Payload ke EvaluatorService

Untuk referensi, berikut format lengkap yang dikirim BackendService ke EvaluatorService:

```json
{
  "input": {
    "LCD": 80,
    "KesehatanBaterai": 75,
    "Processor": 8742,
    "KondisiKeyboard": 85,
    "RAM": 8
  },
  "rules": {
    "fuzzifikasi": {
      "LCD": {
        "buruk": [0, 0, 55, 65],
        "sedang": [55, 65, 75, 85],
        "baik": [75, 85, 100, 100]
      },
      "KondisiKeyboard": {
        "buruk": [0, 0, 55, 65],
        "sedang": [55, 65, 75, 85],
        "baik": [75, 85, 100, 100]
      },
      "RAM": {
        "rendah": [4, 4, 6, 8],
        "sedang": [6, 8, 12],
        "tinggi": [8, 12, 64, 64]
      },
      "KesehatanBaterai": {
        "rendah": [0, 0, 60, 70],
        "sedang": [60, 70, 85],
        "tinggi": [70, 85, 100, 100]
      },
      "Processor": {
        "rendah": [0, 0, 8000, 10000],
        "sedang": [8000, 10000, 18000, 20000],
        "tinggi": [18000, 20000, 64946, 64946]
      }
    },
    "matrix_aturan": [
      { "lcd": "buruk", "keyboard": "buruk", "ram": "rendah", "baterai": "rendah", "processor": "rendah", "output": "tidak_layak" }
    ],
    "defuzzifikasi": {
      "tidak_layak": [0, 0, 55, 65],
      "cukup_layak": [55, 65, 85, 90],
      "layak": [85, 90, 100, 100]
    },
    "thresholds": {
      "tidak_layak_batas": 65.00,
      "layak_batas": 85.00
    }
  }
}
```

---

*Dokumen ini diperbarui pada 24 Juni 2026. Untuk pertanyaan lebih lanjut, hubungi tim pengembang.*
