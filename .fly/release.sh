#!/bin/sh
set -e

# Publier les assets des bundles (EasyAdmin -> /public/bundles/easyadmin)
php bin/console -n assets:install --symlink --relative

# Compiler AssetMapper UNIQUEMENT si présent (Symfony 7 AssetMapper)
if php bin/console -q help asset-map:compile >/dev/null 2>&1; then
  php bin/console -n asset-map:compile
fi

# Migrations & cache
php bin/console -n doctrine:migrations:migrate --allow-no-migration --all-or-nothing
php bin/console -n cache:clear --env=prod
php bin/console -n cache:warmup --env=prod
