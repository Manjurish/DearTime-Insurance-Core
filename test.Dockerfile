FROM laravelphp/vapor:php80

# Add the `ffmpeg` library...
RUN apk --update add ffmpeg

# Add the `mysql` client...
RUN apk --update add mysql-client

# Add the `gmp` PHP extension...
RUN apk --update add gmp gmp-dev
RUN docker-php-ext-install gmp

# Update the `php.ini` file...
# Requires a `php.ini` file at the root of your project...
COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini

# Place application in Lambda application directory...
COPY . /var/task