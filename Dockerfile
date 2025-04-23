# Use the official PHP image with Apache
FROM php:8.2-apache

# Install required packages and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Copy the custom vhost configuration
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the Apache DocumentRoot to Symfony's public/ directory (still useful for other configs)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set the working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions for the web server
RUN chown -R www-data:www-data /var/www/html

# Install PHP dependencies
RUN composer install 
RUN php bin/console doctrine:database:create --no-interaction
RUN php bin/console doctrine:migrations:migrate --no-interaction
RUN php bin/console doctrine:fixtures:load --no-interaction

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]