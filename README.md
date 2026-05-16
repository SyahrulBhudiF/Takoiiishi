# Takoyaki

Laravel + Filament admin app for takoyaki outlet operations: users, roles, outlets, ingredients, purchases, stock, distributions, sales, imports/exports, and dashboard reports.

## Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL 8+ or MariaDB

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Default `.env.example` uses MySQL and database-backed sessions/cache/queue:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=takoyaki
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Create database first:

```sql
CREATE DATABASE takoyaki CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run migrations and seed demo users/data:

```bash
php artisan migrate --seed
```

Build frontend assets:

```bash
npm run build
```

## Run locally

Recommended: run all dev processes together:

```bash
composer run dev
```

This starts:

- Laravel server: `php artisan serve`
- Queue worker: `php artisan queue:listen --tries=1 --timeout=0`
- Logs: `php artisan pail --timeout=0`
- Vite dev server: `npm run dev`

Open:

```text
http://localhost:8000/admin
```

Alternative, separate terminals:

```bash
php artisan serve
npm run dev
php artisan queue:work --tries=1 --timeout=0
php artisan pail --timeout=0
```

## Queue

Project uses Laravel database queue:

```env
QUEUE_CONNECTION=database
```

Queue tables come from `database/migrations/0001_01_01_000002_create_jobs_table.php`:

- `jobs` queued jobs
- `job_batches` batch metadata
- `failed_jobs` failed job records

Run worker in development:

```bash
php artisan queue:work --tries=1 --timeout=0
```

For local all-in-one development, prefer:

```bash
composer run dev
```

Queue matters for background work such as Filament Excel imports/exports and any queued Laravel jobs.

## Demo login

Seeded users use password:

```text
password
```

Accounts:

| Role | Email | Username |
| --- | --- | --- |
| Admin Pusat | `admin.pusat@takoyaki.test` | `admin_pusat` |
| Admin Cabang | `admin.cabang@takoyaki.test` | `admin_cabang` |
| Pemilik Pusat | `pemilik.pusat@takoyaki.test` | `pemilik_pusat` |
| Pemilik Cabang | `pemilik.cabang@takoyaki.test` | `pemilik_cabang` |

## Useful commands

```bash
# Fresh database + seed
php artisan migrate:fresh --seed

# Run tests
composer test

# Format PHP
vendor/bin/pint

# Production-ish asset build
npm run build
```

## Database migrations

Migrations use UUID primary keys for domain models. Main tables:

### Laravel/system tables

- `users` — app users with UUID `id`, `email`, `username`, `role`, optional `outlet_id`.
- `password_reset_tokens` — password reset tokens.
- `sessions` — database session storage.
- `cache`, `cache_locks` — database cache storage.
- `jobs`, `job_batches`, `failed_jobs` — database queue storage.
- `notifications` — Laravel notification storage; follow-up migration changes `notifiable_id` to UUID.

### Permissions/RBAC

From Filament Shield / Spatie Permission:

- `permissions`
- `roles`
- `model_has_permissions`
- `model_has_roles`
- `role_has_permissions`

Roles seeded:

- `admin_pusat`
- `admin_cabang`
- `pemilik_pusat`
- `pemilik_cabang`

### Business tables

- `outlets` — outlets/branches. Fields: `name`, `address`, `type` (`pusat`/`cabang`).
- `ingredients` — inventory ingredients. Fields: `name`, `unit`, `minimum_stock`, `usage_per_portion`.
- `purchases` — purchase header. Fields: `purchase_date`, `created_by`, `total`.
- `purchase_items` — purchase lines. Links purchase + ingredient, stores `quantity`, `price`, `subtotal`.
- `distributions` — stock transfer header. Links `from_outlet_id`, `to_outlet_id`, `created_by`.
- `distribution_items` — transfer lines. Links distribution + ingredient, stores `quantity`.
- `stocks` — current stock per outlet + ingredient. Unique key: `outlet_id`, `ingredient_id`.
- `stock_movements` — stock ledger. Links outlet + ingredient, stores movement `type`, `qty_in`, `qty_out`, optional `reference`.
- `sales` — sales records. Links outlet + user, stores `sale_date` and `portion_qty`.

### Import/export tables

Created for Filament Excel:

- `imports` — import progress/status per user.
- `exports` — export progress/status per user.
- `failed_import_rows` — failed rows + validation errors for imports.

## Data flow summary

- Purchase adds stock to pusat outlet and creates `stock_movements`.
- Distribution subtracts stock from source outlet, adds stock to destination outlet, and logs movements.
- Sale subtracts ingredient stock based on each ingredient's `usage_per_portion` and sale `portion_qty`.
- Dashboard reads sales, stock, low-stock ingredients, purchases, distributions, and movements.
