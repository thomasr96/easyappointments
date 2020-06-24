FROM php:7.2-apache

ENV PROJECT_DIR=/var/www/html \
    APP_URL=localhost

RUN docker-php-ext-install mysqli gettext
RUN pecl install -f xdebug \
&& echo "[xdebug] \n\
zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so) \n\
xdebug.default_enable=1 \n\
xdebug.remote_enable=1 \n\
xdebug.remote_port=9001 \n\
xdebug.remote_handler=dbgp \n\
xdebug.remote_host=172.17.0.1 \n\
xdebug.remote_connect_back=0 \n\
xdebug.idekey=VSCODE \n\
xdebug.remote_autostart=1 \n\
xdebug.remote_log=xdebug.log" > /usr/local/etc/php/conf.d/xdebug.ini;

COPY ./src $PROJECT_DIR
COPY docker-entrypoint.sh /entrypoint.sh

RUN sed -i 's/\r//' /entrypoint.sh

VOLUME $PROJECT_DIR/storage

ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]
CMD ["run"]
