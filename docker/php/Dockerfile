FROM php:8.4-fpm

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev

# Настраиваем PHP расширения
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    intl \
    zip \
    opcache

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем composer.json и composer.lock
COPY composer.json composer.lock ./

# Устанавливаем зависимости
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction
