FROM php:8.4-cli AS base

# Install system dependencies required by Composer and PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libzip-dev \
    zip \
    --no-install-recommends

RUN docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN mkdir /app


FROM base AS dev

WORKDIR /app

CMD ["php", "--server=0.0.0.0:80", "/app/src/server.php"]
