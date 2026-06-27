#!/bin/sh
set -exu  # log + exit on error + variables non définies interdites

# ===========================
# Recréer les clés JWT depuis les secrets Fly
# ===========================
mkdir -p config/jwt
printf "%s" "$JWT_SECRET_KEY_B64" | base64 -d > config/jwt/private.pem
printf "%s" "$JWT_PUBLIC_KEY_B64" | base64 -d > config/jwt/public.pem

chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem

# ===========================
# Publier les assets des bundles
# ===========================
php bin/console -n assets:install

# ===========================
# Alias EasyAdmin
# ===========================
EA_DIR="public/bundles/easyadmin"
if [ -d "$EA_DIR" ]; then
  for f in "$EA_DIR"/*.*.css "$EA_DIR"/*.*.js; do
    [ -f "$f" ] || continue
    b="$(basename "$f")"
    n="${b%%.*}"
    rest="${b#*.}"
    h="${rest%%.*}"
    e="${b##*.}"
    cp -f "$f" "$EA_DIR/${n}_${h}.$e"
  done
fi

# ===========================
# Asset Mapper
# ===========================
if php bin/console -q help asset-map:compile >/dev/null 2>&1; then
  php bin/console -n asset-map:compile
fi

# ===========================
# Base de données + cache
# ===========================
php bin/console -n doctrine:migrations:migrate --allow-no-migration --all-or-nothing
php bin/console -n cache:clear --env=prod
php bin/console -n cache:warmup --env=prod