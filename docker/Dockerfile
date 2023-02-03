ARG PHP=8.0
FROM php:${PHP}-cli-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENTRYPOINT ["php"]
