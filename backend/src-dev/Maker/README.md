# Maker

Генератор простых CRUD модулей

```shell
docker compose run --rm backend bin/console make:module
# или
make crud-module
```

Необходимо ввести параметры:

* Название модуля, например `Page`
* Наименование сущности, например `Страница`
* Название класса сущности, например `Page`

Далее необходимо ввести список полей сущности, с указанием их типа

### Пример сгенерированного модуля Page

```
App\
    Page\                              Имя модуля
        Domain\                             Слой домена
            Page                                Сущность
            PageRepository                      Репозиторий        
        Http\                               Слой Http
            Admin\                              Разделение Http cлоя
                PageArgumentValueResolver           Резолвер страницы
                GetPageAction                       Ручка информации по странице
                GetPageListAction                   Ручка получения списка страниц
                CreatePageAction                    Ручка создания страницы
                CreatePageRequest                   Запрос создания страницы
                UpdatePageAction                    Ручка обновления страницы
                UpdatePageRequest                   Запрос обновления страницы    
                DeletePageAction                    Ручка удаления страницы
Dev\
    OpenApi\
        resources\
            admin
                \page.yaml                          Файл спецификации OpenApi
    Tests\
        Functional\
            SDK\
                Page                                Методы для работы с тестами модуля
            Page\
                Admin\
                    GetPageActionTest               Тест ручки информации по странице
                    GetPageListActionTest           Тест ручки получения списка страниц
                    CreatePageActionTest            Тест ручки создания страницы
                    UpdatePageActionTest            Тест ручки обновления страницы
                    DeletePageActionTest            Тест ручки удаления страницы
            
```
