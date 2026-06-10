FROM php:8.3-fpm-alpine

ARG APP_UID=1000
ARG APP_GID=1000

# System dependencies
RUN apk add --no-cache \
        bash \
        git \
        curl \
        unzip \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        autoconf \
        g++ \
        make \
        linux-headers \
        mysql-client \
        nodejs \
        npm \
        $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install -j$(nproc) \
        bcmath \
        intl \
        opcache \
        pdo_mysql \
        mbstring \
        zip \
        pcntl

 # Xdebug (dev only). Enable at runtime by setting XDEBUG_MODE=debug,develop
 # in docker-compose; defaults to "off" so production-style runs aren't slowed.
 RUN pecl install xdebug \
     && docker-php-ext-enable xdebug

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user matching host (for file permissions)
RUN addgroup -g ${APP_GID} -S app \
    && adduser -u ${APP_UID} -G app -S -h /home/app -s /bin/bash app \
    && mkdir -p /home/app/.composer \
    && chown -R app:app /home/app

# PHP config
COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod 0755 /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

USER app

ENTRYPOINT ["/bin/bash", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
