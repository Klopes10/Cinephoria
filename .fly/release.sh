#!/bin/sh
set -e

php bin/console -n doctrine:migrations:migrate --allow-no-migration --all-or-nothing
php bin/console -n cache:clear --env=prod
php bin/console -n cache:warmup --env=prod
