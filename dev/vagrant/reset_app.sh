#!/bin/bash

ROOTDIR="/jelixapp"
APPDIR="$ROOTDIR/"
VAGRANTDIR="$ROOTDIR/dev/vagrant"
#DISTFILESUFFIX="dist"
DISTFILESUFFIX="dev"
HOMEUSER=/home/vagrant

if [ -f $APPDIR/doc_fr/var/config/CLOSED ]; then
    rm -f $APPDIR/doc_fr/var/config/CLOSED
fi
if [ -f $APPDIR/doc_en/var/config/CLOSED ]; then
    rm -f $APPDIR/doc_en/var/config/CLOSED
fi

# create temp directory
if [ ! -d $APPDIR/temp/doc_en ]; then
    mkdir $APPDIR/temp/doc_en
else
    rm -rf $APPDIR/temp/doc_en/*
fi
touch $APPDIR/temp/doc_en/.dummy
if [ ! -d $APPDIR/temp/doc_fr ]; then
    mkdir $APPDIR/temp/doc_fr
else
    rm -rf $APPDIR/temp/doc_fr/*    
fi
touch $APPDIR/temp/doc_fr/.dummy

cp -a $APPDIR/doc_fr/var/config/profiles.ini.php.dev $APPDIR/doc_fr/var/config/profiles.ini.php
cp -a $APPDIR/doc_en/var/config/profiles.ini.php.dev $APPDIR/doc_en/var/config/profiles.ini.php
cp -a $APPDIR/doc_fr/var/config/localconfig.ini.php.dev $APPDIR/doc_fr/var/config/localconfig.ini.php
cp -a $APPDIR/doc_en/var/config/localconfig.ini.php.dev $APPDIR/doc_en/var/config/localconfig.ini.php

rm -f $APPDIR/doc_fr/var/config/installer.ini.php
rm -f $APPDIR/doc_en/var/config/installer.ini.php


if [ ! -d $APPDIR/books/pdf ]; then
    mkdir $APPDIR/books/pdf
fi

(cd $APPDIR && $APPDIR/install_repositories.sh)

php $APPDIR/doc_fr/install/installer.php
php $APPDIR/doc_en/install/installer.php




