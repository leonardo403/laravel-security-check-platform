FROM php:8.4-fpm


RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

#RUN chown -R www-data:www-data /var/www
RUN git config --global --add safe.directory /var/www

COPY composer.json composer.lock ./

COPY . .

RUN mkdir -p storage bootstrap/cache

RUN chmod 777 -R \
        storage \
        bootstrap/cache


RUN composer install \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
