#!/bin/bash

# =============================================================================
# RateMyEstate — Production Deploy Script
# Ubuntu Server — Docker + Native Nginx (SSL via Certbot)
# =============================================================================
# Usage:
#   ./deploy.sh              — standard deploy (preserves database)
#   ./deploy.sh --first      — first deploy (runs migrations + seeds)
#   ./deploy.sh --seed       — re-run seeds only (no migration, no wipe)
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

REPO_DIR="/home/rme/ratemyestate"
BACKEND_DIR="$REPO_DIR/backend"
ADMIN_DIR="$REPO_DIR/admin"
ADMIN_BUILD_OUTPUT="$ADMIN_DIR/dist"
LARAVEL_PUBLIC_ADMIN="$BACKEND_DIR/public/admin"
NODE_VERSION="16"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# -----------------------------------------------------------------------------
# STEP 1 — Pull latest code
# -----------------------------------------------------------------------------
log "Pulling latest code..."
cd "$REPO_DIR" || error "Repo not found at $REPO_DIR"
git pull origin main || error "Git pull failed"

# -----------------------------------------------------------------------------
# STEP 2 — Build Angular admin (production)
# -----------------------------------------------------------------------------
log "Building Angular admin panel..."
cd "$ADMIN_DIR" || error "Admin dir not found"

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
nvm use "$NODE_VERSION" || error "Node $NODE_VERSION not found. Run: nvm install $NODE_VERSION"

if [ ! -d "node_modules" ]; then
    log "Installing admin dependencies..."
    npm install --legacy-peer-deps || error "npm install failed"
fi

log "Running production build..."
npm run build -- --prod || error "Angular build failed"

# -----------------------------------------------------------------------------
# STEP 3 — Copy Angular build into Laravel public/admin/
# -----------------------------------------------------------------------------
log "Copying admin build to Laravel public directory..."
mkdir -p "$LARAVEL_PUBLIC_ADMIN"
rm -rf "${LARAVEL_PUBLIC_ADMIN:?}"/*
cp -r "$ADMIN_BUILD_OUTPUT"/. "$LARAVEL_PUBLIC_ADMIN/"

# -----------------------------------------------------------------------------
# STEP 4 — Stop legacy containers (first deploy only)
# -----------------------------------------------------------------------------
if $FIRST_DEPLOY; then
    warn "First deploy — stopping legacy rme-container if it exists..."
    docker stop rme-container 2>/dev/null || true
    docker rm rme-container 2>/dev/null || true
fi

# -----------------------------------------------------------------------------
# STEP 5 — Rebuild PHP container only — NEVER touch mysql volume
# -----------------------------------------------------------------------------
log "Rebuilding PHP container..."
cd "$REPO_DIR" || error "Repo dir not found"

docker-compose build --no-cache php

# Start mysql first if not running (preserves existing volume/data)
docker-compose up -d mysql
log "Waiting for MySQL to be ready..."
for i in {1..30}; do
    if docker-compose exec -T mysql mysqladmin ping -u root -pRootChange1234 --silent 2>/dev/null; then
        log "MySQL is ready"
        break
    fi
    echo -n "."
    sleep 2
done
echo ""

# Recreate only php and nginx — leave mysql untouched
docker-compose up -d --force-recreate --no-deps php nginx

# -----------------------------------------------------------------------------
# STEP 6 — Clear stale bootstrap cache
# -----------------------------------------------------------------------------
log "Clearing stale bootstrap cache..."
docker-compose exec -T php rm -f bootstrap/cache/services.php bootstrap/cache/packages.php

# -----------------------------------------------------------------------------
# STEP 7 — Run migrations (safe — skips already-run migrations)
# -----------------------------------------------------------------------------
log "Running migrations..."
docker-compose exec -T php php artisan migrate --force || error "Migrations failed"

# -----------------------------------------------------------------------------
# STEP 8 — Seeds (first deploy or --seed flag only)
# -----------------------------------------------------------------------------
if $FIRST_DEPLOY || $SEED_ONLY; then
    log "Running seeders..."
    docker-compose exec -T php php artisan db:seed --class=RoleSeeder             2>/dev/null || warn "RoleSeeder skipped (already seeded)"
    docker-compose exec -T php php artisan db:seed --class=UserSeeder             2>/dev/null || warn "UserSeeder skipped"
    docker-compose exec -T php php artisan db:seed --class=CmsPageSeeder          2>/dev/null || warn "CmsPageSeeder skipped"
    docker-compose exec -T php php artisan db:seed --class=PropertyCategorySeeder 2>/dev/null || warn "PropertyCategorySeeder skipped"
    docker-compose exec -T php php artisan db:seed --class=BuyerTypeSeeder        2>/dev/null || warn "BuyerTypeSeeder skipped"
    docker-compose exec -T php php artisan db:seed --class=TagsSeeder             2>/dev/null || warn "TagsSeeder skipped"
    docker-compose exec -T php php artisan db:seed --class=SuburbsSeeder          2>/dev/null || warn "SuburbsSeeder skipped"
    docker-compose exec -T php php artisan db:seed --class=BlogCategorySeeder     2>/dev/null || warn "BlogCategorySeeder skipped"
fi

# -----------------------------------------------------------------------------
# STEP 9 — Passport (first deploy only)
# -----------------------------------------------------------------------------
if $FIRST_DEPLOY; then
    log "Installing Passport..."
    docker-compose exec -T php php artisan passport:install || warn "Passport already installed"
fi

# -----------------------------------------------------------------------------
# STEP 10 — Cache config, routes, views
# -----------------------------------------------------------------------------
log "Caching config, routes and views..."
docker-compose exec -T php php artisan config:clear
docker-compose exec -T php php artisan route:clear
docker-compose exec -T php php artisan view:clear
docker-compose exec -T php php artisan config:cache
docker-compose exec -T php php artisan view:cache

# -----------------------------------------------------------------------------
# STEP 11 — Fix permissions
# -----------------------------------------------------------------------------
log "Fixing storage permissions..."
docker-compose exec -T php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec -T php chmod -R 775 storage bootstrap/cache

# -----------------------------------------------------------------------------
# DONE
# -----------------------------------------------------------------------------
log "✅ Deploy complete!"
echo ""
echo "  Public site: https://ratemyestate.castledigital.com.au"
echo "  Admin panel: https://ratemyestatecms.castledigital.com.au"
echo ""
warn "The MySQL volume is NEVER wiped by this script."
warn "To manually reset DB: docker-compose down && docker volume rm ratemyestate_rme_mysql_data"