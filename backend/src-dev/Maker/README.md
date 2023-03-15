# Maker

Генератор простых CRUD модулей

```shell
docker-compose run --rm backend-cli bin/console make:module
```
Необходимо ввести параметры:

* Название модуля
* Название сущности

Для сущности нужно также ввести поля и их типы

Пример сгенерированного модуля Page (страница)

```
App
    Page                               Имя модуля
        Domain                              Слой домена
            Page                                Сущность
            Pages                               Репозиторий        
        Http                                Слой Http
            InfoAction.php                      Ручка информации по странице
            PageArgumentValueResolver           ArgumentValueResolver страницы
            CreateAction.php                    Ручка создания страницы
            CreateRequest.php                   Запрос создания страницы
            UpdateAction.php                    Ручка обновления страницы
            UpdateRequest.php                   Запрос обновления страницы    
            RemoveAction.php                    Ручка удаления страницы
```
