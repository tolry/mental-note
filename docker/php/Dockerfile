FROM php:8.3.13-fpm

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    pdo \
    pdo_mysql \
    memcached \
    igbinary \
    zip

RUN apt-get update && \
    apt-get install -y \
      imagemagick \
      git

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy the php config file
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /app

# Copy the application code
COPY . .

RUN chown -R www-data:www-data /app/var /app/public/thumbnails /app/public/bundles

VOLUME ["/app"]

CMD ["./docker/php/startup.sh"]