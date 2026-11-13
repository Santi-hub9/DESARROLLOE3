FROM php:8.2-apache
RUN apt-get update && apt-get install -y --no-install-recommends tesseract-ocr tesseract-ocr-spa poppler-utils unzip git \
    && docker-php-ext-install pdo_mysql && a2enmod rewrite && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist
COPY . .
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo "upload_max_filesize=9M\npost_max_size=10M\nmax_execution_time=300" > /usr/local/etc/php/conf.d/talentia.ini \
    && mkdir -p storage/cv storage/docs && chown -R www-data:www-data storage
