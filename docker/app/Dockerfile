FROM php:8.2-apache

RUN set -eux; \
    docker-php-ext-install pdo_mysql; \
    a2enmod rewrite headers

COPY docker/app/vhost.conf /etc/apache2/sites-available/000-default.conf
