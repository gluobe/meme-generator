FROM composer:latest AS composer

WORKDIR /app
COPY ./app /app
RUN composer require aws/aws-sdk-php
RUN composer require microsoft/azure-storage-blob

FROM php:7.4-apache

RUN apt update && apt install -y python3-pip imagemagick wget unzip libssl-dev
RUN pip3 install wand
RUN pecl install mongodb
RUN pecl config-set php_ini /etc/php.ini
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini

# For Debugging purposes only
# RUN apt install -y nano
# RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini
# RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
# RUN echo "error_log = /dev/stderr" >> /usr/local/etc/php/php.ini
# RUN echo "error_reporting = E_ALL;" >> /usr/local/etc/php/php.ini

COPY --from=composer /app /var/www/html
RUN chown -R 33:33 /var/www/html
