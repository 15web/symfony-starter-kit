# symfony-starter-kit

[![Code quality status](https://github.com/15web/symfony-starter-kit/actions/workflows/check-code-quality.yml/badge.svg?branch=main)](https://github.com/15web/symfony-starter-kit/actions)

Заготовка для старта проектов на Symfony 6, PHP 8.2, Mysql 8

## Запуск

```shell
git clone git@github.com:15web/symfony-starter-kit.git your-folder-name

cd ./your-folder-name/

make install

```

Порты настраиваются в файле `./.env`

После настройки портов запустить `make install`

Документация OpenAPI доступна по адресу http://localhost:8088/docs

Тестирование писем http://localhost:8088/mailhog

## Запуск проверок исходного кода

Предварительно нужно выполнить настройку тестового окружения:
```shell
make install-test
```
Запуск проверок:
```shell
make check
```
Показать список доступных команд:
```shell
make help
```

## Генерация кода

[Maker](./backend/src-dev/Maker/README.md)

## Инструкция по очистке, для старта проектов

Для старта проекта необходимо удалить ненужные:
- Модули, т.е. все директории в `backend/src`, кроме `Infrastructure`
- Тесты из директорий:
  - `backend/tests/Command`
  - `backend/tests/Functional`, кроме `backend/tests/Functional/SDK/ApiWebTestCase.php`
  - `backend/tests/Unit`
- Все миграции из директории `backend/migrations` и сгенерировать новые.
- Переменные окружения из файла `docker/backend/.env.dist`
- Задания `cron` из файла `docker/backend/cron/crontab`
- Разделы документации из файла `backend/openapi.yaml`
- Слои и правила `deptrac` из файла `backend/deptrac.yaml`
- Убрать секцию `paths` в конфиге `translation` из файла `backend/config/packages/translation.yaml`

### Copyright and license

Copyright © [Studio 15](http://15web.ru), 2012 - Present.   
Code released under [the MIT license](https://opensource.org/licenses/MIT).

We use [BrowserStack](https://www.browserstack.com/) for cross browser testing.

![BrowserStack](http://15web.github.io/web-accessibility/images/browserstack_logo.png)
