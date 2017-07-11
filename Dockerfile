FROM php:5.6-apache

RUN apt-get update && apt-get upgrade -y && \
docker-php-ext-install mysqli && \
a2enmod rewrite && \
service apache2 restart && \
rm -rf /var/lib/apt/lists/*