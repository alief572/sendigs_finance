FROM composer:2 AS composer

FROM php:8.5.13-apache-trixie AS runtime

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        mbstring \
        bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Set Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Update Apache config to allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY php.ini /usr/local/etc/php/conf.d/sendigs.ini
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

FROM runtime AS application

COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist \
    && composer check-platform-reqs --no-dev

COPY . .
RUN mkdir -p application/cache application/logs uploads \
    && chown -R www-data:www-data application/cache application/logs uploads

EXPOSE 80
