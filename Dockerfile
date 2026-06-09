FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    python3 \
    python3-pip \
    libzip-dev \
    zip \
    curl

RUN docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN pip3 install --break-system-packages --no-cache-dir -r requirements.txt


RUN php artisan key:generate --force || true

EXPOSE 8080

CMD rm -f database/database.sqlite && touch database/database.sqlite && php artisan migrate:fresh --force && php artisan serve --host=0.0.0.0 --port=8080