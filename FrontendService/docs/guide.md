# LapiCheck Frontend Guide

Panduan singkat pengembangan antarmuka (UI) **LapiCheck** berbasis **Vue 3, Vite, & TypeScript**.

---

## 1. Menjalankan Aplikasi

1. **Instalasi Dependensi:**
   ```bash
   npm install
   ```
2. **Konfigurasi API (.env.local):**
   ```env
   VITE_BASE_URL=http://localhost:8000/
   ```
3. **Jalankan Aplikasi:**
   ```bash
   npm run dev
   ```
   _Aplikasi dapat diakses di: `http://localhost:5173/`_

---

## 2. Struktur Proyek Utama

- `src/views/assessments/index.vue`: Formulir input penilaian & visualisasi skor/AI instan.
- `src/views/assessments/history.vue`: Tabel riwayat & modal detail penilaian.
- `src/services/evaluation.ts`: Penghubung request/response API.
- `src/constants/assessment.ts`: Konfigurasi pilihan dropdown, mata uang, dan aturan metrik.
- `src/utils/assessment.ts`: Utilitas format nilai dan pewarnaan hasil fuzzy.
- `src/composables/useApi.ts`: Konfigurasi basis Axios client.

---

## 3. Alur UX Halaman Utama

1. **Dropdown Component State:** Placeholder menggunakan inisialisasi string kosong `""` agar tampilan visual seragam dan validasi browser bekerja otomatis.
2. **Konfigurasi Terpusat:** Opsi LCD, keyboard, mata uang, dan aturan fuzzy berada di `src/constants/assessment.ts` agar konsisten dan mudah dirawat.
3. **Smooth Scrolling:** Menggunakan `scrollIntoView({ behavior: 'smooth' })` untuk mengarahkan pengguna ke visualisasi laporan hasil secara otomatis saat kalkulasi sukses.
4. **Konfirmasi Aksi:** Menggunakan library **SweetAlert2** untuk visualisasi alert estimasi dan modal konfirmasi hapus riwayat.

---

_LapiCheck Frontend Guide (Concise)._
