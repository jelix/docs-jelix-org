ARG php_version=7.4

FROM 3liz/liz-php-fpm:${php_version}

ARG php_version
ARG DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=${php_version}

RUN set -e; apt-get update; \
    apt-get -y -o Dpkg::Options::='--force-confdef' -o Dpkg::Options::='--force-confold' upgrade; \
    apt-get -y -o Dpkg::Options::='--force-confdef' -o Dpkg::Options::='--force-confold' install \
    php${PHP_VERSION}-xdebug  \
    ; \
    apt-get clean

COPY profile.start /etc/profile.d/start
COPY jelix_entrypoint.sh /bin/entrypoint.d/
COPY appctl.sh /bin/
RUN chmod 755 /bin/entrypoint.d/jelix_entrypoint.sh /bin/appctl.sh

WORKDIR /srv/docs.jelix.org/
