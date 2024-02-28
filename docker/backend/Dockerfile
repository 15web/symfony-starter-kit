FROM php:8.3.3-fpm as base

RUN apt-get update; \
    apt-get install -y --no-install-recommends unzip git;

COPY php.ini /usr/local/etc/php/php.ini

COPY --from=composer:2.7.1 /usr/bin/composer /usr/bin/composer

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_pgsql intl sysvsem
RUN docker-php-ext-install bcmath

RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

ARG USER_ID
RUN groupadd --gid "$USER_ID" dev \
    && useradd --uid "$USER_ID" --gid dev --shell /bin/bash --create-home dev

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

RUN su dev -c 'mkdir -p /home/dev/.composer/ /home/dev/app/'

USER dev

WORKDIR /app/

FROM base as messenger

USER root

RUN apt-get update; \
    apt-get install -y --no-install-recommends supervisor;

# https://symfony.com/doc/current/messenger.html#graceful-shutdown
RUN install-php-extensions pcntl

COPY messenger/messenger-worker.conf /etc/supervisor/conf.d/messenger-worker.conf
COPY messenger/supervisord.conf /etc/supervisor/supervisord.conf

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]

FROM base as cron

USER root

RUN apt-get update; \
    apt-get install -y --no-install-recommends cron;

COPY cron/crontab /root/crontab

RUN crontab < /root/crontab

COPY cron/start.bash /root/cron/start.bash
CMD ["/root/cron/start.bash"]
