# symfony-starter-kit

[![Code quality status](https://github.com/15web/symfony-starter-kit/actions/workflows/check-code-quality.yml/badge.svg?branch=main)](https://github.com/15web/symfony-starter-kit/actions)

Заготовка для старта проектов на Symfony 7, PHP 8.3, Posgres 17

## Модули

Проект имеет модульную архитектуру, что позволяет каждый модуль реализовывать индивидуально.
Модули разделены согласно своему контексту и должны иметь слабые связи между собой (cohesion).

Логика CRUD-модулей ограничена простыми операциями с данными, что упрощает их реализацию.

- [Infrastructure - инфраструктура](backend/src/Infrastructure/README.md)
- [Article - статьи как пример CRUD-модуля](backend/src/Article/README.md)
- [Mailer - отправка электронной почты](backend/src/Mailer/README.md)
- [Ping - пинг приложения](backend/src/Ping/README.md)
- [SEO - SEO модуль](backend/src/Seo/README.md)
- [Setting - настройки приложения](backend/src/Setting/README.md)
- [Task - todo-лист как пример модуля с богатой логикой](backend/src/Task/README.md)
- [User - пользователи](backend/src/User/README.md)
- [Maker - генератор простых CRUD модулей](backend/src-dev/Maker/README.md)



## Запуск

```shell
git clone git@github.com:15web/symfony-starter-kit.git your-folder-name

cd ./your-folder-name/

make init

```

Порты настраиваются в файле `./.env`

После настройки портов запустить `make init`

Документация OpenAPI доступна по адресу http://localhost:8088/docs

[Исходник OpenAPI документации проекта](backend/src-dev/openapi.yaml)

Тестирование писем http://localhost:8088/mailhog

## Запуск проверок исходного кода

Все инструменты по проверке кода и тесты вынесены в отдельную папку src-dev. Это позволяет легко исключить ее из деплоя на production. 

Запуск проверок:
```shell
make check
```
Показать список доступных команд:
```shell
make help
```

## Инструкция по очистке, для старта проектов

Для старта проекта необходимо удалить ненужные:
- Модули, т.е. все директории в `backend/src`, кроме `Infrastructure`
- Тесты из директорий:
  - `backend/tests/Command`
  - `backend/tests/Functional`, кроме `backend/tests/Functional/SDK/ApiWebTestCase.php`
  - `backend/tests/Unit`
- Все миграции из директории `backend/migrations` и сгенерировать новые.
- Переменные окружения из файла `docker/backend/.env.dist`
- Разделы документации из файла `backend/src-dev/openapi.yaml`
- Слои и правила `deptrac` из файла `backend/src-dev/deptrac.yaml`
- Убрать секцию `paths` в конфиге `twig` из файла `backend/config/packages/twig.yaml`

### Copyright and license

Copyright © [Studio 15](http://15web.ru), 2012 - Present.   
Code released under [the MIT license](https://opensource.org/licenses/MIT).

We use [BrowserStack](https://www.browserstack.com/) for cross browser testing.

![BrowserStack](http://15web.github.io/web-accessibility/images/browserstack_logo.png)
