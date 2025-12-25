# 1. Update this to match your local PHP version (8.5)
# If this image fails to pull, try 'php:8.4-apache' instead.
FROM php:8.5-apache

# 2. Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip

# 3. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 4. Set web root to public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 7. Set workdir
WORKDIR /var/www/html

# 8. Copy files
COPY . .

# 9. Build app
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# 10. Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache


