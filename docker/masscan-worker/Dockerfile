FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libssl-dev \
    supervisor \
    # masscan build dependencies
    gcc \
    make \
    libpcap-dev \
    && rm -rf /var/lib/apt/lists/*

# Install masscan from source for the latest version
RUN git clone https://github.com/robertdavidgraham/masscan /tmp/masscan \
    && cd /tmp/masscan \
    && make -j"$(nproc)" \
    && make install \
    && rm -rf /tmp/masscan

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip pcntl

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application code
COPY . .

# Install PHP dependencies (no dev, optimized autoloader)
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY docker/masscan-worker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
