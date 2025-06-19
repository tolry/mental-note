#!/usr/bin/env bash

set -eEuo pipefail

cd /app

composer install --quiet --no-interaction --no-scripts

while ! bin/console do:qu:sql -q 'CREATE DATABASE IF NOT EXISTS mental_note' >/dev/null 2>&1;
do
  echo WAITING FOR MYSQL AVAILIBILITY
  sleep 3;
done;

bin/console do:mi:mi --no-interaction --quiet

php-fpm


