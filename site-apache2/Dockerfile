<<<<<<< Updated upstream
#image
FROM php:apache
#Metadata du container
LABEL authors="Patrick"
LABEL maintainer="Patrick"
LABEL version="1.0"
LABEL description="webserver based image for lab"
#WORKDIR : repertoire de reference
WORKDIR /var/www/html

ENV HOME=/root

RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y nano wget git net-tools iputils-ping libpq-dev autoconf && \
    apt-get clean

RUN docker-php-ext-install pgsql
RUN pecl install redis && docker-php-ext-enable redis
=======
FROM php:apache

RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y \
        nano wget git \
        net-tools iputils-ping \
        libpq-dev \
        # autoconf requis pour compiler l'extension Redis via pecl
        autoconf && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensions PostgreSQL
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Extension Redis — installée via PECL (gestionnaire de paquets PHP)
# pecl install compile et installe l'extension
# docker-php-ext-enable l'active dans la configuration PHP
RUN pecl install redis && docker-php-ext-enable redis

# mod_rewrite pour .htaccess → public/
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
>>>>>>> Stashed changes
