# syntax=docker/dockerfile:1.7

FROM composer:2.8 AS composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-progress --prefer-dist --optimize-autoloader

FROM node:20-alpine AS node
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci && npm run build

FROM php:8.3-fpm-alpine AS php
WORKDIR /var/www/html
RUN apk add --no-cache \
    icu-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    bash \
    supervisor \
    nginx
RUN docker-php-ext-install pdo_mysql intl zip
RUN pecl install redis && docker-php-ext-enable redis
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=composer /var/www/html/vendor ./vendor
COPY --from=node /var/www/html/public ./public
COPY . .
RUN composer dump-autoload --optimize
RUN addgroup -g 1000 www && adduser -G www -u 1000 -D www
RUN chown -R www:www storage bootstrap/cache
USER www

FROM php AS production
USER root
COPY --from=php /var/www/html /var/www/html
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/supervisor/queue.conf /etc/supervisor/conf.d/queue.conf
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache
USER www
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
