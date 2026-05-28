# Phase 0 — Local setup

## Environment

Copy `.env.example` to `.env` if needed, then set:

```env
APP_URL=https://elearning.local
ADMIN_DOMAIN=admin.elearning.local
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
```

Copy `.env.example` to `.env`, then run `php artisan key:generate` if `APP_KEY` is empty (missing key causes a 500 error).

Vhosts must point both hosts at the project's `public/` directory.

## Install and migrate

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev
```

## Demo accounts (password: `password`)

| Email | Role | Portal |
|-------|------|--------|
| student@elearning.local | student | https://elearning.local |
| instructor@elearning.local | instructor | https://admin.elearning.local |
| admin@elearning.local | admin | https://admin.elearning.local |
| super@elearning.local | super_admin | https://admin.elearning.local |

## URLs

- Student home: `https://elearning.local/`
- Student login: `https://elearning.local/login`
- Student register: `https://elearning.local/register`
- Staff login: `https://admin.elearning.local/login`
- Staff dashboard: `https://admin.elearning.local/dashboard`

## HTTPS notes

- Laravel forces `https` scheme when `APP_URL` starts with `https://` or `FORCE_HTTPS=true`.
- Trust proxies is enabled for TLS-terminated local vhosts.
- Browser “Not Secure” on a self-signed cert is normal for local dev; trust the cert in your OS/keychain or use Valet/Herd.
