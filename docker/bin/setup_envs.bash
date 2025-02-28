#!/usr/bin/env bash

set -Eeuo pipefail

replace_env() {
    ./docker/bin/replace_env.bash "$@"
}

COMPOSE_ENV_PATH="$(realpath ./.env)"
COMPOSE_DIST_ENV_PATH="$(realpath ./docker/.env.dist)"

# У docker compose странный баг: когда ./.env загружен, compose пытается загрузить ./docker/.env,
# вероятно из-за того, что там находится docker-compose.local.yml,
# это приводит к непредсказуемому поведению и ошибкам
rm -f ./docker/.env

[ ! -f "${COMPOSE_ENV_PATH}" ] && cp "${COMPOSE_DIST_ENV_PATH}" "${COMPOSE_ENV_PATH}"

replace_env "${COMPOSE_ENV_PATH}" 'COMPOSE_PROJECT_NAME' 'symfony-starter-kit-local'
replace_env "${COMPOSE_ENV_PATH}" 'COMPOSE_FILE' './docker/docker-compose.local.yml'
replace_env "${COMPOSE_ENV_PATH}" 'USER_ID' "$(id -u)"

[ ! -f 'docker/pgsql/.env' ] && cp 'docker/pgsql/.env.dist' 'docker/pgsql/.env'

echo 'Envs set up!';
