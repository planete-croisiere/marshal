FROM php:8.2-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=dev \
    COMPOSER_MEMORY_LIMIT=-1

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        dirmngr \
        git \
        gnupg \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxslt1-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
        pkg-config \
        unzip \
        zip \
        zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

RUN npm install -g yarn

RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl

RUN docker-php-ext-install -j$(nproc) pdo_mysql pdo_sqlite

RUN docker-php-ext-install -j$(nproc) zip opcache

RUN docker-php-ext-install -j$(nproc) xsl

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

COPY docker/dev/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8081

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
