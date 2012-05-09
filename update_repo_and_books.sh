#!/bin/bash

# This script can be called by a cron...

cd repositories/en/
git fetch --all

cd ../../repositories/fr
git fetch --all

cd ../..

rm -rf books/manual-1.0
php doc_en/scripts/manage.php gitiwiki~wiki:generateBook manual-1.0 index

rm -rf books/manual-1.1
php doc_en/scripts/manage.php gitiwiki~wiki:generateBook manual-1.1 index

rm -rf books/manual-1.2
php doc_en/scripts/manage.php gitiwiki~wiki:generateBook manual-1.2 index

rm -rf books/manual-1.3
php doc_en/scripts/manage.php gitiwiki~wiki:generateBook manual-1.3 index

rm -rf books/manual-1.4
php doc_en/scripts/manage.php gitiwiki~wiki:generateBook manual-1.4 index

rm -rf books/manuel-1.0
php doc_fr/scripts/manage.php gitiwiki~wiki:generateBook manuel-1.0 index

rm -rf books/manuel-1.1
php doc_fr/scripts/manage.php gitiwiki~wiki:generateBook manuel-1.1 index

rm -rf books/manuel-1.2
php doc_fr/scripts/manage.php gitiwiki~wiki:generateBook manuel-1.2 index

rm -rf books/manuel-1.3
php doc_fr/scripts/manage.php gitiwiki~wiki:generateBook manuel-1.3 index

rm -rf books/manuel-1.4
php doc_fr/scripts/manage.php gitiwiki~wiki:generateBook manuel-1.4 index

