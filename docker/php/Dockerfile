FROM php:8.2-apache

RUN apt-get update
RUN apt-get install -y libpng-dev libzip-dev libicu-dev
RUN docker-php-ext-install mysqli gd zip pdo_mysql bcmath exif intl 

CMD ["apache2-foreground"]
