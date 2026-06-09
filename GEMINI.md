# GEMINI.md

This file is the Gemini Code entry point for this repo. It outlines the system architecture, stack, code quality standards, and developer guidelines for building the **Sistem Penilaian Kelayakan Laptop Bekas (LapiCheck)**.

## Stack recap

- **BackendService**:
  - Framework: Laravel 12, PHP 8.2+
  - Database: MySQL 8 (Port 3307 on host, `db:3306` inside Docker, database name `manajemen_data_fuzzy`)
  - Integration: Google Gemini AI (`gemini-2.5-flash` model via REST API for narrative recommendations)
  - Connectivity: Inter-service HTTP calls to `EvaluatorService` (via `http://evaluator` inside Docker)
  - Testing: PHPUnit
- **EvaluatorService**:
  - Framework: Laravel 12, PHP 8.2+
  - Characteristics: Stateless microservice, no database connection
  - Logic: Fuzzy Logic Mamdani (Fuzzification, MIN-MAX Inference, Centroid of Area Defuzzification)
  - Testing: PHPUnit
- **FrontendService**:
  - Tech Stack: Vue 3 (Composition API) + Vite + TypeScript + Tailwind CSS
  - API Client: Axios
  - Libraries: SweetAlert2 (alerts/confirmations), `vue3-perfect-scrollbar` (custom scrollbars), `jspdf` & `html2canvas` (client-side PDF generation)

## Operational Guide

- **Start Services**: `docker compose up -d`
- **Stop Services**: `docker compose down`
- **Reset Database**: `docker compose down -v` then `docker compose up -d`
- **Run migrations & seeders**: `docker compose exec backend php artisan migrate:fresh --seed`
- **Run Backend Tests**: `docker compose exec backend php artisan test`
- **Run Evaluator Tests**: `docker compose exec evaluator php artisan test`
- **Run Frontend locally**: Navigate to `FrontendService` and run `npm run dev`

## Code quality bar

Every change — new code and edits to existing code — must hold to these principles. When a principle and "match existing style" conflict, prefer the principle for new/changed code, but don't do drive-by rewrites of unrelated surrounding code.

### SOLID
- **S**ingle Responsibility: A class, service, controller, or helper does one thing. Adhere to the established controller-service pattern in `BackendService` (e.g., keep controllers thin and move fuzzy orchestration/AI prompting to services). Keep `EvaluatorService` strictly stateless (no local database or state).
- **O**pen/Closed: Prefer adding new behavior (e.g., adding membership function curves or new API parameters) via new methods/classes over editing working logic in risky ways.
- **L**iskov Substitution: Implementations of the same interface (like custom fuzzy membership functions or API client wrappers) must be interchangeable without surprising the caller.
- **I**nterface Segregation: Keep API payloads and service method signatures narrow and purposeful.
- **D**ependency Inversion: Controllers depend on service classes or helpers, not on direct Eloquent models/DB calls for complex logic. Use dependency injection where possible.

### DRY (Don't Repeat Yourself)
- Before writing new logic, check `BackendService/app/Services/` for existing clients, and `FrontendService/src/services/` or `FrontendService/src/utils/` for existing API/formatting helpers.
- Extract shared logic (e.g., membership evaluation formulas, formatting prices, or custom styling rules) only when there are real duplicated rules (≥2-3 call sites) — don't pre-emptively abstract.

### KISS (Keep It Simple)
- Prefer the straightforward solution that solves the actual requirement over a clever or generalized one.
- Use guard clauses and early returns over deep nesting (especially in nested fuzzy logic loops).
- Target ~≤80 lines per function/method; if it's longer, extract helpers.
- No speculative features, database columns, or complex configurations that don't exist in the PRD.

### No spaghetti code
- Keep a clear, traceable flow: Request -> Validation -> Controller -> Service -> External Calls (Evaluator/Gemini) -> Response.
- Avoid hidden control flow via shared mutable globals, deep callback chains, or cross-service side-effects not visible at the call site.
- Avoid circular dependencies between modules or Vue components.

### No code smells
- Watch for and fix: long parameter lists, duplicate code blocks, magic numbers/strings (use configuration files, environment variables, or PHP class constants), god objects, mixing concerns (e.g., raw SQL strings in controllers, DB connections in `EvaluatorService`, business logic inside Vue views).

### Avoid N+1 (Laravel Eager Loading)
- Never issue one database query (or HTTP/AI call) per loop iteration.
- In Laravel, use eager loading (e.g., `$assessment->load('processor', 'images')`) when fetching collections to prevent N+1 queries.
- Do not make HTTP requests to the `EvaluatorService` or Gemini AI inside a loop; batch or optimize transactions.

### Scalability
- Add pagination (`paginate()`) to listing endpoints like `GET /api/assessments` and avoid fetching all records with `all()` or `get()`.
- Avoid `SELECT *` on growing tables; select only required columns where possible.
- Pay attention to memory usage when performing disk-based sampling for defuzzification in `EvaluatorService`.

## Reminders worth repeating

- **SQL Injection Prevention**: Always use Eloquent ORM or parameterized bindings (`DB::raw` with bindings or `where` clauses) — never concatenate raw user inputs into SQL.
- **API Inter-service Communication**:
  - BackendService MUST call EvaluatorService via `http://evaluator` (or configured `FUZZY_SERVICE_URL`).
  - FrontendService MUST call BackendService only. It should NEVER call `EvaluatorService` directly.
- **File Upload & Handling**:
  - Image uploads from Frontend use native `FormData` (`multipart/form-data`) to send the `images[]` file array.
  - Enforce limits: Max 3 images, max 2MB per image, formats: `.jpg`, `.jpeg`, `.png`. Keep validation on both client and server side.
- **Timezone Management**:
  - Database stores `created_at` in UTC.
  - Date filters from Frontend (entered in WIB/Asia/Jakarta timezone) must be parsed via Carbon and converted to UTC (`startOfDay()->setTimezone('UTC')` and `endOfDay()->setTimezone('UTC')`) before querying the database.
- **Gemini AI Integration**:
  - Model: `gemini-2.5-flash`.
  - Prompts must be objective, factual, third-person perspective, and in Bahasa Indonesia.
  - Gemini responses must be sanitized (removing markdown, repetitive characters, headers).
  - Check description relevance: If description does not contain laptop-related terms, ignore it (`description_ignored = true`) and do not send it to Gemini.
  - Fallback: If the API key is missing or calls fail, return `'tidak ada catatan tambahan'` (no static fallback from PHP).
- **Destructive Actions**:
  - All deletion or irreversible actions in Vue UI must go through SweetAlert2 confirmation, never standard `window.confirm`.
- **Language Convention**:
  - Code, comments, class/method names, and commit messages must be in English.
  - User-facing UI strings and AI-generated text must be in Bahasa Indonesia.
- **Secrets Security**:
  - Never commit `.env` files, API keys, or tokens. Never log secrets in application logs.
