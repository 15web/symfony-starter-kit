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

    if [ "$(grep 'USE_MUTAGEN' ./.env)" == 'USE_MUTAGEN=1' ]; then
        echo 'Mutagen used.';
        COMPOSE_FILE_ENV="COMPOSE_FILE='docker-compose.local.yml:mutagen-compose.yml'";
        compose() {
            mutagen-compose "$@"
        }
    else
        COMPOSE_FILE_ENV="COMPOSE_FILE='docker-compose.local.yml'";
        compose() {
            docker-compose "$@"
        }
    fi

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

    for (( i=1; i<=4; i++ ))
    do
        compose exec -T mysql mysql -proot -e "drop database if exists db_name_test$i;";
        compose exec -T mysql mysql -proot -e "create database if not exists db_name_test$i;";
        compose exec -T mysql mysql -proot -e "GRANT ALL PRIVILEGES ON db_name_test$i.* TO 'db_user'@'%';";

        compose run -e TEST_TOKEN=$i --rm backend bin/console --env=test doctrine:migrations:migrate --no-interaction
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
        runBackend composer audit --format=plain

        runBackend bin/console cache:clear
        runBackend composer check

        runBackend bin/console --env=test cache:clear
        compose run --rm -e APP_ENV=test backend vendor/bin/paratest -p4

        runBackend bin/console app:openapi-routes-diff ./openapi.yaml

        ;;
    install-test)
        setupEnvs;

        installTest
        ;;
    test)
        setupEnvs;

        runBackend bin/console --env=test cache:clear
        compose run --rm -e APP_ENV=test backend vendor/bin/paratest -p4
        ;;
    test-verbose)
        # запуск тестов с описанием

        setupEnvs;

        runBackend bin/console --env=test cache:clear
        compose run --rm -e APP_ENV=test backend vendor/bin/phpunit --testdox
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
            test-verbose,
            fix[f],
            hooks-install[hi],
            cleanup-mutagen[cm],
            setup-envs[se].'
        ;;
esac
