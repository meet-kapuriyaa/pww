# ==========================================================
# Stage 1: Build Frontend Assets with Node
# ==========================================================
FROM node:18-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ==========================================================
# Stage 2: Compile PHP Dependencies and Run Production Server
# ==========================================================
FROM php:8.2-apache

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip bcmath opcache

# Enable Apache rewrite module for Laravel routing
RUN a2enmod rewrite

# Configure Apache Document Root to Laravel's /public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy Vite compiled assets from the node builder stage
COPY --from=node-builder /app/public/build ./public/build

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel directory access
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
