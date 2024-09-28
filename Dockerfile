ARG PHP_VERSION
FROM php:${PHP_VERSION}cli

# install Composer
COPY ./docker/composer.sh /root/

RUN <<EOF
set -eux;
apt-get update;
apt-get install -y  \
    git  \
    zip;
rm -rf /var/lib/apt/lists/*;
cd /root/;
chmod 755 composer.sh;
/root/composer.sh;
mv /root/composer.phar /usr/local/bin/composer;
rm /root/composer.sh;
EOF

# install Xdebug
ARG XDEBUG_ENABLED=1

RUN <<EOF
if [ $XDEBUG_ENABLED -eq 1 ]; then
    pecl install xdebug;
    docker-php-ext-enable xdebug;
fi
EOF

WORKDIR /app/
