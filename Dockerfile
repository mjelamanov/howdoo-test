FROM php:7.3-alpine

WORKDIR /home/backend/app/
COPY ./ /home/backend/app/

RUN apk add netcat-openbsd \
    && docker-php-ext-install pdo_mysql \
    && apk add composer \
    && composer global require hirak/prestissimo --no-suggest \
    && wget https://raw.githubusercontent.com/eficode/wait-for/master/wait-for -O /usr/local/bin/wait-for \
    && chmod +x /usr/local/bin/wait-for

EXPOSE 8000

CMD cp .env.example .env \
    && sed -i -E 's/(DB_HOST=)(.+?)/\1db/' .env \
    && sed -i -E 's/(DB_PASSWORD=)(.+?)/\1${DB_PASSWORD}/' .env \
    && sed -i -E 's/(DB_PORT=)([0-9]+)/\1${DB_PORT}/' .env \
    && composer install --no-suggest --no-progress \
    && php artisan key:generate \
    && wait-for db:${DB_PORT} -t 120 \
    && php artisan migrate:fresh --seed \
    && php artisan serve --host=0.0.0.0 --port=8000
