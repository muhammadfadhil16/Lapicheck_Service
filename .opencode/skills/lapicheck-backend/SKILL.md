---
name: lapicheck-backend
description: Use for backend tasks in LapiCheck. Covers Laravel (BackendService & EvaluatorService), API routes, controllers, database, MySQL, fuzzy logic evaluation, Docker deployment, Cloudflare Tunnel, Nginx, and server config. Use when the user mentions "backend", "api", "laravel", "database", "migration", "docker", "deploy", "server", "vps", "nginx", "cloudflare tunnel", "evaluator", "fuzzy", or "artisan".
---

# LapiCheck Backend

## Services

| Service | Path | Port | Role |
|---------|------|------|------|
| BackendService | `BackendService/` | 8000 | Main API (Laravel 12, PHP 8.4) |
| EvaluatorService | `EvaluatorService/` | 8001 (internal) | Fuzzy logic evaluation (Laravel 12) |
| MySQL | Docker (db service) | 3306 (internal) | Database |
| Nginx | Docker (nginx service) | 80 | Reverse proxy |

## API Routes

### BackendService (`/api/*`)

| Method | Path | Controller | Notes |
|--------|------|------------|-------|
| GET | `/api/laptop-brands` | LaptopController@brands | List active brands with laptop counts |
| POST | `/api/laptop-brands` | LaptopController@storeBrand | Create or restore a brand |
| PUT | `/api/laptop-brands/{brand}` | LaptopController@updateBrand | Rename a brand |
| DELETE | `/api/laptop-brands/{brand}` | LaptopController@destroyBrand | Soft delete brand without active laptops |
| GET | `/api/laptops?brand_id=` | LaptopController@index | List active laptop models, optionally filtered by brand |
| POST | `/api/laptops` | LaptopController@store | Create laptop with processor data |
| PUT | `/api/laptops/{laptop}` | LaptopController@update | Update laptop data |
| DELETE | `/api/laptops/{laptop}` | LaptopController@destroy | Soft delete laptop |
| GET | `/api/assessments` | AssessmentController@index | List with pagination & filters |
| POST | `/api/assessments` | AssessmentController@store | Create assessment (multipart) |
| GET | `/api/assessments/{id}` | AssessmentController@show | Detail assessment |
| DELETE | `/api/assessments/{id}` | AssessmentController@destroy | Delete assessment |

### EvaluatorService (internal only)

| Method | Path | Notes |
|--------|------|-------|
| POST | `/api/evaluator` | Called by BackendService internally |

## Domain Data Rules

- Processor master data is embedded in `laptops`; do not add new application flow dependencies on the old `processors` table.
- `Laptop` and `LaptopBrand` use Eloquent `SoftDeletes`.
- Active laptop lists exclude soft-deleted records by default.
- Assessment relations load soft-deleted laptops for historical reports.
- A brand cannot be archived while it still has active laptop models.

## Internal Service Communication

`BackendService/app/Services/External/EvaluatorService.php` calls EvaluatorService:
```
BackendService → POST http://evaluator/api/evaluator
```

Environment: `EVALUATOR_SERVICE_URL=http://evaluator` in BackendService `.env`.

## Database

- **Name:** `manajemen_data_fuzzy`
- **Host:** `db` (Docker service name, internal)
- **Port:** 3306
- **Auth:** root with empty password (configurable)

## Docker Architecture

```yaml
services:
  db:        # mysql:8.0, port 3307:3306
  backend:   # PHP 8.4 Apache, port 8000:80
  evaluator: # PHP 8.4 Apache, port 8001:80
  nginx:     # nginx:alpine, port 80:80 (reverse proxy)
```

### Dockerfile Pattern (both services)
```
FROM php:8.4-apache
→ Install libzip, zip, unzip
→ docker-php-ext-install pdo_mysql zip
→ a2enmod rewrite
→ Set APACHE_DOCUMENT_ROOT to /var/www/html/public
```

## Nginx Config (`nginx-prod.conf`)

Used in Docker deployment:
- `/` → serves frontend `dist/`
- `/api/*` → reverse proxy to `backend:80`
- `/storage/*` → reverse proxy to `backend:80`

## Deployment

### Production
```
VPS Docker:
  cloudflared tunnel → api.domain.com → nginx → BackendService
```

### Cloudflare Tunnel (`cloudflared-config.yml`)
```yaml
tunnel: lapicheck
ingress:
  - hostname: fadhil.snappie.my.id
    service: http://localhost:80
  - service: http_status:404
```

## Common Artisan Commands

```bash
# Inside the backend container:
docker exec Backend-Service php artisan migrate
docker exec Backend-Service php artisan db:seed
docker exec Backend-Service php artisan cache:clear
docker exec Backend-Service php artisan route:list

# Without Docker (local dev):
cd BackendService && php artisan serve
```
