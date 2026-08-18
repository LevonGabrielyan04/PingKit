FROM php:8.5-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libzip-dev \
    libonig-dev \
    libssl-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip pcntl

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

RUN set -eux; \
    groupadd -g 1000 app; \
    useradd -m -u 1000 -g app app; \
    mkdir -p storage bootstrap/cache; \
    chown -R app:app /var/www/html/storage /var/www/html/bootstrap/cache; \
    # Ensure PHP-FPM worker pool runs as the same non-root user.
    if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then \
      sed -ri 's/^user = .*/user = app/; s/^group = .*/group = app/; s/^listen\.owner = .*/listen.owner = app/; s/^listen\.group = .*/listen.group = app/' /usr/local/etc/php-fpm.d/www.conf; \
    fi; \
    php artisan storage:link || true

USER app

EXPOSE 9000

CMD ["php-fpm"]

