# symfony-starter-kit

[![Code quality status](https://github.com/15web/symfony-starter-kit/actions/workflows/check-code-quality.yml/badge.svg)](https://github.com/15web/symfony-starter-kit/actions)

Заготовка для старта проектов на Symfony


## Запуск

```shell
git clone git@github.com:15web/symfony-starter-kit.git your-folder-name

cd ./your-folder-name/docker

./manage.bash i

```

Настроить порты можно тут `./your-folder-name/docker/.env`, потом запустить `./manage.bash i`

## Проверка кода

```shell
cd ./your-folder-name/docker

./manage.bash c
```

## Документация OpenAPI

Документация OpenAPI доступна по адресу http://localhost:8088/docs/

Заменить 8088 порт на тот который указан в `./your-folder-name/docker/.env`
