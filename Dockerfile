FROM php:8.0-cli as php-base
WORKDIR /build
RUN apt update && apt install -y \
    libicu-dev \
    librdkafka-dev \
    libzip-dev \
    nano \
    procps \
    && pecl install \
    rdkafka \
    && docker-php-ext-install \
    bcmath \
    intl \
    mysqli \
    pcntl \
    pdo \
    pdo_mysql \
    zip \
    sockets \
    && docker-php-ext-enable \
    rdkafka \
    && apt purge -y $PHPIZE_DEPS \
    && apt autoremove -y --purge \
    && apt clean all

RUN curl -sS https://getcomposer.org/installer | php -- \
  --install-dir=/usr/bin --filename=composer

FROM php-base as php-build
COPY composer.lock composer.json ./

RUN composer install --verbose --ignore-platform-reqs --prefer-dist --no-progress --no-interaction --optimize-autoloader

FROM php-base as php-runtime
WORKDIR /app

COPY . .
COPY --from=php-build /build/vendor/ ./vendor/

CMD [ "php", "bin/application.php", "app:r2k" ]

