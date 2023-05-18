.PHONY: all

init: # Запуск проекта и установка зависимостей
	./setup_envs.bash
	docker compose build
	docker compose run --rm backend-cli composer install --no-scripts --prefer-dist
	docker compose up --detach --force-recreate --remove-orphans
	docker compose run --rm backend-cli bin/console doctrine:migrations:migrate --no-interaction
	docker compose run --rm backend-cli bin/console messenger:setup-transports

install-test: # Запуск проекта и подготовка тестового окружения
	./setup_envs.bash
	docker compose build backend mysql
	docker compose run --rm backend-cli composer install --no-scripts --prefer-dist --no-progress
	docker compose up --detach --force-recreate --remove-orphans backend mysql
	docker compose run --rm backend-cli bin/console doctrine:migrations:migrate --no-interaction
	docker compose run --rm backend-cli bin/console messenger:setup-transports
	@for i in 1 2 3 4 ; do \
  		docker compose exec -T mysql mysql -proot -e "drop database if exists db_name_test$$i;"; \
  		docker compose exec -T mysql mysql -proot -e "create database if not exists db_name_test$$i;"; \
  		docker compose exec -T mysql mysql -proot -e "GRANT ALL PRIVILEGES ON db_name_test$$i.* TO 'db_user'@'%';"; \
  		docker compose run --rm backend-cli bash -c "TEST_TOKEN=$$i bin/console --env=test doctrine:migrations:migrate --no-interaction"; \
	done

check: composer-validate composer-audit cache-clear lint test check-openapi-diff check-openapi-schema	# Запуск проверок

fix: fixer-fix rector-fix	# Запуск фиксеров

up:	# Запуск докера
	./setup_envs.bash
	docker compose up -d --force-recreate --remove-orphans

down:	# Остановка докера
	./setup_envs.bash
	docker compose down --remove-orphans

update:	# Обновление зависимостей
	docker compose run --rm backend-cli composer update

build:	# Билд образов и установка зависимостей
	./setup_envs.bash
	docker compose build
	docker compose run --rm backend-cli composer install --no-scripts --prefer-dist
	docker compose up -d --force-recreate --remove-orphans

run-backend:	# Выполнение команды на бэкенде, пример: make run-backend echo "hello"
	./setup_envs.bash
	@docker compose run --rm backend-cli $(Arguments)

logs:	# Просмотр логов сервиса, пример: make logs backend
	./setup_envs.bash
	@docker compose logs $(Arguments)

test-verbose:	# Запуск тестов
	./setup_envs.bash
	docker compose run --rm backend-cli bin/console --env=test cache:clear
	docker compose run --rm backend-cli bash -c 'APP_ENV=test vendor/bin/phpunit --testdox'

lint: container-lint validate-doctrine-schema twig-lint fixer-check rector-check phpstan psalm deptrac-check deptrac-check-unassigned cache-prod-check

composer-validate:
	docker compose run -T --rm backend-cli composer validate --strict

composer-audit:
	docker compose run -T --rm backend-cli composer audit --format=plain

cache-clear:
	docker compose run -T --rm backend-cli bin/console cache:clear

container-lint:
	docker compose run -T --rm backend-cli bin/console lint:container

validate-doctrine-schema:
	docker compose run -T --rm backend-cli bin/console doctrine:schema:validate -v

twig-lint:
	docker compose run -T --rm backend-cli bin/console lint:twig src/Mailer/templates

phpstan:	# Запустить phpstan
	docker compose run -T --rm backend-cli vendor/bin/phpstan analyse -c phpstan-config.neon --memory-limit 2G --ansi

psalm:	# Запустить psalm
	docker compose run -T --rm backend-cli vendor/bin/psalm

fixer-check:
	docker compose run -T --rm backend-cli vendor/bin/php-cs-fixer --config=php-cs-fixer-config.php fix --dry-run --diff --ansi

rector-check:
	docker compose run -T --rm backend-cli vendor/bin/rector process --dry-run --ansi

cache-prod-check:
	docker compose run -T --rm backend-cli bin/console cache:clear --env=prod

deptrac-check:
	docker compose run -T --rm backend-cli vendor/bin/deptrac analyse --fail-on-uncovered --report-uncovered --config-file=deptrac.yaml --cache-file=var/cache/.deptrac.cache

deptrac-check-unassigned:
	docker compose run -T --rm backend-cli vendor/bin/deptrac debug:unassigned --config-file=deptrac.yaml --cache-file=var/cache/.deptrac.cache | tee /dev/stderr | grep 'There are no unassigned tokens'

test:
	docker compose run -T --rm backend-cli bin/console --env=test cache:clear
	docker compose run -T --rm backend-cli bash -c 'APP_ENV=test vendor/bin/paratest -p4'

check-openapi-diff:
	docker compose run -T --rm backend-cli bin/console app:openapi-routes-diff ./openapi.yaml

check-openapi-schema:
	docker compose run -T --rm vacuum lint /app/openapi.yaml -d -e

fixer-fix:
	docker compose run -T --rm backend-cli vendor/bin/php-cs-fixer --config=php-cs-fixer-config.php fix

rector-fix:
	docker compose run -T --rm backend-cli vendor/bin/rector process --clear-cache

deprecations-check:
	docker compose run -T --rm backend-cli bin/console debug:container --deprecations

help:	#Show this help
	@grep -E '^[a-zA-Z0-9 -]+:.*#'  Makefile | sort | while read -r l; do printf "\033[1;32m$$(echo $$l | cut -f 1 -d':')\033[00m:$$(echo $$l | cut -f 2- -d'#')\n"; done
