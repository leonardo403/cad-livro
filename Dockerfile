# Imagem da aplicação - PHP 8.4 + Apache
FROM php:8.4-apache

# Extensão para conectar ao MySQL, módulo de reescrita de URLs
# e ferramentas exigidas pelo Composer (unzip para baixar os pacotes)
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia o código da aplicação e instala as dependências
COPY . .

RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html/var

# Vhost apontando para o diretório public/ do Symfony
COPY docker/apache/symfony.conf /etc/apache2/sites-available/symfony.conf
RUN a2dissite 000-default.conf \
    && a2ensite symfony.conf

EXPOSE 80