FROM laravelphp/vapor:php80
# Add the `ffmpeg` library...
RUN apk --update add ffmpeg

# Add the `mysql` client...
RUN apk --update add mysql-client

# Add the `gmp` PHP extension...
RUN apk --update add gmp gmp-dev
RUN docker-php-ext-install gmp

COPY . /var/task