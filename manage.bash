#!/usr/bin/env bash

set -Eeuo pipefail

compose() {
    docker-compose "$@"
}

replace_env() {
    ./docker/bin/replace_env.bash "$@"
}

COMPOSE_ENV_PATH="$(realpath ./.env)"
COMPOSE_DIST_ENV_PATH="$(realpath ./docker/.env.dist)"

setupEnvs() {
    # У docker compose странный баг: когда ./.env загружен, compose пытается загрузить ./docker/.env,
    # вероятно из-за того, что там находится docker-compose.local.yml,
    # это приводит к непредсказуемому поведению и ошибкам
    rm -f ./docker/.env

    [ ! -f "${COMPOSE_ENV_PATH}" ] && cp "${COMPOSE_DIST_ENV_PATH}" "${COMPOSE_ENV_PATH}"

    replace_env "${COMPOSE_ENV_PATH}" 'COMPOSE_PROJECT_NAME' 'symfony-starter-kit-local'
    replace_env "${COMPOSE_ENV_PATH}" 'COMPOSE_FILE' './docker/docker-compose.local.yml'

    if [ "$(grep 'USE_MUTAGEN' ${COMPOSE_ENV_PATH})" == 'USE_MUTAGEN=1' ]; then
        echo 'Mutagen used.';
        replace_env "${COMPOSE_ENV_PATH}" 'COMPOSE_PROJECT_NAME' 'symfony-starter-kit-local-mutagen'
        replace_env "${COMPOSE_ENV_PATH}" 'COMPOSE_FILE' 'docker/docker-compose.local.yml:docker/mutagen-compose.yml'

        compose() {
            mutagen-compose "$@"
        }
    fi

    replace_env "${COMPOSE_ENV_PATH}" 'USER_ID' "$(id -u)"

    [ ! -f 'docker/backend/.env' ] && cp 'docker/backend/.env.dist' 'docker/backend/.env'
    [ ! -f 'docker/mysql/.env' ] && cp 'docker/mysql/.env.dist' 'docker/mysql/.env'

    echo 'Envs set up!';
}

installTest() {
    compose build backend mysql

    runBackend composer install --no-scripts --prefer-dist --no-progress

    compose up --detach --force-recreate --remove-orphans backend mysql

    printf "Waiting for mysql"
    until echo 'select 1;' | compose exec -T mysql mysql -proot &>/dev/null
    do
      printf "."
      sleep 1
    done
    printf "\nMysql is up!\n"

    runBackend ./bin/console doctrine:migrations:migrate --no-interaction
    runBackend ./bin/console messenger:setup-transports

    for (( i=1; i<=4; i++ ))
    do
        compose exec -T mysql mysql -proot -e "drop database if exists db_name_test$i;";
        compose exec -T mysql mysql -proot -e "create database if not exists db_name_test$i;";
        compose exec -T mysql mysql -proot -e "GRANT ALL PRIVILEGES ON db_name_test$i.* TO 'db_user'@'%';";

        runBackend bash -c "TEST_TOKEN=${i} bin/console --env=test doctrine:migrations:migrate --no-interaction"
    done
}

install() {
    compose build

    runBackend composer install --no-scripts --prefer-dist

    compose up --detach --force-recreate --remove-orphans

    printf "Waiting for mysql"
    until echo 'select 1;' | compose exec -T mysql mysql -proot &>/dev/null
    do
      printf "."
      sleep 1
    done
    printf "\nMysql is up!\n"

    runBackend ./bin/console doctrine:migrations:migrate --no-interaction
    runBackend ./bin/console messenger:setup-transports

    echo "Done!"
}

up() {
    compose up -d --force-recreate --remove-orphans
}

down() {
    compose down --remove-orphans
}

update() {
    compose pull
    compose build --pull
    runBackend composer update
}

build() {
    compose build
    runBackend composer install --no-scripts --prefer-dist
    compose up -d --force-recreate --remove-orphans
}

runBackend() {
    compose run --rm backend-cli "$@"
}

logs() {
    compose logs "$@"
}

cleanup-mutagen() {
    mutagen-compose down --volumes;

    mutagen sync terminate --all;

    exit 0;
}

COMMAND=$1
case $COMMAND in
    install | i)
        setupEnvs
        install
        ;;
    up | u)
        setupEnvs;
        up;
        ;;
    down | d)
        setupEnvs;
        down;
        ;;
    update | upd)
        setupEnvs;
        update;
        ;;
    build | b)
        setupEnvs;
        build;
        ;;
    run-backend | rb)
        setupEnvs;

        ARGS_WITHOUT_FIRST="${@:2}"
        runBackend $ARGS_WITHOUT_FIRST;
        ;;
    check | c)
        setupEnvs;

        runBackend composer validate --strict
        runBackend composer audit --format=plain

        runBackend bin/console cache:clear
        runBackend composer check

        runBackend bin/console --env=test cache:clear
        runBackend bash -c 'APP_ENV=test vendor/bin/paratest -p4'

        runBackend bin/console-dev app:openapi-routes-diff ./openapi.yaml

        compose run --rm vacuum lint /app/openapi.yaml -d;

        ;;
    check-openapi)
        setupEnvs;

        compose run --rm vacuum lint /app/openapi.yaml -d -e;
        ;;
    install-test)
        setupEnvs;

        installTest
        ;;
    test)
        setupEnvs;

        runBackend bin/console --env=test cache:clear
        runBackend bash -c 'APP_ENV=test vendor/bin/paratest -p4'
        ;;
    test-verbose)
        # запуск тестов с описанием

        setupEnvs;

        runBackend bin/console --env=test cache:clear
        runBackend bash -c 'APP_ENV=test vendor/bin/phpunit --testdox'
        ;;
    fix | f)
        setupEnvs;

        runBackend composer fix
        ;;
    logs | l)
        setupEnvs;

        ARGS_WITHOUT_FIRST="${@:2}"
        logs $ARGS_WITHOUT_FIRST;
        ;;
    hooks-install | hi)
        printf '#!/usr/bin/env sh\n\ncd docker;./manage.bash check;\n' > ../.git/hooks/pre-commit;

        chmod +x .git/hooks/pre-commit;
        ;;
    cleanup-mutagen | cm)
        cleanup-mutagen
        ;;
    setup-envs | se)
        setupEnvs;
        ;;
    *)
        echo 'Unknown command. Available:
            install[i],
            up[u],
            down[d],
            update[upd],
            build[b],
            run-backend[rb],
            logs[l],
            check[c],
            check-openapi,
            install-test,
            test,
            test-verbose,
            fix[f],
            hooks-install[hi],
            cleanup-mutagen[cm],
            setup-envs[se].'
        ;;
esac
