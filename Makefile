.PHONY: all

setup-env: # Установка переменных окружения
	./setup_envs.bash

init: # Запуск проекта и установка зависимостей
	make setup-env
	make build
	make db-migrate
	make setup-transports

test-install: # Подготовка тестового окружения
	make init
	@for i in 1 2 3 4 ; do \
		docker compose exec pgsql dropdb -f --if-exists db_name_test$$i; \
		docker compose exec pgsql createdb -O postgres db_name_test$$i; \
		docker compose run --rm backend bash -c "TEST_TOKEN=$$i bin/console --env=test doctrine:migrations:migrate --no-interaction"; \
	done

check: composer-validate composer-audit cache-clear lint test check-openapi-diff check-openapi-schema # Проверка кода

fix: fixer-fix rector-fix # Запуск правок кода

up:	# Запуск контейнеров
	make setup-env
	docker compose up -d --force-recreate --remove-orphans

down: # Остановка контейнеров
	make setup-env
	docker compose down --remove-orphans

update:	# Обновление зависимостей
	docker compose run --rm backend-cli composer update

build: # Сборка образов и установка зависимостей
	make setup-env
	docker compose build
	make composer-install
	make up

db-migrate: # Миграции БД
	docker compose run --rm backend-cli bin/console doctrine:migrations:migrate --no-interaction

db-create-migration: # Создание миграций БД
	docker compose run --rm backend-cli bin/console doctrine:migrations:diff

db-migration-prev: # Откатить последнюю миграцию
	docker compose run --rm backend-cli bin/console doctrine:migrations:migrate prev

setup-transports: # Настройка очередей
	docker compose run --rm backend-cli bin/console messenger:setup-transports

run-backend: # Выполнение команды на бэкенде, пример: make run-backend echo "hello"
	make setup-env
	@docker compose run --rm backend-cli $(Arguments)

logs: # Просмотр логов сервиса, пример: make logs backend
	make setup-env
	@docker compose logs $(Arguments)

lint: container-lint twig-lint fixer-check rector-check phpstan psalm deptrac-check-unassigned cache-prod-check

composer-install: # Установка пакетов
	docker compose run --rm backend-cli composer install --no-scripts --prefer-dist

composer-validate: # Валидация композера
	docker compose run --rm backend-cli composer validate --strict

composer-audit: # Проверка пакетов
	docker compose run --rm backend-cli composer audit --format=plain

cache-clear: # Очистка кеша
	docker compose run --rm backend-cli bin/console cache:clear

container-lint: # Проверка контейнера зависимостей
	docker compose run --rm backend-cli bin/console lint:container

validate-doctrine-schema: # Проверка валидности схемы БД
	docker compose run --rm backend-cli bin/console doctrine:schema:validate -v

twig-lint: # Линтер твиг-шаблонов
	docker compose run --rm backend-cli bin/console lint:twig src/Mailer/templates

phpstan: # Запустить phpstan
	docker compose run --rm backend-cli vendor/bin/phpstan analyse -c src-dev/PHPStan/phpstan-config.neon --memory-limit 2G --ansi

phpstan-update-baseline: # Обновить baseline для phpstan
	docker compose run --rm backend-cli vendor/bin/phpstan analyse -c src-dev/PHPStan/phpstan-config.neon --memory-limit 2G --generate-baseline

psalm:	# Запустить psalm
	docker compose run --rm backend-cli vendor/bin/psalm --config=src-dev/psalm.xml

psalm-update-baseline:	# Обновить baseline для psalm
	docker compose run --rm backend-cli vendor/bin/psalm --config=src-dev/psalm.xml --set-baseline=psalm-baseline.xml

fixer-check: # Проверка стиля написания кода
	docker compose run --rm backend-cli vendor/bin/php-cs-fixer --config=src-dev/PHPCsFixer/php-cs-fixer-config.php fix --dry-run --diff --ansi -v

fixer-fix: # Фикс стиля написания кода
	docker compose run --rm backend-cli vendor/bin/php-cs-fixer --config=src-dev/PHPCsFixer/php-cs-fixer-config.php fix -v

rector-check: # Какой код необходимо отрефакторить
	docker compose run --rm backend-cli vendor/bin/rector process --config=src-dev/Rector/rector.config.php --dry-run --ansi

rector-fix: # Рефакторинг кода
	docker compose run --rm backend-cli vendor/bin/rector process --config=src-dev/Rector/rector.config.php --clear-cache

cache-prod-check: # Очистка кода для прода
	docker compose run --rm backend-cli bin/console cache:clear --env=prod

deptrac-check: # Контроль зависимостей
	docker compose run --rm backend-cli vendor/bin/deptrac analyse --config-file=src-dev/deptrac.yaml --fail-on-uncovered --report-uncovered

deptrac-check-unassigned: # Покрытие кода с deptrac
	docker compose run --rm backend-cli vendor/bin/deptrac debug:unassigned --config-file=src-dev/deptrac.yaml | tee /dev/stderr | grep 'There are no unassigned tokens'

test: # Запуск тестов
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	docker compose run --rm backend-cli bash -c 'APP_ENV=test vendor/bin/paratest --configuration=src-dev/phpunit.xml -p4'

test-verbose: # Запуск тестов с детальным описанием
	make setup-env
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	docker compose run --rm backend-cli bash -c 'APP_ENV=test vendor/bin/paratest --configuration=src-dev/phpunit.xml -p4 --testdox'

test-single: # Запуск одного теста, пример: make test-single class=TaskCommentBodyTest
	make setup-env
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	@docker compose run --rm backend-cli bash -c "APP_ENV=test vendor/bin/phpunit --configuration=src-dev/phpunit.xml --filter=$(class)"

check-openapi-diff: # Валидация соответствия роутов и схемы openapi
	docker compose run --rm backend-cli bin/console app:openapi-routes-diff ./src-dev/openapi.yaml

check-openapi-schema: spectral # Валидация openapi.yaml

spectral: # Валидация openapi.yaml с помощью spectral
	docker run --rm -it -v ${PWD}/backend:/app stoplight/spectral lint /app/src-dev/openapi.yaml -F warn --ruleset=/app/src-dev/.spectral.yaml

deprecations-check: # Проверка на устаревший функционал
	docker compose run --rm backend-cli bin/console debug:container --deprecations

help:	# Справка по командам
	@grep -E '^[a-zA-Z0-9 -]+:.*#'  Makefile | sort | while read -r l; do printf "\033[1;32m$$(echo $$l | cut -f 1 -d':')\033[00m:$$(echo $$l | cut -f 2- -d'#')\n"; done
