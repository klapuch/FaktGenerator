FROM php:7.4-fpm-buster

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update -qq
RUN apt-get install -y curl git zip unzip make bash file libzip-dev libpng-dev libbz2-dev libpq-dev libxslt-dev
RUN docker-php-ext-install pdo_pgsql sysvsem zip xsl xml
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN apt-get clean

COPY php.ini /usr/local/etc/php/php.ini
