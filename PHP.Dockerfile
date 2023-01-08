FROM php:fpm

ARG APP_ENV=dev
WORKDIR /usr/local/apache2/htdocs

RUN if [ ${APP_ENV} = "dev" ]; then \ 
    apt-get update && apt-get -y --no-install-recommends install git \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin/ --filename=composer \
    && rm -rf /var/lib/apt/lists/*; \
fi

RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug
