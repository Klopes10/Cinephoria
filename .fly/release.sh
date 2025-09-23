#!/bin/sh
set -e

# Publier les assets des bundles (EasyAdmin -> /public/bundles/easyadmin)
php bin/console -n assets:install

# Créer des alias "underscore" pour les fichiers fingerprintés EasyAdmin
EA_DIR="public/bundles/easyadmin"
if [ -d "$EA_DIR" ]; then
  for f in "$EA_DIR"/*.*.css "$EA_DIR"/*.*.js; do
    [ -f "$f" ] || continue
    b="$(basename "$f")"
    n="${b%%.*}"              # name
    rest="${b#*.}"            # hash.ext
    h="${rest%%.*}"           # hash
    e="${b##*.}"              # ext
    cp -f "$f" "$EA_DIR/${n}_${h}.$e"
  done
fi

# AssetMapper seulement si présent
if php bin/console -q help asset-map:compile >/dev/null 2>&1; then
  php bin/console -n asset-map:compile
fi

# DB & cache
php bin/console -n doctrine:migrations:migrate --allow-no-migration --all-or-nothing
php bin/console -n cache:clear --env=prod
php bin/console -n cache:warmup --env=prod
