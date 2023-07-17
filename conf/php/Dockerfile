FROM php:8.1-fpm

RUN apt-get update && \
apt-get install -y curl zlib1g-dev cmake protobuf-compiler bison flex

 # Download libmemcached
RUN curl -L -o /tmp/libmemcached.tar.gz "https://github.com/awesomized/libmemcached/archive/refs/tags/1.1.3.tar.gz" \
    && mkdir -p /usr/src/php/ext/libmemcached \
    && tar -C /usr/src/php/ext/libmemcached -zxvf /tmp/libmemcached.tar.gz --strip 1 \
    && rm /tmp/libmemcached.tar.gz

# Build libmemcached
RUN mkdir -p /usr/src/build-libmemcached \
    && cd /usr/src/build-libmemcached \
    && cmake /usr/src/php/ext/libmemcached \
    && make \
    && make install

# Install php-memcached
RUN curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/refs/tags/v3.2.0.tar.gz" \
    && mkdir -p /usr/src/php/ext/memcached \
    && tar -C /usr/src/php/ext/memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
    && docker-php-ext-configure memcached --disable-memcached-sasl \
    && docker-php-ext-install memcached \
    && rm /tmp/memcached.tar.gz
