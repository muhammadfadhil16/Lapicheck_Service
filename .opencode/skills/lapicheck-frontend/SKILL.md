---
name: lapicheck-frontend
description: Use for frontend development tasks in LapiCheck. Covers Vue.js 3 components, Vue Router, composables, Axios API integration, Vite build, Cloudflare Pages deployment, SPA routing, and type definitions. Use when the user mentions "frontend", "vue", "component", "router", "halaman", "axios", "api call", "composable", "vite", "build", "cloudflare pages", or "spa".
---

# LapiCheck Frontend

## Tech Stack

| Technology | Usage |
|------------|-------|
| Vue.js 3 | Framework with `<script setup lang="ts">` |
| Vue Router 4 | Client-side routing (`createWebHistory`) |
| Axios | HTTP client |
| Vite 5 | Build tool |
| Tailwind CSS 3 | Utility CSS (PostCSS build-time) |
| TypeScript 6 | Type safety |
| html2canvas + jspdf | PDF generation |
| SweetAlert2 | Modals & alerts |

## Project Structure

```
FrontendService/src/
├── assets/css/
│   ├── app.css          # Global imports + animations
│   ├── tailwind.css      # @tailwind directives
│   └── lyp.css           # Custom styles
├── components/           # Reusable Vue components
├── composables/
│   └── useApi.ts         # Axios instance, base URL, error handling, getImageUrl
├── constants/
│   └── assessment.ts     # Types: Processor, criteria scores, etc.
├── layouts/
│   └── app-layout.vue    # Main layout wrapper
├── router/
│   └── index.ts          # Routes: / (home), /history
├── services/
│   └── evaluation.ts     # API service functions
├── utils/                # Utility functions
├── views/assessments/    # Page components
│   ├── index.vue         # Home — create evaluation form
│   └── history.vue       # History — list past assessments
├── App.vue               # Root component
└── main.ts               # App entry point
```

## Routes

```ts
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/',          name: 'home',    component: FuzzyIndex },
    { path: '/history',   name: 'history', component: () => import('...') },
  ],
})
```

## API Integration

### `useApi()` composable (`src/composables/useApi.ts`)

```ts
export const BASE_URL = import.meta.env.VITE_BASE_URL || ''
```

- Creates Axios instance with `baseURL: BASE_URL`
- Error interceptor handles: 422 (validation), 404, 500, 413, 429
- Returns `{ api }`

### `evaluationService()` (`src/services/evaluation.ts`)

All calls use relative paths (e.g., `api.get('/api/processors')`).
When `VITE_BASE_URL` is set, these become absolute URLs.

### Functions

| Function | Method | Path | Params |
|----------|--------|------|--------|
| `getProcessors()` | GET | `/api/processors` | — |
| `evaluate(data)` | POST | `/api/assessments` | FormData (multipart) |
| `getAllAssessments(page, filters?)` | GET | `/api/assessments?page=&search=&start_date=&end_date=` | Query params |
| `getAssessmentById(id)` | GET | `/api/assessments/{id}` | — |
| `deleteAssessment(id)` | DELETE | `/api/assessments/{id}` | — |

### Image URLs

```ts
export const getImageUrl = (path: string | null | undefined): string => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `/storage/${cleanPath}`  // relative to origin
}
```

When backend is on a different domain, update `VITE_BASE_URL` and `getImageUrl` must use the full API URL.

## Environment Variables

| File | VITE_BASE_URL | Usage |
|------|--------------|-------|
| `.env.local` | `http://localhost:8000/` | Local development |
| `.env.production` | (empty) | Cloudflare Pages with relative API calls |

## Build & Deploy

```bash
# Dev server (with proxy to backend)
cd FrontendService && npm run dev

# Production build
cd FrontendService && npm run build-only
# Output: FrontendService/dist/

# Type-check only
cd FrontendService && npx vue-tsc --build
```

### Cloudflare Pages Settings

| Setting | Value |
|---------|-------|
| Build command | `cd FrontendService && npm install && npm run build-only` |
| Output directory | `FrontendService/dist` |
| Node version | 22 |

### SPA Routing (`public/_redirects`)

```
/*    /index.html    200
```

## Component Conventions

- All components use `<script setup lang="ts">`
- Imports use `@/` alias (maps to `src/`)
- Template uses Tailwind semantic color tokens
- Actions use SweetAlert2 for confirmations
- Forms create `FormData` for multipart uploads
