FROM ubuntu:20.04

LABEL maintainer="Pedro Vasconcelos"

ARG APP_DOMAIN=localhost
ARG XDEBUG=true
ARG WWWGROUP=50
ARG WWWUSER=50

# Export to entrypoint
ENV APP_DOMAIN=$APP_DOMAIN
ENV XDEBUG=$XDEBUG
ENV WWWGROUP=$WWWGROUP
ENV WWWUSER=$WWWUSER

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C \
    && echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu focal main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y nginx \
    && apt-get install -y php7.4-fpm php7.4-cli php7.4-dev \
       php7.4-pgsql php7.4-sqlite3 php7.4-gd \
       php7.4-curl php7.4-memcached \
       php7.4-imap php7.4-mysql php7.4-mbstring \
       php7.4-xml php7.4-zip php7.4-bcmath php7.4-soap \
       php7.4-intl php7.4-readline php7.4-pcov \
       php7.4-msgpack php7.4-igbinary php7.4-ldap \
       php7.4-redis \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -sL https://deb.nodesource.com/setup_15.x | bash - \
    && apt-get install -y nodejs \
    && apt-get install -y vim \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y mysql-client \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN if [ "$XDEBUG" = "true" ] ; then \
    pecl install xdebug \
    && echo "[XDebug]" > /etc/php/7.4/mods-available/xdebug.ini \
    && echo "zend_extension=$(find /usr/lib/php/20190902/ -name xdebug.so)" >> /etc/php/7.4/mods-available/xdebug.ini \
    && echo "xdebug.mode = debug" >> /etc/php/7.4/mods-available/xdebug.ini \
    && echo "xdebug.client_host = host.docker.internal" >> /etc/php/7.4/mods-available/xdebug.ini \
    && phpenmod xdebug ; \
fi;

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php7.4

#RUN mkdir -p /etc/nginx/certs/self-signed
#COPY certs/com.test.crt /etc/nginx/certs/self-signed/
#COPY certs/com.test.key /etc/nginx/certs/self-signed/
#RUN openssl dhparam -out /etc/nginx/certs/dhparam.pem 2048
COPY nginx.conf /etc/nginx/sites-available/default

# NGINX tweaks
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf
#RUN sed -i -r "s/HOST/${APP_DOMAIN}/g" /etc/nginx/sites-available/default
RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stderr /var/log/nginx/error.log

# PHP-FPM Tweaks
RUN perl -pi -e 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.4/fpm/php.ini
RUN perl -pi -e 's/allow_url_fopen = Off/allow_url_fopen = On/g' /etc/php/7.4/fpm/php.ini
RUN perl -pi -e 's/expose_php = On/expose_php = Off/g' /etc/php/7.4/fpm/php.ini
RUN perl -pi -e 's/;clear_env = no/clear_env = no/g' /etc/php/7.4/fpm/pool.d/www.conf

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/7.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80
#EXPOSE 443

ENTRYPOINT ["start-container"]
