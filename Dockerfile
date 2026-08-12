FROM php:8.2-fpm-alpine

# Install Nginx and required dependencies/extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite-dev

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_sqlite bcmath gd zip

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Set permissions for Laravel storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
