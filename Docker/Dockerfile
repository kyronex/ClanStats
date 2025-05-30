FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libicu-dev \
    vim \
    default-mysql-client

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

# Create new Symfony project
# RUN composer create-project symfony/webapp-skeleton ClanStats
RUN composer create-project symfony/skeleton:"^6.4" ClanStats
RUN cd ClanStats && composer require \
    symfony/asset:6.4.* \
    symfony/form:6.4.* \
    symfony/http-client:6.4.* \
    symfony/monolog-bundle \
    symfony/security-bundle:6.4.* \
    symfony/serializer:6.4.* \
    symfony/twig-bundle:6.4.* \
    symfony/validator:6.4.* \
    phpdocumentor/reflection-docblock:^5.6 \
    phpstan/phpdoc-parser:^2.1

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
