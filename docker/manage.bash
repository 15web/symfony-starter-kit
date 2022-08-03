#!/usr/bin/env bash

set -Eeuo pipefail

os-depend-sed-in-place() {
    if [ "$(uname -s)" == 'Darwin' ]; then
        sed -i '' "$@";
    else
        sed --in-place "$@";
    fi
}

setupEnvs() {
    [ ! -f ./.env ] && cp ./.env.dist ./.env

    COMPOSE_FILE_ENV="COMPOSE_FILE='docker-compose.local.yml'";
    if [ "$(grep 'COMPOSE_FILE' ./.env)" != '' ]; then
        os-depend-sed-in-place -E "s|^.*COMPOSE_FILE=.*$|$COMPOSE_FILE_ENV|" ./.env;
    else
        echo "$COMPOSE_FILE_ENV" >> ./.env;
    fi

    USER_ID_ENV="USER_ID=$(id -u)";
    if [ "$(grep 'USER_ID' ./.env)" != '' ]; then
        os-depend-sed-in-place -E "s|^.*USER_ID=.*$|$USER_ID_ENV|" ./.env;
    else
        echo "$USER_ID_ENV" >> ./.env;
    fi

    source '.env'
    if [ "$USE_MUTAGEN" == 1 ]; then
        echo 'Mutagen used.'
        compose() {
            mutagen-compose -f "$COMPOSE_FILE" -f "$MUTAGEN_FILE" "$@"
        }
    else
        compose() {
            docker-compose "$@"
        }
    fi

    [ ! -f ./backend/.env ] && cp ./backend/.env.dist ./backend/.env
    [ ! -f ./mysql/.env ] && cp ./mysql/.env.dist ./mysql/.env

    echo 'Envs set up!';
}

installTest() {
    compose build backend mysql

    runBackend composer install --no-scripts --prefer-dist --no-progress

    compose up --detach --force-recreate --remove-orphans backend mysql

    runBackend ./bin/console doctrine:migrations:migrate --no-interaction
    runBackend ./bin/console messenger:setup-transports

    compose exec -T mysql mysql -proot -e "drop database if exists db_name_test;";
    compose exec -T mysql mysql -proot -e "create database if not exists db_name_test;";
    compose exec -T mysql mysql -proot -e "GRANT ALL PRIVILEGES ON db_name_test.* TO 'db_user'@'%';";

    runBackend bin/console --env=test doctrine:migrations:migrate --no-interaction
    runBackend bin/console --env=test cache:clear
}

install() {
    compose build

    runBackend composer install --no-scripts --prefer-dist

    compose up --detach --force-recreate --remove-orphans

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
    compose build --no-cache
    runBackend composer install --no-scripts --prefer-dist
    compose up -d --force-recreate --remove-orphans
}

runBackend() {
    compose run --rm backend "$@"
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
        runBackend composer check

        runBackend bin/console --env=test cache:clear
        compose run --rm -e APP_ENV=test backend bin/phpunit

        runBackend bin/console app:openapi-routes-diff ./openapi.yaml

        ;;
    install-test)
        setupEnvs;

        installTest
        ;;
    test)
        setupEnvs;

        runBackend bin/console --env=test cache:clear
        compose run --rm -e APP_ENV=test backend bin/phpunit -v
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
            install-test,
            test,
            fix[f],
            hooks-install[hi],
            cleanup-mutagen[cm],
            setup-envs[se].'
        ;;
esac
