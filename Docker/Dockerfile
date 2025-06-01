FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libicu-dev \
    vim \
    default-mysql-client \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Set COMPOSER_ALLOW_SUPERUSER
ENV COMPOSER_ALLOW_SUPERUSER=1

# Active le module rewrite d'Apache
RUN a2enmod rewrite
COPY Docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copie du script de configuration React
COPY Docker/setup-react.sh /usr/local/bin/setup-react
RUN chmod +x /usr/local/bin/setup-react

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Script de démarrage intelligent
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

CMD ["/usr/local/bin/entrypoint"]
