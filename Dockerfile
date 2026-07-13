FROM php:8.4-fpm-alpine

RUN apk add --no-cache bash curl icu-dev libzip-dev zlib-dev oniguruma-dev postgresql-dev nodejs npm git make g++ python3 \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PORT=8080

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
CMD ["sh", "-lc", "php artisan serve --host=0.0.0.0 --port=${PORT}"]