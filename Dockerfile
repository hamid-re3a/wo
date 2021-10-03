FROM webdevops/php-apache:7.4

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
RUN rm -rf /opt/docker/etc/httpd/vhost.conf
COPY apache-default-vhost.conf /opt/docker/etc/httpd/vhost.conf

#supervisor
COPY supervisor.d/ /opt/docker/etc/supervisor.d/


# src
COPY . /app

WORKDIR /app

# build
RUN cp .env.staging .env
RUN composer config --global --auth http-basic.ride-to-the-future.repo.repman.io token 67001fefcf70038c817987b7431f2d17498dc5c2409b4748e51cad87a69b8567
RUN composer install
RUN php artisan key:generate
RUN php artisan vendor:publish --all
RUN php artisan migrate:fresh
RUN php artisan db:seed
RUN php artisan scribe:generate
RUN php artisan optimize:clear
