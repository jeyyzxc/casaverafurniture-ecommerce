# Use PHP 8.2 for Laravel
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    postgresql-client \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Expose port (Render will provide PORT env var)
EXPOSE 8080

# Create startup script that checks requirements and runs migrations
RUN echo '#!/bin/sh\n\
set -e\n\
echo "Starting Laravel application..."\n\
echo "Port: ${PORT:-8080}"\n\
\n\
# Check if APP_KEY is set\n\
if [ -z "$APP_KEY" ]; then\n\
    echo "WARNING: APP_KEY is not set. Generating one..."\n\
    php artisan key:generate --force || true\n\
fi\n\
\n\
# Run migrations (only if not already run)\n\
echo "Running database migrations..."\n\
php artisan migrate --force || echo "Migration failed or already run"\n\
\n\
# Seed database if empty\n\
echo "Running database seeders..."\n\
php artisan db:seed --force || echo "Seeding skipped (data may already exist)"\n\
\n\
# Cache configuration for better performance\n\
php artisan config:cache || true\n\
php artisan route:cache || true\n\
php artisan view:cache || true\n\
\n\
# Start Laravel server\n\
echo "Starting server on port ${PORT:-8080}..."\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Start Laravel server
CMD ["/usr/local/bin/start.sh"]
