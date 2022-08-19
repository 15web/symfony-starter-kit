# symfony-starter-kit

[![Code quality status](https://github.com/15web/symfony-starter-kit/actions/workflows/check-code-quality.yml/badge.svg)](https://github.com/15web/symfony-starter-kit/actions)

Заготовка для старта проектов на Symfony 6, PHP 8.1, Mysql 8

## Запуск

```shell
git clone git@github.com:15web/symfony-starter-kit.git your-folder-name

cd ./your-folder-name/docker

./manage.bash i

```

Порты настраиваются в файле `./your-folder-name/docker/.env`

После настройки портов запустить `./manage.bash i`

Документация OpenAPI доступна по адресу http://localhost:8088/docs

Тестирование писем http://localhost:8088/mailhog

## Запуск проверок исходного кода

Предварительно нужно выполнить настройку тестового окружения:
```shell
cd ./your-folder-name/docker

./manage.bash install-test
```
Запуск проверок:
```shell
cd ./your-folder-name/docker

./manage.bash c
```

### Copyright and license

Copyright © [Studio 15](http://15web.ru), 2012 - Present.   
Code released under [the MIT license](https://opensource.org/licenses/MIT).

We use [BrowserStack](https://www.browserstack.com/) for cross browser testing.

![BrowserStack](http://15web.github.io/web-accessibility/images/browserstack_logo.png)
