# 🎰 Gaming Platform — Setup & Configuration Guide

## Phase 1: Initial Setup

### 1. Clone & Install

```bash
git clone -b lite-13 https://github.com/gamingdotme/laravel-social-gaming.git
cd laravel-social-gaming/casino
composer install
npm install && npm run build
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 3. Database Migration

```bash
php artisan migrate --seed
```

### 4. Mail Configuration (Required)

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.your-domain.com
MAIL_PORT=465
MAIL_USERNAME=contact@your-domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="contact@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Phase 2: Stripe Cashier Integration (Real Money)

### 1. Install Cashier

```bash
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

### 2. Configure Stripe Keys in `.env`

Replace with your **live** keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys):

```env
# Cashier (Laravel Cashier standard keys)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
CASHIER_CURRENCY=eur
CASHIER_CURRENCY_LOCALE=fr

# Payment driver config (used by PaymentController & payments.php)
STRIPE_ENABLED=true
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...   # <-- This is the primary key used by PaymentController
```

> **Note:** `STRIPE_SECRET_KEY` (used by `config/payments.php`) and `STRIPE_SECRET` (Cashier standard)
> should both be set to the same `sk_live_...` value to ensure all code paths work correctly.

### 3. Stripe Webhook Setup

1. Go to [Stripe Dashboard → Developers → Webhooks](https://dashboard.stripe.com/webhooks)
2. Add endpoint: `https://votre-domaine.com/stripe/webhook`
3. Select event: `checkout.session.completed`
4. Copy the webhook signing secret to `STRIPE_WEBHOOK_SECRET`

### 4. Deposit Button Usage in Blade Templates

```blade
{{-- Single amount --}}
<a href="{{ route('deposit.checkout', ['userId' => auth()->user()->id, 'amount' => 10]) }}"
   class="btn btn-primary">
    Déposer 10 €
</a>

{{-- Or use the partial --}}
@include('frontend.Minimal.partials.deposit-button', ['amount' => 10])

{{-- Multiple amounts --}}
@include('frontend.Minimal.partials.deposit-button', ['amounts' => [10, 25, 50, 100]])
```

### 5. Routes Available

| Method | URL | Name | Description |
|--------|-----|------|-------------|
| GET | `/deposit/{userId}/{amount}` | `deposit.checkout` | Initiate Stripe checkout |
| GET | `/deposit/success` | `deposit.success` | Payment success page |
| GET | `/deposit/cancel` | `deposit.cancel` | Payment cancelled page |
| POST | `/stripe/webhook` | `stripe.webhook` | Stripe webhook handler |

---

## Phase 3: PWA & Android Packaging

### 1. Install PWA Kit

```bash
composer require devrabiul/laravel-pwa-kit
php artisan vendor:publish --provider="Devrabiul\PwaKit\PwaKitServiceProvider"
```

### 2. Configuration

Edit `config/laravel-pwa-kit.php` to customize:
- App name, colors, icons
- Service worker caching strategies
- Shortcuts

### 3. Icons Required

Place icons in `/minimal/img/icons/`:
- `icon-72x72.png`
- `icon-96x96.png`
- `icon-128x128.png`
- `icon-144x144.png`
- `icon-152x152.png`
- `icon-192x192.png`
- `icon-384x384.png`
- `icon-512x512.png`

### 4. HTTPS Requirement

PWAs require HTTPS. Use Let's Encrypt:
```bash
certbot --nginx -d votre-domaine.com
```

### 5. Android APK Generation

Option A — Chrome Beta (WebAPK):
1. Open the app in Chrome Beta on Android
2. Tap "Add to Home Screen"
3. Chrome generates a WebAPK automatically

Option B — PWABuilder:
1. Visit [pwabuilder.com](https://www.pwabuilder.com)
2. Enter your HTTPS URL
3. Download the signed APK

---

## Phase 4: Production Security

### 1. Production `.env` Settings

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
LOG_LEVEL=error
DISABLE_DEMO_ACCOUNTS=true
DISABLE_TEST_PAYMENTS=true
```

### 2. Rate Limiting

Already configured in `app/Http/Kernel.php`:
- Web routes: 120 req/min
- API routes: 60 req/min
- Deposit routes: 10 req/min

### 3. Stripe Live Mode

- Replace all `pk_test_` / `sk_test_` keys with `pk_live_` / `sk_live_`
- Verify webhook endpoint is using live webhook secret

### 4. Disable Demo Seeders

Remove or comment out demo seeders in `database/seeders/`:
```bash
# Do NOT run in production:
# php artisan db:seed --class=DemoSeeder
```

### 5. Security Headers

Configured in `config/security.php`:
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- Strict-Transport-Security (HSTS)
- Content Security Policy

### 6. Storage & Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

---

## Audit & Corrections (Post-Deploy)

The following bugs were identified and fixed during the production audit:

| # | File | Bug | Fix |
|---|------|-----|-----|
| 1 | `app/Models/User.php` | Wrong namespace `App\Models` — project uses `VanguardLTE` | Made it extend `VanguardLTE\User` for Cashier compatibility |
| 2 | `routes/web.php` | `/deposit/{userId}/{amount}` captured `/deposit/success` and `/deposit/cancel` | Moved static routes before dynamic route |
| 3 | `app/Http/Controllers/PaymentController.php` | `env('STRIPE_SECRET')` fallback didn't match `STRIPE_SECRET_KEY` | Added both fallbacks: `STRIPE_SECRET_KEY` then `STRIPE_SECRET` |
| 4 | `app/Http/Middleware/VerifyCsrfToken.php` | `register` without leading slash may not match | Added `/register` alongside `register` |
| 5 | `app/Http/Kernel.php` | Throttle aliases used invalid `:5,1` syntax in class string | Removed parameters from alias (pass at route level) |
| 6 | `app/Http/Controllers/PaymentController.php` | `transactions` insert used `type` column (doesn't exist) and missed `direction` (NOT NULL) | Fixed to use correct schema: `direction='add'`, `balance_before`, `balance_after` |
| 7 | `app/Http/Controllers/PaymentController.php` | Race condition: balance read after increment | Used `lockForUpdate()` to read balance before increment inside transaction |

---

## Files Modified / Created

| File | Change |
|------|--------|
| `casino/.env` | Production config + Stripe + Mail |
| `casino/composer.json` | Added `laravel/cashier` + `devrabiul/laravel-pwa-kit` |
| `casino/app/Models/User.php` | Added `Billable` trait |
| `casino/app/Http/Controllers/PaymentController.php` | **NEW** — Stripe checkout + webhook |
| `casino/app/Http/Kernel.php` | Added throttle middleware |
| `casino/app/Http/Middleware/VerifyCsrfToken.php` | Excluded Stripe webhooks |
| `casino/routes/web.php` | Added deposit + webhook + offline routes |
| `casino/config/laravel-pwa-kit.php` | **NEW** — PWA manifest config |
| `casino/config/security.php` | **NEW** — Production security config |
| `casino/resources/views/frontend/Minimal/layouts/app.blade.php` | PWA meta tags + SW registration |
| `casino/resources/views/payment/success.blade.php` | **NEW** — Payment success view |
| `casino/resources/views/payment/cancel.blade.php` | **NEW** — Payment cancel view |
| `casino/resources/views/frontend/Minimal/pages/offline.blade.php` | **NEW** — PWA offline page |
| `casino/resources/views/frontend/Minimal/partials/deposit-button.blade.php` | **NEW** — Reusable deposit button |
| `manifest.json` | **NEW** — PWA Web App Manifest |
| `sw.js` | **NEW** — Service Worker |
