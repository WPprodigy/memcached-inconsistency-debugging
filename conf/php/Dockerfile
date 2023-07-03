FROM php:fpm-alpine

RUN apk add libmemcached-dev libmemcached autoconf pkgconfig g++ zlib-dev make && pecl install memcached && \
docker-php-ext-enable memcached && apk del make g++ autoconf pkgconfig
