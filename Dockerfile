FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-interaction --no-dev --optimize-autoloader

RUN php artisan key:generate --no-interaction || true

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080