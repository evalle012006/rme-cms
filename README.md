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

## Staging Live URLs

| App | URL |
|---|---|
| Public site | https://ratemyestate.castledigital.com.au |
| Admin (Angular — legacy) | https://ratemyestatecms.castledigital.com.au |
| Admin (React — new) | https://ratemyestatecms-react.castledigital.com.au |

## Pre Production Live URLS

| App | URL |
|---|---|
| Public site | https://pre.ratemyestate.com.au |
| Admin (React — new) | https://pre-admin.ratemyestatecms.com.au |

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

### Locality reference data

Suburb and postcode selection on the review form and in the admin filters is
driven by the `suburbs` table, seeded from a Geoscape G-NAF derived extract and
refreshed quarterly:

```bash
php artisan localities:import              # seed or refresh from database/data/localities-gnaf.json
php artisan reviews:backfill-localities    # attach existing reviews to a locality
```

The G-NAF attribution in the site footer is a licence obligation and must ship
with the data. See [docs/LOCALITY-REFERENCE-DATA.md](docs/LOCALITY-REFERENCE-DATA.md)
for the refresh runbook.

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

## CI/CD & Deployment

### Overview

Two GitHub Actions workflows automate deployment:

| Environment | Trigger                              | Branch        | Workflow file                          |
|-------------|---------------------------------------|---------------|------------------------------------------|
| Staging     | Push to `development`                | `development` | `.github/workflows/deploy-staging.yml`   |
| Production  | Push of a `v*` tag, or manual dispatch | `production`   | `.github/workflows/deploy-prod.yml`      |

Both workflows SSH into the target droplet and run that server's own `deploy.sh`.

### Required GitHub Secrets

| Secret            | Description                                      |
|--------------------|--------------------------------------------------|
| `STAGING_HOST`     | Staging droplet IP/hostname                       |
| `STAGING_USER`     | SSH user on staging (`rme`)                       |
| `STAGING_SSH_KEY`  | Private key for GitHub → staging SSH access       |
| `PROD_HOST`        | Production droplet IP (`134.199.175.102`)         |
| `PROD_USER`        | SSH user on prod (`rme_admin`)                    |
| `PROD_SSH_KEY`     | Private key for GitHub → prod SSH access          |

These keys are **separate** from the deploy keys each droplet uses to `git pull` from GitHub — do not confuse the two. Each droplet has its own distinct SSH keypair; a compromised staging key must not grant prod access.

### ⚠️ Branch-specific files — never carry these over from `development`

The following files intentionally differ between `development` and `production`:

- `deploy.sh`
- `docker-compose.yml`
- `backend/Dockerfile` *(does not exist on `production` — consolidated into `docker/php/Dockerfile`; do not reintroduce it)*

**Why:** `production` has no local MySQL container (uses a managed DB cluster), no Angular build step, `docker compose` (v2 plugin syntax) instead of `docker-compose` (v1), and a different `REPO_DIR`/git branch reference. `development` still uses the legacy setup for staging.

### Promoting a commit from `development` to `production`

Production does not receive full merges from `development`. Instead, specific commits are cherry-picked individually:

```bash
# 1. On development, commit and note the commit hash
git log -1 --format="%H"   # copy this hash

# 2. Switch to production
git checkout production
git pull origin production

# 3. Cherry-pick the specific commit
git cherry-pick <commit-hash>
```

**⚠️ Before pushing — always check what the cherry-pick actually touched:**
```bash
git show --stat HEAD
```
If `deploy.sh`, `docker-compose.yml`, or `backend/Dockerfile` appear in that list, **stop** — these files must not be carried over from `development`. Either:
- Cherry-pick with `--no-commit`, then `git restore --staged --worktree <file>` on the affected file(s) before committing, or
- Manually re-apply only the non-infra parts of the commit.

```bash
# 4. Push to production
git push origin production

# 5. Tag and push the tag to trigger the production deploy workflow
git tag vX.Y.Z
git push origin vX.Y.Z
```

This tag push triggers `.github/workflows/deploy-prod.yml`, which SSHes into the production droplet and runs `deploy.sh`.

### Files that are environment-specific and never committed

`.env`, `cms/.env`, and `backend/public/cms/` (build output) are git-ignored. Each environment's `.env` is configured once, directly on that server, and is never synced via git or CI/CD.

### First deploy vs. standard deploy on a new server

`deploy.sh --first` runs seeders and `passport:install`. **Do not run `--first` if the database already has real/dumped data** — check first:
```bash
docker-compose exec -T php artisan tinker <<'EOF'
DB::table('oauth_clients')->get(['id', 'name']);
EOF
```
If Passport client rows already exist, use a standard `bash deploy.sh` instead.

**Important:** Passport *client records* (DB rows) and Passport's *signing keys* (`storage/oauth-private.key` / `oauth-public.key`) are separate things. A DB dump brings the client rows but never the key files, since they're generated per-server and git-ignored. On any new server restored from a dump, run once:
```bash
docker-compose exec -T php php artisan passport:keys
docker-compose exec -T php chown www-data:www-data storage/oauth-*.key
```
Login will fail with a `CryptKey ... does not exist` error until this is done.

### Manual first-time server setup checklist (not automated)

Before CI/CD can deploy to a new server, these are done once by hand:
- [ ] Droplet provisioned, non-root sudo user created, SSH hardened
- [ ] Docker + Compose plugin, native Nginx, Certbot installed
- [ ] Repo cloned, correct branch checked out
- [ ] `.env` files created directly on server (never via git)
- [ ] DB cluster's Trusted Sources includes this server's IP (or VPC, if using private networking)
- [ ] DNS records created and confirmed resolving
- [ ] Native Nginx reverse-proxy configs + Certbot SSL issued
- [ ] `passport:keys` run once against the DB

---

## Infrastructure

- **Server:** DigitalOcean Ubuntu droplet
- **Containers:** Docker (PHP-FPM + Nginx + MySQL 5.7)
- **SSL:** Certbot / Let's Encrypt
- **Host Nginx:** Reverse proxies domains to container ports (8000, 8001, 8002)
