FROM php:7.2-apache

ENV PROJECT_DIR=/var/www/html \
    APP_URL=localhost

RUN docker-php-ext-install mysqli gettext
RUN pecl install -f xdebug \
&& echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini;

COPY ./src $PROJECT_DIR
COPY docker-entrypoint.sh /entrypoint.sh

RUN sed -i 's/\r//' /entrypoint.sh

VOLUME $PROJECT_DIR/storage

ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]
CMD ["run"]
