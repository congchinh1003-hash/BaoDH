FROM php:8.2-apache

# Install PDO MySQL and SSL certificates for cloud database connections (TiDB Cloud)
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache AllowOverride for .htaccess routing
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set application directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Ensure all runtime writable directories exist and set correct permissions
RUN mkdir -p /var/www/html/uploads /var/www/html/storage /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/uploads \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/public/uploads

# Render injects $PORT (usually 10000). Default to 80 if not set.
ENV PORT=80

EXPOSE 80 10000

# Bind Apache dynamically to Render's $PORT at runtime
CMD ["sh", "-c", "sed -i \"s/Listen [0-9]*/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:[0-9]*>/<VirtualHost *:${PORT:-80}>/g\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
