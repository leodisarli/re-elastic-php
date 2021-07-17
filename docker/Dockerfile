FROM kiwfydev/php73-clear-linux

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html

EXPOSE 9000

CMD ["php-fpm"]