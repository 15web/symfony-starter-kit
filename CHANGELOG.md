# Changelog

## 2026-10-21 — Обновление зависимостей до последних мажорных версий (Symfony 8.1, PHPUnit 13)

### Composer: мажорные обновления

- `extra.symfony.require`: `7.4.*` → `8.1.*`; все пакеты symfony `^7.3` → `^8.1` (7.4.21 → **8.1.9**), включая dev-пакеты (browser-kit, css-selector, psr-http-message-bridge, stopwatch, web-profiler-bundle).
- `phpunit/phpunit` `^12.0` → `^13.0` (12.5.40 → **13.4.3**); `brianium/paratest` 7.20.0 → 7.27.3 — вместе с PHPUnit 13 обновилась вся цепочка `phpunit/*` и `sebastian/*` (php-code-coverage 14, comparator 8 и т.д.).
- `symfony/monolog-bundle` `^3.10` → `^4.0` (3.11.2 → **4.1.0**).
- `doctrine/doctrine-migrations-bundle` `^3.3` → `^4.0` (3.7.1 → **4.0.1**).
- `webmozart/assert` `^1.11` → `^2.0` (1.12.1 → **2.5.0**).
- Удалён `symfony/proxy-manager-bridge` (`~6.4.0`) вместе с транзитивными `friendsofphp/proxy-manager-lts` и `laminas/laminas-code`: мост выведен из состава symfony ещё в 7.0, в ветке 8.x несовместим; lazy-сервисы используют нативные lazy objects PHP 8.4+.
- Транзитивно удалён `masterminds/html5` — symfony/dom-crawler 8 парсит HTML нативным HTML5-парсером; добавлен `symfony/polyfill-deepclone` (нужен symfony/var-exporter 8).

### Правки кода под новые версии

- `src/Mailer/config.php`, `src/User/config.php`: Symfony 8 убрал «флюентный» PHP-формат семантической конфигурации с внедряемыми в closure типизированными `Symfony\Config\*Config`-билдерами (и их генерацию) — конфиги переведены на closure только с `ContainerConfigurator` и `$container->extension('framework', [...])`; логика условиями по окружению (`$container->env()`) сохранена. В `messenger.routing` вложенный `senders` заменён на плоский список отправителей (депрекация symfony 8.1, удаление в 9.0).
- `src/User/SignIn/Http/SignInAction.php`, `src/User/Password/Http/ChangePasswordAction.php`: параметры `RateLimiterFactoryInterface $signInLimiter` / `$changePasswordLimiter` получили `#[Target('sign_in')]` / `#[Target('change_password')]` — в symfony 8.1 сопоставление именованного autowiring-алиаса только по имени параметра объявлено устаревшим.
- `config/packages/doctrine.yaml`: удалён устаревший `doctrine.orm.controller_resolver.auto_mapping` (deprecated c doctrine-bundle 3.1, удаление в 4.0).
- `src-dev/Rector/rector.config.php`: в `withSkip` добавлен `AddParamTypeToRefactorMethodRector` — новое правило rector 2.7 добавляет сужающие `@param Xxx $node` в док-блоки `refactor()` кастомных правил, после чего phpstan падает на контравариантности (`method.childParameterType`); док-типы, добавленные правилом, откачены.
- `README.md`: описание приведено к фактическим версиям (Symfony 8, PHP 8.5, PostgreSQL 18).

### Что оказалось совместимым без правок

- Миграции doctrine: все классы наследуют `AbstractMigration` — `ContainerAwareInterface` (удалён в migrations-bundle 4.0) не использовался.
- YAML-конфиги пакетов (monolog, doctrine, messenger, framework) не используют опции, удалённые в мажорных версиях (в частности, monolog 4: `excluded_404s`, типы `elasticsearch`/`mongo`/`sentry`).
- Тесты: не используют удалённые в PHPUnit 13 API (`assertContainsOnly()`, `containsOnly()`, `Assert::isType()`, `#[RunClassInSeparateProcess]`) и моки — миграция тестов не потребовалась; новые assertions PHPUnit 13 (`assertArraysAre*`) к скалярным сравнениям в тестах не применимы.
- Код приложения не использует API, удалённые в symfony 8.0 (`Request::get()`, `getDefaultName()`, `#[TaggedIterator]`, `PropertyInfo\Type`, `LazyGhostTrait` и др.).

### Результаты проверок

- `composer update` — успешно, `composer.lock` перегенерирован; `composer validate --strict`, `composer audit`, `composer normalize --dry-run`, `db-validate` — OK.
- `make lint`: check-git-lf, container-lint, twig-lint, fixer-check, rector-check, phpstan, psalm, deptrac-check-unassigned, cache-prod-check — OK (phpstan/psalm без новых ошибок, baselines не менялись).
- `make test` — **252 tests, 553 assertions, OK** (PHP 8.5.9, PHPUnit 13.4.3, ParaTest 7.27.3).
- `make check-openapi-diff` — OK (маршруты не менялись); spectral (`stoplight/spectral` без флага `-it`) — «No results with a severity of 'warn' or higher».
- Депрекации: `cache:clear` для dev/test/prod и `debug:container --deprecations` — без записей.

### Замечания

- `make spectral` локально падает в окружении без TTY из-за флага `-it` в Makefile (`cannot attach stdin to a TTY-enabled container`); в CI этот target запускается без `-it` и работает. Для ручного запуска вне терминала: `docker run --rm -v ${PWD}/backend:/app stoplight/spectral:latest lint /app/src-dev/OpenApi/openapi.yaml -F warn --ruleset=/app/src-dev/OpenApi/.spectral.yaml`.

## 2026-10-19 — Обновление версий в Docker-образах и всех зависимостей composer

### Docker-образы

- PHP `8.4.14` → `8.5.9`, Composer `2.8.12` → `2.10.3`, nginx `1.31.7`, PostgreSQL `18.6`, MailHog → MailPit `v1.30.2`.
- `docker/backend/Dockerfile`: расширение `pcov` (1.0.12) теперь собирается из тега на GitHub вместо `pecl.php.net` (PECL недоступен из сетей без доступа к `pecl.php.net`; GitHub доступен в любом окружении, где работает сборка).
- `docker/backend/www.conf`: `listen = 127.0.0.1:9000` → `listen = 9000` — fpm слушает на всех интерфейсах контейнера, nginx может подключиться к backend (раньше `/` отдавал 502; `/docs` и `/mailpit` работали, так как идут через прокси).
- `docker/.env.dist`: добавлен `MAILPIT_HTTP_PORT=8025` (порт UI mailpit публикуется без предупреждений compose).
- `backend/.env`: `serverVersion=17` → `18` в `DATABASE_URL` — синхронизировано с образом postgres 18.6.
- PostgreSQL: данные предыдущей major-версии (17) несовместимы с 18.6 — при обновлении существующего окружения нужна миграция данных (`pg_dump`/`pg_restore`) либо пересоздание volume (`docker compose down -v`).

### Composer: обновление всех зависимостей

- `extra.symfony.require`: `7.3.*` → `7.4.*` — symfony обновлён до **7.4.21** (LTS). В ветке 7.3.x нет фиксов для новых CVE-2026 (фиксы вышли в 7.4.12/7.4.13/7.4.18/7.4.19), поэтому composer 2.10 блокировал резолв security-advisories.
- `vimeo/psalm` `6.13.1` → **6.20.0** — первая версия с поддержкой PHP 8.5 (иначе `composer install` падал: «your php version (8.5.9) does not satisfy that requirement»).
- `league/openapi-psr7-validator` `0.21` → `0.24`, `league/uri` `6.8.0` → `7.9.0`, `psr/http-message` `1.1` → `2.0` — в league/uri 6.8.0 под PHP 8.5 deprecation-сигнатура роняла psalm.
- Прочие крупные обновления: `doctrine/orm` 3.5.3 → 3.7.5, `doctrine/doctrine-migrations-bundle` 3.5.0 → 3.7.1, `friendsofphp/php-cs-fixer` 3.89.1 → 3.95.27, `rector` 2.2 → 2.7, `phpunit` 12.5.40 + `paratest` 7.20, `ergebnis/composer-normalize` 2.54.

### Правки кода под новые версии инструментов

- `src-dev/Rector/rector.config.php`: в `withSkip` добавлен `ParentTestClassConstructorRector` — правило из rector 2.7 генерирует конструкторы `parent::__construct(self::class)` для классов-тестов, а `PHPUnit\Framework\TestCase::__construct` в PHPUnit 12.5 объявлен `final` (фатал при загрузке класса, падал phpstan/psalm).
- Применены `make rector-fix` (~23 файла: в основном `#[Autowire('%param%')]` → `#[Autowire(param: '%param%')]`) и `make fixer-fix` (`src-dev/Maker/Vendor/EntityGenerator.php`).
- phpstan: в 16 методов-провайдеров тестов добавлены док-типы `@return Iterator<array-key, mixed>` (новая проверка `missingType.generics`); в 4 кастомных rector-правилах (`src-dev/Rector/Rules/*`) убраны сужающие `@param Xxx $node` из док-блоков `refactor()` (нативный тип `Node` + `@var`-каст в теле остаются; phpstan стал проверять контравариантность через phpdoc); в `AssertMustHaveMessageRector` расширен union параметра closure с `ArgPlaceholder`.
- psalm: `TaskCommentId::$value` типизирован как `UuidV7` (консистентно с `getValue(): UuidV7`); в `ClassDocCommentFixer` и `TestdoxFixer` для `static`-кэша добавлена `@var`-аннотация (устранён `MixedAssignment`); `src-dev/psalm-baseline.xml` перегенерирован (удалена устаревшая запись `InvalidArgument` для `ApiRequestMapper`).
- `backend/.gitignore`: добавлен `/config/reference.php` — новый автогенерируемый файл конфигурации symfony 7.4.

### Результаты проверок

- `docker compose build` — успешно, все образы собраны.
- Сервисы поднимаются: pgsql healthy (PG 18.6, 11 миграций, схема валидна), mailpit healthy, backend/messenger/docs/nginx запускаются, транспорты messenger настроены.
- `composer validate --strict`, `composer normalize --dry-run`, `composer audit` — без уязвимостей, `composer check-platform-reqs` на PHP 8.5.9 — всё OK.
- `make lint`: container-lint, twig-lint, fixer-check, rector-check, phpstan, psalm, deptrac-check-unassigned, cache-prod-check — OK.
- `make check-openapi-diff`, `make check-openapi-schema` (spectral) — OK.
- `make test` — **252 tests, 553 assertions, OK** (PHP 8.5.9).
- Smoke: `GET /` через nginx доходит до приложения (404 от Symfony — маршрута на `/` нет), `/docs` → 301, `/mailpit/` → 200.

### Замечания

- nginx кэширует DNS при старте: после пересоздания контейнера backend (смены IP) выполните `docker compose restart nginx`, иначе возможен 502 на устаревший адрес.
- Сетевые особенности окружения, в котором выполнялось обновление: `pecl.php.net`, `packagist.org`/`flex.symfony.com` частично недоступны (pecl заменён сборкой из GitHub; актуальный symfony/flex забирает рецепты с `raw.githubusercontent.com`). В окружении с обычным доступом эти ограничения не влияют на сборку и работу.
