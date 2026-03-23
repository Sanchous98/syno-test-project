FROM php:8.4-fpm-alpine AS dev

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    install-php-extensions @composer redis pdo_mysql

WORKDIR /var/www/html

FROM dev AS prod

RUN addgroup -g 2000 www && adduser -u 2000 -G www -s /bin/sh -D www

COPY . /var/www/html

RUN chown -R www:www /var/www/html

USER www:www

RUN composer i -o -a && php bin/console cache:warmup

ENTRYPOINT ["/var/www/html/docker/php/entrypoint.sh"]
CMD ["php-fpm"]