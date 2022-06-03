FROM php:8.1-fpm as base

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_pgsql intl

RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

ARG USER_ID
RUN groupadd --gid "$USER_ID" dev \
    && useradd --uid "$USER_ID" --gid dev --shell /bin/bash --create-home dev

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

RUN apt-get update; \
    apt-get install -y --no-install-recommends unzip;

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN su dev -c 'mkdir -p /home/dev/.composer/ /home/dev/app/'

USER dev

WORKDIR /app/
