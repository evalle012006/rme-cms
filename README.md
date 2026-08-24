# RateMyEstate
#test
Monorepo for the RateMyEstate platform — a property review site built for Castle Technology Solutions.

## Repository Structure

```
rate-my-estate-cms/
├── backend/   # Laravel 6 — public site + REST API
├── admin/     # Angular 9 — legacy admin panel (active, being phased out)
├── cms/       # React 18 — new admin portal (in development)
└── deploy.sh  # Production deploy script
```

## Live URLs

| App | URL |
|---|---|
| Public site | https://ratemyestate.castledigital.com.au |
| Admin (Angular — legacy) | https://ratemyestatecms.castledigital.com.au |
| Admin (React — new) | https://ratemyestatecms-react.castledigital.com.au |

---

## Backend (`backend/`)

Laravel 6 application serving both the public-facing website and the REST API consumed by the admin panels.

| Concern | Detail |
|---|---|
| Framework | Laravel 6 (PHP 7.4) |
| Auth | Laravel Passport (Bearer tokens) |
| Permissions | Spatie Laravel Permission v3 |
| Database | MySQL 5.7 |
| File Storage | AWS S3 (via Flysystem v1) |
| Image Processing | Intervention Image v2 |
| Excel Export | Maatwebsite Excel v3 |
| Slugs | Eloquent Sluggable v6 |
| View Tracking | Eloquent Viewable v4 |
| Social Auth | Laravel Socialite v4 |
| Log Viewer | Arcanedev Log Viewer v5 |

### Run locally

```bash
cd backend
php artisan serve
# → http://localhost:8000
```

MySQL must be running first. Default admin credentials (seeded): `admin@gmail.com` / `admin123`.

---

## React Admin Portal (`cms/`)

New admin panel replacing the Angular 9 panel. Currently in active development.

| Concern | Library |
|---|---|
| Framework | React 18 + Vite 8 |
| Language | TypeScript (strict) |
| Routing | React Router v6 |
| State | Zustand |
| Forms | React Hook Form + Zod |
| Tables | TanStack Table v8 |
| UI | Tailwind CSS v4 |
| Charts | Recharts |
| File Upload | react-dropzone |
| HTTP | Axios with interceptors |
| Toast | Sonner |

### Run locally

```bash
cd cms
nvm use        # uses .nvmrc — Node 24.x
npm install
cp .env.example .env.local
npm run dev
# → http://localhost:4221
```

Requires the Laravel backend running on `http://localhost:8000`.

---

## Local Development (all three together)

Three terminal tabs:

```bash
# Tab 1 — Backend
cd backend && php artisan serve

# Tab 2 — Angular admin (legacy)
cd admin && nvm use 16 && npm run start

# Tab 3 — React admin (new)
cd cms && nvm use && npm run dev
```

---

## Deploy (production)

```bash
# Standard deploy — preserves database
./deploy.sh

# First deploy — runs migrations and all seeders
./deploy.sh --first

# Skip Angular build (React iteration only)
./deploy.sh --skip-angular

# Skip React build (backend/Angular iteration only)
./deploy.sh --skip-react
```

> ⚠️ The MySQL Docker volume is never wiped by the deploy script. See `deploy.sh` comments for manual reset instructions.

---

## Infrastructure

- **Server:** DigitalOcean Ubuntu droplet
- **Containers:** Docker (PHP-FPM + Nginx + MySQL 5.7)
- **SSL:** Certbot / Let's Encrypt
- **Host Nginx:** Reverse proxies domains to container ports (8000, 8001, 8002)