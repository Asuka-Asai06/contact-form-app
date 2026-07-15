FROM composer:2

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libzip-dev

RUN docker-php-ext-install gd zip