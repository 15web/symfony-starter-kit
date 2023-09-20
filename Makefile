.PHONY: all

init: ./setup_envs.bash build db-migrate setup-transports # Запуск проекта и установка зависимостей

install-test: # Подготовка тестового окружения
	make init
	@for i in 1 2 3 4 ; do \
  		docker compose exec mysql mysql -proot -e "drop database if exists db_name_test$$i;"; \
  		docker compose exec mysql mysql -proot -e "create database if not exists db_name_test$$i;"; \
  		docker compose exec mysql mysql -proot -e "GRANT ALL PRIVILEGES ON db_name_test$$i.* TO 'db_user'@'%';"; \
  		docker compose run --rm backend-cli bash -c "TEST_TOKEN=$$i bin/console --env=test doctrine:migrations:migrate --no-interaction"; \
	done

check: composer-validate composer-audit cache-clear lint test check-openapi-diff check-openapi-schema # Проверка кода

fix: fixer-fix rector-fix # Запуск правок кода

up:	# Запуск контейнеров
	./setup_envs.bash
	docker compose up -d --force-recreate --remove-orphans

down: # Остановка контейнеров
	./setup_envs.bash
	docker compose down --remove-orphans

update:	# Обновление зависимостей
	docker compose run --rm backend-cli composer update

build: # Сборка образов и установка зависимостей
	./setup_envs.bash
	docker compose build
	make composer-install
	make up

db-migrate: # Миграции БД
	docker compose run --rm backend-cli bin/console doctrine:migrations:migrate --no-interaction

setup-transports: # Настройка очередей
	docker compose run --rm backend-cli bin/console messenger:setup-transports

run-backend: # Выполнение команды на бэкенде, пример: make run-backend echo "hello"
	./setup_envs.bash
	@docker compose run --rm backend-cli $(Arguments)

logs: # Просмотр логов сервиса, пример: make logs backend
	./setup_envs.bash
	@docker compose logs $(Arguments)

test-verbose: # Запуск тестов с детальным описанием
	./setup_envs.bash
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	docker compose run --rm backend-cli bash -c 'APP_ENV=test vendor/bin/phpunit --testdox'

test-single: # Запуск одного теста, пример: make test-single class=TaskCommentBodyTest
	./setup_envs.bash
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	@docker compose run --rm backend-cli bash -c "APP_ENV=test vendor/bin/phpunit --filter=$(class)"

lint: container-lint validate-doctrine-schema twig-lint fixer-check rector-check phpstan psalm deptrac-check deptrac-check-unassigned cache-prod-check

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
	docker compose run --rm backend-cli vendor/bin/phpstan analyse -c phpstan-config.neon --memory-limit 2G --ansi

psalm:	# Запустить psalm
	docker compose run --rm backend-cli vendor/bin/psalm

fixer-check: # Проверка стиля написания кода
	docker compose run --rm backend-cli vendor/bin/php-cs-fixer --config=php-cs-fixer-config.php fix --dry-run --diff --ansi

fixer-fix: # Фикс стиля написания кода
	docker compose run --rm backend-cli vendor/bin/php-cs-fixer --config=php-cs-fixer-config.php fix

rector-check: # Какой код необходимо отрефакторить
	docker compose run --rm backend-cli vendor/bin/rector process --dry-run --ansi

rector-fix: # Рефакторинг кода
	docker compose run --rm backend-cli vendor/bin/rector process --clear-cache

cache-prod-check: # Очистка кода для прода
	docker compose run --rm backend-cli bin/console cache:clear --env=prod

deptrac-check: # Контроль зависимостей
	docker compose run --rm backend-cli vendor/bin/deptrac analyse --fail-on-uncovered --report-uncovered --config-file=deptrac.yaml --cache-file=var/cache/.deptrac.cache

deptrac-check-unassigned: # Покрытие кода с deptrac
	docker compose run --rm backend-cli vendor/bin/deptrac debug:unassigned --config-file=deptrac.yaml --cache-file=var/cache/.deptrac.cache | tee /dev/stderr | grep 'There are no unassigned tokens'

test: # Запуск тестов
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	docker compose run --rm backend-cli bash -c 'APP_ENV=test vendor/bin/paratest -p4'

check-openapi-diff: # Валидация соответствия роутов и схемы openapi
	docker compose run --rm backend-cli bin/console app:openapi-routes-diff ./openapi.yaml

check-openapi-schema: # Валидация openapi.yaml
	docker compose run --rm vacuum lint /app/openapi.yaml -d -e

deprecations-check: # Проверка на устаревший функционал
	docker compose run --rm backend-cli bin/console debug:container --deprecations

help:	# Справка по командам
	@grep -E '^[a-zA-Z0-9 -]+:.*#'  Makefile | sort | while read -r l; do printf "\033[1;32m$$(echo $$l | cut -f 1 -d':')\033[00m:$$(echo $$l | cut -f 2- -d'#')\n"; done
