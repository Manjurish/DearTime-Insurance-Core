FROM laravelphp/vapor:php80
# Add the `ffmpeg` library...
RUN apk --update add ffmpeg

# Add the `mysql` client...
RUN apk --update add mysql-client

# Add the `gmp` PHP extension...
RUN apk --update add gmp gmp-dev
RUN docker-php-ext-install gmp

RUN apk add --no-cache ${PHPIZE_DEPS} imagemagick imagemagick-dev
RUN pecl install -o -f imagick\
    &&  docker-php-ext-enable imagick
RUN apk del --no-cache ${PHPIZE_DEPS}

# Update the `php.ini` file...
# Requires a `php.ini` file at the root of your project...
COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini

COPY . /var/task