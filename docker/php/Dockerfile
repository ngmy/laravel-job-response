FROM php:8.1

ARG USERNAME=app
ARG GROUPNAME=app
ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID $GROUPNAME \
    && useradd -m -u $UID -g $GID $USERNAME

# Add to download Composer packages from dist.
RUN apt-get update && apt-get install -y unzip

# Add to connect to MySQL.
RUN docker-php-ext-install pdo_mysql

# Add to connect to Redis.
RUN apt-get update && apt-get install -y git \
    && git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis \
    && docker-php-ext-install redis

COPY --from=composer /usr/bin/composer /usr/bin/composer
