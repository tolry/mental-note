#!/usr/bin/env bash

set -eEuo pipefail

cd /app

composer install --quiet --no-interaction --no-scripts
bin/console do:mi:mi --no-interaction --quiet

php-fpm


