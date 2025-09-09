FROM php:8.1-apache

COPY --from=composer:2.6.4 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
    git zip unzip libicu-dev default-mysql-client vim curl gnupg2 \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql intl \
    && a2enmod headers rewrite proxy proxy_http proxy_wstunnel \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /root/.symfony5

WORKDIR /var/www/html

# ✅ CORRECTION : Ajout setup-react.sh avec permissions
COPY Docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint
COPY Docker/setup-react.sh /usr/local/bin/setup-react

# ✅ CORRECTION : Permissions pour entrypoint ET setup-react
RUN chmod +x /usr/local/bin/entrypoint /usr/local/bin/setup-react \
    && chown -R www-data:www-data /var/www/html
EXPOSE 80 8081

CMD ["/usr/local/bin/entrypoint"]