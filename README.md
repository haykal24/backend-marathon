# Backend - Marathon Platform

REST API Backend menggunakan **Laravel 12** dengan **Filament 4** admin panel.

## 🚀 Quick Start

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

## 📦 API Endpoints

- **Base URL:** `https://api.yourdomain.com/api`
- **Admin Panel:** `https://api.yourdomain.com/admin`

### Key Routes
- `GET /events` - List events
- `POST /events` - Create event (requires auth)
- `GET /events/{slug}` - Event detail
- `POST /otp/request` - Request OTP
- `POST /otp/verify` - Verify OTP

## 🔐 Authentication

Menggunakan **Laravel Sanctum** untuk API token authentication setelah WA OTP verification.

```bash
# Get token:
POST /api/otp/request
{
  "phone_number": "0812XXXXXXXX"
}

# Verify OTP:
POST /api/otp/verify
{
  "phone_number": "0812XXXXXXXX",
  "code": "123456"
}
```

## 🐳 Docker (Optional)

```bash
# Build image
docker build -t marathon-api .

# Run container
docker run -p 8000:8000 marathon-api
```

## 🚀 Deployment

### Automated (GitHub Actions)
Push ke branch `master` akan auto-deploy ke cPanel via FTP.

**Requirements:**
- Set GitHub Secrets:
  - `CPANEL_FTP_HOST`
  - `BACKEND_FTP_USER`
  - `BACKEND_FTP_PASS`
  - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

### Manual
```bash
# 1. Install composer deps
composer install --no-dev --optimize-autoloader

# 2. Generate key
php artisan key:generate

# 3. Upload via FTP (exclude vendor, .env, tests)

# 4. SSH ke server & run:
php artisan migrate --force
php artisan optimize
```

## 📝 Key Files

- `routes/api.php` - API routes
- `app/Http/Controllers/Api/V1/` - Controllers
- `app/Models/` - Database models
- `database/migrations/` - Migrations
- `.env.example` - Environment template

## 📚 Documentation

- Laravel: https://laravel.com/docs/12.x
- Filament: https://filamentphp.com
- API: Docs available at `/api/docs`
