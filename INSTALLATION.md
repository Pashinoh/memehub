# Installation Guide

Panduan instalasi MemeHub secara lokal.

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+ dan npm
- MySQL atau MariaDB

## 1. Clone Repository

```bash
git clone https://github.com/Pashinoh/memehub.git
cd memehub
```

## 2. Install Dependencies

```bash
composer install
npm install
```

## 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

## 4. Configure `.env`

Minimal sesuaikan:

- `APP_URL`
- `APP_VERSION` (default `1.3.0`)
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI` (default: `${APP_URL}/auth/google/callback`)
- `ADMIN_EMAILS` (contoh: `ADMIN_EMAILS=admin@gmail.com`)
- `TURNSTILE_SITE_KEY` dan `TURNSTILE_SECRET_KEY` (opsional)

## 5. Migrate and Build

```bash
php artisan migrate
npm run build
```

## 6. Run App

```bash
php artisan serve
```

## Optional: Production Optimization

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```
