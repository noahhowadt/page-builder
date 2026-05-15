# Use the official PHP-Apache image with your PHP version
FROM php:8.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install OS packages, Node.js, and required PHP extension build dependencies.
RUN apt-get update && apt-get install -y --no-install-recommends \
  curl \
  git \
  nano \
  nodejs \
  npm \
  unzip \
  libcurl4-openssl-dev \
  libfreetype6-dev \
  libicu-dev \
  libjpeg62-turbo-dev \
  libonig-dev \
  libpng-dev \
  libpq-dev \
  libxml2-dev \
  libzip-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
  curl \
  gd \
  intl \
  mbstring \
  pdo_pgsql \
  zip \
  && a2enmod rewrite headers \
  && rm -rf /var/lib/apt/lists/*

# Use the official Composer binary instead of downloading the installer script.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration files
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY apache2.conf /etc/apache2/apache2.conf

# Copy Laravel application files
COPY . .

# Install dependencies, generate Wayfinder files, and build assets for production.
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
  && composer install --no-dev --no-interaction --optimize-autoloader \
  && php artisan wayfinder:generate \
  && npm install --no-audit --no-fund \
  && npm run build \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 775 storage bootstrap/cache \
  && rm -rf /root/.npm

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]