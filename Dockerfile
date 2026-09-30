FROM php:8.3-fpm-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupmod --gid "${GID}" www-data \
    && usermod --uid "${UID}" --gid "${GID}" www-data

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /usr/local/bin/playground-app-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/playground-app-entrypoint \
    && chmod +x /usr/local/bin/playground-app-entrypoint

ENTRYPOINT ["/usr/local/bin/playground-app-entrypoint"]
CMD ["php-fpm"]
