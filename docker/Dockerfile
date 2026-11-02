FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql
RUN printf "upload_max_filesize=5M\npost_max_size=55M\nmax_file_uploads=10\n" > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads/rooms \
    && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
