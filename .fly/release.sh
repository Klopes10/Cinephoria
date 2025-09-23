#!/bin/sh
set -e

# 1) Publier les assets des bundles (EasyAdmin -> /public/bundles/easyadmin)
php bin/console -n assets:install   # copie réelle (pas de symlink, plus simple)

# 2) Créer des alias "underscore" pour les fichiers fingerprintés EasyAdmin
EA_DIR="public/bundles/easyadmin"
if [ -d "$EA_DIR" ]; then
  # pour chaque fichier "name.hash.css|js", crée "name_hash.css|js"
  find "$EA_DIR" -maxdepth 1 -type f \( -name '*.*.css' -o -name '*.*.js' \) | while read -r f; do
    b="$(basename "$f")"          # ex: app.769fc123.css
    ext="${b##*.}"                # css
    tmp="${b%.*}"                 # app.769fc123
    hash="${tmp##*.}"             # 769fc123
    name="${tmp%.*}"              # app
    underscored="${name}_${hash}.${ext}"  # app_769fc123.css
    # crée une copie (ou ln -sf si tu préfères des symlinks)
    cp -f "$EA_DIR/$b" "$EA_DIR/$underscored"
  done
fi

# 3) AssetMapper (uniquement si présent dans ton projet)
if php bin/console -q help asset-map:compile >/dev/null 2>&1; then
  php bin/console -n asset-map:compile
fi

# 4) BDD & cache
php bin/console -n doctrine:migrations:migrate --allow-no-migration --all-or-nothing
php bin/console -n cache:clear --env=prod
php bin/console -n cache:warmup --env=prod
