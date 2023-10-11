FROM php:8.2.7-fpm

RUN apt-get update; \
    apt-get install -y --no-install-recommends unzip git;

COPY --from=composer:2.6.3 /usr/bin/composer /usr/bin/composer

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_pgsql intl sysvsem
RUN docker-php-ext-install bcmath

RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

ARG USER_ID
RUN groupadd --gid "$USER_ID" dev \
    && useradd --uid "$USER_ID" --gid dev --shell /bin/bash --create-home dev

COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN su dev -c 'mkdir -p /home/dev/.composer/ /home/dev/app/'

USER dev

WORKDIR /app/
