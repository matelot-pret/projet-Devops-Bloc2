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
    apt-get install -y nano wget git net-tools iputils-ping libpq-dev && \
    apt-get clean

RUN docker-php-ext-install pgsql
=======
FROM php:apache

# Mise à jour et outils de diagnostic réseau
RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y \
        nano wget git \
        net-tools iputils-ping \
        libpq-dev && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Extension PostgreSQL (PDO + pgsql natif)
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# ── mod_rewrite ──────────────────────────────────────────────────────────────
# Nécessaire pour que le .htaccess à la racine redirige vers public/.
# Sans mod_rewrite, Apache ignore complètement les directives RewriteRule.
RUN a2enmod rewrite

# ── AllowOverride All ────────────────────────────────────────────────────────
# Par défaut Apache ignore les .htaccess (AllowOverride None).
# On passe à AllowOverride All pour autoriser les directives du .htaccess.
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
>>>>>>> Stashed changes
