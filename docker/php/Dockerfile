FROM php:8.3-fpm-bullseye

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git curl unzip libzip-dev libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libssl-dev libkrb5-dev libc-client-dev libxslt1-dev libgmp-dev \
    && rm -rf /var/lib/apt/lists/*

# Enable extensions needed by the app (includes imap for Webklex)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl=/usr \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip intl gd imap bcmath opcache exif gmp xsl

# Install Composer inside the container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
