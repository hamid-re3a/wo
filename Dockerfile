FROM webdevops/php-nginx:7.4

# php modules
RUN apt update -y 
RUN apt-get update && apt-get install -y \
    curl \
    nano \
    net-tools \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \ 
    unzip\
    libcurl4-openssl-dev

RUN pecl install -D 'enable-sockets="yes" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="yes" enable-swoole-curl="yes" enable-cares="yes"' swoole
RUN echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini
RUN pecl install grpc
RUN echo 'extension=grpc.so' >> /usr/local/etc/php/conf.d/grpc.ini
# apache configuration
#RUN rm -rf /opt/docker/etc/httpd/vhost.conf
#COPY apache-default-vhost.conf /opt/docker/etc/httpd/vhost.conf

ENV WEB_DOCUMENT_ROOT /app/public
#supervisor
COPY supervisor.d/ /opt/docker/etc/supervisor.d/

# src
COPY . /app

WORKDIR /app

# ENV
ENV PHP_MEMORY_LIMIT 1024M

# build
#RUN cp .env.staging .env
RUN git checkout 777773caa1fabf623f67f7e0c65a92ab112f32cb
RUN chown -R application:application /app
RUN su application -c "composer config --global --auth http-basic.ride-to-the-future.repo.repman.io token 67001fefcf70038c817987b7431f2d17498dc5c2409b4748e51cad87a69b8567"
RUN su application -c "composer install"
RUN su application -c "composer dump-autoload"
RUN su application -c "php artisan cache:clear"
RUN su application -c "php artisan key:generate"
RUN su application -c "php artisan vendor:publish --all"
RUN su application -c "php artisan migrate:fresh"
RUN su application -c "php artisan db:seed"
RUN su application -c "php artisan scribe:generate"
RUN su application -c "php artisan optimize:clear"
RUN su application -c "php artisan queue:restart"



# permission
RUN chown -R application:application /app