#!/bin/sh
set -e

# 1) Publier les assets des bundles (dont EasyAdmin) vers /public/bundles
php bin/console -n assets:install --symlink --relative

# 2) BDD & cache
php bin/console -n doctrine:migrations:migrate --allow-no-migration --all-or-nothing
php bin/console -n cache:clear --env=prod
php bin/console -n cache:warmup --env=prod
