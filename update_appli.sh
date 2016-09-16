#!/bin/bash

git pull origin master

rm -rf temp/doc_en/*
rm -rf temp/doc_fr/*

php doc_fr/install/installer.php
php doc_en/install/installer.php
