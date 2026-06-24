# LapiCheck Assessments API Specs

Spesifikasi teknis interface data dan integrasi API untuk modul penilaian kelayakan (_assessments_).

---

## 1. TypeScript Interfaces (`src/services/evaluation.ts`)

### Request Payload (`EvaluationInput`)

```typescript
export interface EvaluationInput {
  customer_name: string
  laptop_name: string
  price: number
  lcd_score: number
  keyboard_score: number
  ram_capacity: number
  battery_health: number
  processor_id?: number
  processor_name?: string
  processor_input?: number
  images?: File[]
  description?: string
  use_ai?: boolean
}
```

**Mapping ke Backend API:**
| Frontend Field | API Field | Keterangan |
|----------------|-----------|------------|
| `price` | `market_price` | Harga pasar |
| `lcd_score` | `lcd` | Skor LCD (0-100) |
| `keyboard_score` | `keyboard` | Skor keyboard (0-100) |
| `ram_capacity` | `ram` | Kapasitas RAM (GB) |
| `battery_health` | `battery` | Kesehatan baterai (0-100) |

### Response Payload (`EvaluationData`)

```typescript
export interface EvaluationData {
  id: number
  customer_name: string
  laptop_name: string
  final_score: number
  status: 'Tidak Layak' | 'Cukup Layak' | 'Layak'
  estimated_price: number
  market_price?: number | null
  images: EvaluationImage[]
  ai_conclusion: string
  lcd_input?: number
  battery_input?: number
  processor_input?: number
  keyboard_input?: number
  ram_input?: number
  description?: string
  created_at: string
  processor?: ProcessorRelation
  description_ignored?: boolean
  ai_warning?: string | null
  ai_used?: boolean
}
```

---

## 2. API Service Methods (`evaluationService()`)

Semua metode memanggil endpoint core backend (`http://localhost:8000/api/...`):

| Metode Service            | HTTP Request                   | Content-Type       | Kegunaan                                                             |
| :------------------------ | :----------------------------- | :----------------- | :------------------------------------------------------------------- |
| `evaluate(data)`          | `POST /api/assessments`        | multipart/form-data| Mengirim data laptop + gambar, menghitung fuzzy, memicu AI, menyimpan.|
| `getAllAssessments(page)` | `GET /api/assessments`         | -                  | Mengambil data riwayat berhalaman (paginated) untuk tabel histori.   |
| `getAssessmentById(id)`   | `GET /api/assessments/{id}`    | -                  | Mengambil spesifikasi detail satu laptop untuk modal detail riwayat. |
| `deleteAssessment(id)`    | `DELETE /api/assessments/{id}` | -                  | Menghapus satu data penilaian dari database.                         |

---

_LapiCheck Assessments Specs — Diperbarui 20 Juni 2026._
