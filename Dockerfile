# https://hub.docker.com/r/danbelden/ubuntu-php72-fpm-nginx/
FROM danbelden/ubuntu-php72-fpm-nginx
MAINTAINER Dan Belden <me@danbelden.com>

# Mount the custom nginx and FPM config files
COPY docker/nginx/default /etc/nginx/sites-available/default
COPY docker/fpm/www.conf /usr/local/etc/php/fpm/pool.d/www.conf

# Set the working directory as code root
WORKDIR /var/www/html
