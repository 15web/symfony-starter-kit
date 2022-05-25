#!/usr/bin/env bash

set -Eeuo pipefail

docker build \
        --tag symfony-starter-kit-php-cli \
        --build-arg USER_ID=$(id -u) \
    ./docker

docker run --rm --volume "$(pwd):/home/dev/app/" symfony-starter-kit-php-cli "$@";
