FROM php:8.3.13-fpm-alpine

RUN docker-php-ext-install -j"$(nproc)" pdo pdo_mysql   


RUN apk add --update --no-cache libmemcached-dev autoconf g++ make zlib-dev

RUN pecl install igbinary && docker-php-ext-enable igbinary
RUN pecl install memcached && docker-php-ext-enable memcached


# Copy the php config file
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy the application code
COPY . /app

VOLUME ["/app"]
