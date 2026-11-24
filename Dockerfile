FROM node:22-bookworm-slim AS remote-relay-deps
WORKDIR /build
COPY remote/relay/package.json remote/relay/package-lock.json ./
RUN npm ci --omit=dev --ignore-scripts

FROM php:8.2-apache

# Install PDO MySQL and SSL certificates for cloud database connections (TiDB Cloud)
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    libstdc++6 \
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
COPY --from=remote-relay-deps /usr/local/bin/node /usr/local/bin/node
COPY --from=remote-relay-deps /build/node_modules /var/www/html/remote/relay/node_modules
RUN chmod +x /var/www/html/remote/start-server.sh

# Ensure all runtime writable directories exist and set correct permissions
RUN mkdir -p /var/www/html/uploads /var/www/html/storage /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/uploads \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/public/uploads

# Render injects $PORT (usually 10000). Default to 80 if not set.
ENV PORT=80

EXPOSE 80 10000

# Node binds Render's $PORT; PHP/Apache runs on a private loopback listener.
CMD ["/var/www/html/remote/start-server.sh"]
