FROM ubuntu:latest

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    git \
    php-mysql \
    php-pear \
    apache2 \
    libapache2-mod-php \
    php-cli \
    php-dev \
    libyaml-dev \
    php-mbstring \
    python3-yaml \
    wget \
    unzip \
    cron \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

RUN git clone https://github.com/roxlukas/lmeve

RUN sed -i 's|/var/www/html|/var/www/lmeve/wwwroot|g' /etc/apache2/sites-available/000-default.conf

RUN mkdir /Incoming && \
    cd /Incoming && \
    wget www.ash-online.net/lmevegfx/lmevegfx.tar.gz && \
    tar -xzvf lmevegfx.tar.gz -C /

COPY config.php /var/www/lmeve/config/config.php

RUN echo "*/15 * * * * www-data /usr/bin/php /var/www/lmeve/bin/poller.php" >> /etc/crontab

EXPOSE 80

CMD apache2ctl -D FOREGROUND