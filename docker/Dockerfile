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

#RUN mkdir /Incoming && \
#    cd /Incoming && \
#    wget www.ash-online.net/lmevegfx/lmevegfx.tar.gz && \
#    tar -xzvf lmevegfx.tar.gz -C /

# Download config-dist.php, rename it, and modify it
RUN wget https://raw.githubusercontent.com/roxlukas/lmeve/master/config/config-dist.php -O /var/www/lmeve/config/config.php && \
    sed -i "s/\$LM_dbhost='localhost'/\$LM_dbhost=getenv('DB_HOST')/" /var/www/lmeve/config/config.php && \
    sed -i "s/\$LM_dbname='lmeve'/\$LM_dbname=getenv('DB_NAME_LMEVE')/" /var/www/lmeve/config/config.php && \
    sed -i "s/\$LM_dbuser='lmeve'/\$LM_dbuser=getenv('DB_USER')/" /var/www/lmeve/config/config.php && \
    sed -i "s/\$LM_dbpass='password'/\$LM_dbpass=getenv('DB_PASSWORD')/" /var/www/lmeve/config/config.php && \
    sed -i "s/\$LM_EVEDB='eve_tia100_dbo'/\$LM_EVEDB=getenv('DB_NAME_STATIC')/" /var/www/lmeve/config/config.php

RUN apt-get install libyaml-0-2 libyaml-dev && \
    printf "\n" | pecl install yaml && \
    echo "extension=yaml.so" >> /etc/php/8.3/apache2/php.ini && \
    echo "extension=yaml.so" >> /etc/php/8.3/cli/php.ini

RUN echo "*/15 * * * * www-data /usr/bin/php /var/www/lmeve/bin/poller.php" >> /etc/crontab

RUN chown www-data:www-data /var/www/lmeve/var

EXPOSE 80

CMD apache2ctl -D FOREGROUND
