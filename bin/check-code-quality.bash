#!/usr/bin/env bash

set -Eeuo pipefail

./bin/docker-run-cmd.bash vendor/bin/php-cs-fixer fix --dry-run --diff --config php-cs-fixer-config.php
./bin/docker-run-cmd.bash vendor/bin/phpstan analyse -c phpstan-config.neon
./bin/docker-run-cmd.bash vendor/bin/phpcs
