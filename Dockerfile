FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  libcurl4-openssl-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install curl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY docker/servername.conf /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

COPY docker/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN composer install --no-interaction --no-plugins --no-scripts --verbose && ls -la /var/www/html/vendor/autoload.php

EXPOSE 80

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
