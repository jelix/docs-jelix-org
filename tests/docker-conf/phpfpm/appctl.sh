#!/bin/bash
ROOTDIR="/srv/docs.jelix.org"
TEMPDIR="$ROOTDIR/temp/"
APPDIR_FR="$ROOTDIR/doc_fr/"
APPTEMPDIR_FR="$ROOTDIR/temp/doc_fr"
APPDIR_EN="$ROOTDIR/doc_en"
APPTEMPDIR_EN="$ROOTDIR/temp/doc_en"

APP_USER=usertest
APP_GROUP=grouptest

COMMAND="$1"
shift

if [ "$COMMAND" == "" ]; then
    echo "Error: command is missing"
    exit 1;
fi

function resetJelixTemp() {
    echo "--- Reset temp files"
    local appdir="$1"
    local tempdir="$2"
    local apptempdir="$3"
    if [ ! -d $appdir/var/log ]; then
        mkdir $appdir/var/log
        chown $APP_USER:$APP_GROUP $appdir/var/log
    fi
    if [ ! -d $apptempdir/ ]; then
        mkdir -p $apptempdir/
        chown $APP_USER:$APP_GROUP $apptempdir
    else
        rm -rf $apptempdir/*
    fi
    touch $tempdir/.dummy
    chown $APP_USER:$APP_GROUP $tempdir
    chown $APP_USER:$APP_GROUP $tempdir/.dummy
    chmod ug+w $tempdir $apptempdir
}

function resetApp() {
    echo "--- Reset configuration files in $1"
    local appdir="$1"
    local apptempdir="$2"
    if [ -f $appdir/var/config/CLOSED ]; then
        rm -f $appdir/var/config/CLOSED
    fi

    for vardir in log mails uploads; do
      if [ ! -d $appdir/var/$vardir ]; then
          mkdir $appdir/var/$vardir
      else
          rm -rf $appdir/var/$vardir/*
      fi
      touch $appdir/var/$vardir/.dummy
    done

    if [ -f $appdir/var/config/profiles.docker.ini.php.dist ]; then
        cp $appdir/var/config/profiles.docker.ini.php.dist $appdir/var/config/profiles.ini.php
    fi
    if [ -f $appdir/var/config/localconfig.docker.ini.php.dist ]; then
        cp $appdir/var/config/localconfig.docker.ini.php.dist $appdir/var/config/localconfig.ini.php
    fi
    chown -R $APP_USER:$APP_GROUP $appdir/var/config/profiles.ini.php $appdir/var/config/localconfig.ini.php

    if [ -f $appdir/var/config/installer.ini.php ]; then
        rm -f $appdir/var/config/installer.ini.php
    fi
    if [ -f $appdir/var/config/liveconfig.ini.php ]; then
        rm -f $appdir/var/config/liveconfig.ini.php
    fi

    if [ -f $appdir/var/config/localframework.ini.php ]; then
        rm -f $appdir/var/config/localframework.ini.php
    fi

    setRights $appdir $apptempdir
    launchInstaller $appdir
}

function resetMysql() {
    echo "--- Reset mysql database for database $1"
    local base="$1"
    local login="$2"
    local pass="$3"

    mysql -h mysql -u $login -p$pass -Nse 'show tables' $base | while read table; do mysql -h mysql -u $login -p$pass -e "drop table $table" $base; done
}

function launchInstaller() {
    echo "--- Launch app installer in $1"
    local appdir="$1"
    su $APP_USER -c "php $appdir/install/installer.php --verbose"
}

function setRights() {
    echo "--- Set rights on directories and files in $1 and $2"
    local appdir="$1"
    local apptempdir="$2"
    USER="$3"
    GROUP="$4"

    if [ "$USER" = "" ]; then
        USER="$APP_USER"
    fi

    if [ "$GROUP" = "" ]; then
        GROUP="$APP_GROUP"
    fi

    DIRS="$appdir/var/config $appdir/var/db $appdir/var/log $appdir/var/mails $appdir/var/uploads $apptempdir $apptempdir/../"
    for VARDIR in $DIRS; do
      if [ ! -d $VARDIR ]; then
        mkdir -p $VARDIR
      fi
      chown -R $USER:$GROUP $VARDIR
      chmod -R ug+w $VARDIR
    done

}

function composerInstall() {
    echo "--- Install Composer packages"
    local appdir="$1"
    if [ -d $appdir/composer.json ]; then
      if [ -f $appdir/composer.lock ]; then
          rm -f $appdir/composer.lock
      fi
      composer install --prefer-dist --no-progress --no-ansi --no-interaction --working-dir=$appdir
      chown -R $APP_USER:$APP_GROUP $appdir/vendor $appdir/composer.lock
    fi
}

function composerUpdate() {
    echo "--- Update Composer packages"
    local appdir="$1"
    if [ -d $appdir/composer.json ]; then
      if [ -f $appdir/composer.lock ]; then
          rm -f $appdir/composer.lock
      fi
      composer update --prefer-dist --no-progress --no-ansi --no-interaction --working-dir=$appdir
      chown -R $APP_USER:$APP_GROUP $appdir/vendor $appdir/composer.lock
    fi
}

function launch() {
    echo "--- Launch setup in $1"
    local appdir="$1"
    local apptempdir="$2"
    if [ ! -f $appdir/var/config/profiles.ini.php ]; then
        cp $appdir/var/config/profiles.docker.ini.php.dist $appdir/var/config/profiles.ini.php
    fi
    if [ ! -f $appdir/var/config/localconfig.ini.php ]; then
        cp $appdir/var/config/localconfig.docker.ini.php.dist $appdir/var/config/localconfig.ini.php
    fi
    chown -R $APP_USER:$APP_GROUP $appdir/var/config/profiles.ini.php $APPDIR/var/config/localconfig.ini.php

    if [ ! -d $appdir/vendor ]; then
      composerInstall
    fi

    resetApp $appdir $apptempdir
    launchInstaller $appdir
    setRights $appdir $apptempdir
}


case $COMMAND in
    clean_tmp)
        resetJelixTemp $APPDIR_FR $TEMPDIR $APPTEMPDIR_FR
        resetJelixTemp $APPDIR_EN $TEMPDIR $APPTEMPDIR_EN
        ;;
    reset)
        resetJelixTemp $APPDIR_FR $TEMPDIR $APPTEMPDIR_FR
        resetJelixTemp $APPDIR_EN $TEMPDIR $APPTEMPDIR_EN
        composerInstall $APPDIR_FR
        composerInstall $APPDIR_EN
        resetApp $APPDIR_FR $APPTEMPDIR_FR
        resetApp $APPDIR_EN $APPTEMPDIR_EN
        ;;
    install)
        launchInstaller $APPDIR_FR
        launchInstaller $APPDIR_EN
        ;;
    rights)
        setRights $APPDIR_FR $APPTEMPDIR_FR
        setRights $APPDIR_EN $APPTEMPDIR_EN
        ;;
    composer_install)
        composerInstall $APPDIR_FR
        composerInstall $APPDIR_EN
        ;;
    composer_update)
        composerUpdate $APPDIR_FR
        composerUpdate $APPDIR_EN
        ;;
    unit-tests)
        UTCMD="cd $ROOTDIR/tests/units && vendor/bin/phpunit  $@"
        su $APP_USER -c "$UTCMD"
        ;;
    *)
        echo "wrong command"
        exit 2
        ;;
esac

