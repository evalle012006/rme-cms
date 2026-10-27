#!/bin/bash

# =============================================================================
# RateMyEstate — Production Deploy Script
# Ubuntu Server — Docker + Native Nginx (SSL via Certbot)
# =============================================================================
# Usage:
#   ./deploy.sh          — standard deploy (runs migrations, no seeding)
#   ./deploy.sh --first  — first deploy (runs migrations + all seeders + passport)
#   ./deploy.sh --seed   — re-run seeds only (no migration)
# =============================================================================

set -e

FIRST_DEPLOY=false
SEED_ONLY=false

for arg in "$@"; do
    case $arg in
        --first) FIRST_DEPLOY=true ;;
        --seed)  SEED_ONLY=true ;;
    esac
done

REPO_DIR="/home/rme_admin/ratemyestate"
BACKEND_DIR="$REPO_DIR/backend"

CMS_DIR="$REPO_DIR/cms"
CMS_BUILD_OUTPUT="$CMS_DIR/dist"
LARAVEL_PUBLIC_CMS="$BACKEND_DIR/public/cms"
NODE_VERSION_REACT="24"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

load_nvm() {
    export NVM_DIR="$HOME/.nvm"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
}

# -----------------------------------------------------------------------------
# STEP 1 — Pull latest code
# -----------------------------------------------------------------------------
log "Pulling latest code..."
cd "$REPO_DIR" || error "Repo not found at $REPO_DIR"
git pull origin production || error "Git pull failed"

# -----------------------------------------------------------------------------
# STEP 2 — Build React CMS
# -----------------------------------------------------------------------------
log "Building React CMS..."
cd "$CMS_DIR" || error "cms dir not found at $CMS_DIR"

load_nvm
nvm use "$NODE_VERSION_REACT" || error "Node $NODE_VERSION_REACT not available. Run: nvm install $NODE_VERSION_REACT"

if [ ! -d "node_modules" ]; then
    log "Installing React dependencies..."
    npm install || error "npm install failed"
fi

npm run build || error "React build failed"

log "Copying React build → backend/public/cms/"
mkdir -p "$LARAVEL_PUBLIC_CMS"
rm -rf "${LARAVEL_PUBLIC_CMS:?}"/*
cp -r "$CMS_BUILD_OUTPUT"/. "$LARAVEL_PUBLIC_CMS/"

# -----------------------------------------------------------------------------
# STEP 3 — Rebuild PHP container and recreate services
# -----------------------------------------------------------------------------
log "Rebuilding PHP container..."
cd "$REPO_DIR" || error "Repo dir not found"

docker compose build --no-cache php
docker compose up -d --force-recreate --no-deps php nginx

# -----------------------------------------------------------------------------
# STEP 4 — Clear stale bootstrap cache
# -----------------------------------------------------------------------------
log "Clearing stale bootstrap cache..."
docker compose exec -T php rm -f bootstrap/cache/services.php bootstrap/cache/packages.php

# -----------------------------------------------------------------------------
# STEP 5 — Run migrations (against external managed DB)
# -----------------------------------------------------------------------------
log "Running migrations..."
docker compose exec -T php php artisan migrate --force || error "Migrations failed"

# -----------------------------------------------------------------------------
# STEP 6 — Seeds (first deploy or --seed flag only)
# -----------------------------------------------------------------------------
if $FIRST_DEPLOY || $SEED_ONLY; then
    log "Running seeders..."
    docker compose exec -T php php artisan db:seed --class=RoleSeeder             2>/dev/null || warn "RoleSeeder skipped"
    docker compose exec -T php php artisan db:seed --class=UserSeeder             2>/dev/null || warn "UserSeeder skipped"
    docker compose exec -T php php artisan db:seed --class=CmsPageSeeder          2>/dev/null || warn "CmsPageSeeder skipped"
    docker compose exec -T php php artisan db:seed --class=PropertyCategorySeeder 2>/dev/null || warn "PropertyCategorySeeder skipped"
    docker compose exec -T php php artisan db:seed --class=BuyerTypeSeeder        2>/dev/null || warn "BuyerTypeSeeder skipped"
    docker compose exec -T php php artisan db:seed --class=TagsSeeder             2>/dev/null || warn "TagsSeeder skipped"
    docker compose exec -T php php artisan db:seed --class=SuburbsSeeder          2>/dev/null || warn "SuburbsSeeder skipped"
    docker compose exec -T php php artisan db:seed --class=BlogCategorySeeder     2>/dev/null || warn "BlogCategorySeeder skipped"
fi

# -----------------------------------------------------------------------------
# STEP 7 — Passport (first deploy only)
# -----------------------------------------------------------------------------
if $FIRST_DEPLOY; then
    log "Installing Passport..."
    docker compose exec -T php php artisan passport:install || warn "Passport already installed"
fi

# -----------------------------------------------------------------------------
# STEP 8 — Cache config, routes, views
# -----------------------------------------------------------------------------
log "Caching config, routes and views..."
docker compose exec -T php php artisan config:clear
docker compose exec -T php php artisan route:clear
docker compose exec -T php php artisan view:clear
docker compose exec -T php php artisan config:cache
docker compose exec -T php php artisan view:cache

# -----------------------------------------------------------------------------
# STEP 9 — Fix permissions
# -----------------------------------------------------------------------------
log "Fixing storage permissions..."
docker compose exec -T php chown -R www-data:www-data storage bootstrap/cache
docker compose exec -T php chmod -R 775 storage bootstrap/cache

# -----------------------------------------------------------------------------
# STEP 10 — Create storage symlink
# -----------------------------------------------------------------------------
log "Creating storage symlink..."
docker compose exec -T php php artisan storage:link || warn "storage:link failed (check storage permissions)"

# -----------------------------------------------------------------------------
# DONE
# -----------------------------------------------------------------------------
log "✅ Deploy complete!"
echo ""
echo "  Public site: https://pre.ratemyestate.com"
echo "  CMS (React): https://pre-admin.ratemyestate.com"
echo ""
warn "Production database is external (DO managed MySQL) — this script never touches it directly."