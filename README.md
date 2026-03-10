# LoanPlatform — Admin Module

## Quick Setup

### Requirements
- PHP 8.2+
- Composer
- MySQL 8+
- Node.js (optional, for assets)

### Steps

```bash
# 1. Install dependencies
composer install

# 2. Copy env and set your DB credentials
cp .env.example .env
# Edit .env: set DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Generate app key
php artisan key:generate

# 4. Run migrations + seed
php artisan migrate --seed

# 5. Start local server
php artisan serve
```

### Login
- URL: http://localhost:8000/admin/login
- Email: admin@loanplatform.com
- Password: Admin@12345

Loan Officer:
- Email: officer@loanplatform.com
- Password: Officer@12345

---

## Module Structure

```
app/
  Http/Controllers/Admin/   ← All admin controllers
  Services/Admin/           ← Business logic services
  Models/                   ← Eloquent models
  Http/Middleware/           ← AdminActive middleware

database/
  migrations/               ← All DB tables
  seeders/AdminSeeder.php   ← Seeds admin users + loan products

resources/views/admin/
  layouts/app.blade.php     ← Master layout + sidebar
  auth/login.blade.php
  dashboard/
  applications/             ← Review, approve, decline
  loans/                    ← Loan management, schedule
  payments/                 ← Payment tracking + verification
  reports/                  ← 7 report types
  users/                    ← User CRUD
  products/                 ← Loan product config
  settings/                 ← System settings (tabs)
  credit/                   ← Credit bureau

routes/admin.php            ← All admin routes (prefixed /admin)
```

## Adding Loan Officer / Borrower Modules Later

When you add borrower/officer modules:
1. Add their routes to `routes/web.php` or new route files
2. Register in `bootstrap/app.php` `withRouting()`
3. Their controllers go in `app/Http/Controllers/Borrower/` etc.
4. Views in `resources/views/borrower/` etc.
5. Use same `users` table — role column differentiates them
6. Add `auth:web` guard for borrower portal (separate from `auth:admin`)

The DB schema is already designed for all 3 roles.
