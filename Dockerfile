FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip opcache intl xml dom

# Установка Xdebug для code coverage
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Настройка Xdebug для coverage
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание пользователя приложения
RUN useradd -G www-data,root -u 1000 -m developer

# Установка рабочей директории
WORKDIR /var/www/html

# Установка прав доступа
RUN chown -R developer:www-data /var/www/html

# Переключение на пользователя приложения
USER developer

EXPOSE 9000

CMD ["php-fpm"]
