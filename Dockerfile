  # Dockerfile written by 10up <sales@10up.com>
#
# Work derived from official PHP Docker Library:
# Copyright (c) 2014-2015 Docker, Inc.

FROM php:7.4-apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

RUN apt-get update
RUN apt-get install -y build-essential
RUN apt-get install -y curl
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get install -y gcc g++ make
RUN apt-get install -y libnode-dev
RUN apt-get install -y nodejs
RUN apt-get install -y git
RUN apt-get install -y zlib1g zlib1g-dev libpng-dev libjpeg-dev
RUN apt-get install -y nano
RUN apt-get install -y cron

RUN mkdir -p /var/app
COPY ./prisma /var/app

ENV TZ 'America/Araguaina'
RUN echo $TZ > /etc/timezone && \
apt-get install -y tzdata && \
rm /etc/localtime && \
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
dpkg-reconfigure -f noninteractive tzdata && \
apt-get clean

#RUN npm install --prefix /var/www/html/nodejs
#RUN npm install pm2 -g

RUN rm -rf /var/www/html && mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && chown -R www-data:www-data /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html
COPY ./app /var/www/html
#COPY ./app/nodejs/node.conf /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default
# Apache + PHP requires preforking Apache for best results
RUN a2dismod mpm_event && a2enmod mpm_prefork

COPY cron /etc/cron.d/cron
RUN chmod 0644 /etc/cron.d/cron
RUN crontab /etc/cron.d/cron
# Enable apache2 rewrite engine
RUN a2enmod rewrite && a2enmod proxy_http && a2enmod proxy_wstunnel

RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update && \
     apt-get install -y \
         libzip-dev \
         && docker-php-ext-install zip


#RUN cd /var/www/html/nodejs && npm install 

# Install Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# RUN composer require 'byjg/migration-cli=4.0.*'
# RUN ./vendor/bin/migrate install

# Change www-data user to match the host system UID and GID and chown www directory
RUN usermod --non-unique --uid 1000 www-data \
  && groupmod --non-unique --gid 1000 www-data \
  && chown -R www-data:www-data /var/www

WORKDIR /var/app
#RUN npm install -g @prisma/cli

# Install new CLI
RUN npm install -g prisma 

# Invoke via npx
RUN npx prisma --help
RUN npm install

# CMD [ "npx", "prisma", "migrate", "deploy", "--preview-feature", "&&", "tail", "-f", "/dev/null"]
#ENTRYPOINT ["/bin/bash", "-c", "npx prisma migrate deploy --preview-feature && pm2 start /var/www/html/nodejs/srv-lifebox.js --name node-socket && apachectl -D FOREGROUND"]
ENTRYPOINT ["/bin/bash", "-c", "env > /etc/environment && service cron start && npx prisma migrate deploy --preview-feature && apachectl -D FOREGROUND"]



