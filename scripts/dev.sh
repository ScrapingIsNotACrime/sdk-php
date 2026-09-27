#!/usr/bin/env bash
# Runs a command inside the PHP toolchain container (PHP is not installed on the host).
set -euo pipefail
cd "$(dirname "$0")/.."
docker build -q -t scrapingisnotacrime-php-dev -f docker/dev.Dockerfile docker >/dev/null
exec docker run --rm -i \
  --user "$(id -u):$(id -g)" \
  -e HOME=/tmp -e COMPOSER_HOME=/tmp/composer \
  ${SCRAPINGISNOTACRIME_API_KEY:+-e SCRAPINGISNOTACRIME_API_KEY} \
  -v "$PWD":/src -w /src scrapingisnotacrime-php-dev "$@"
