#!/bin/bash

# This script can be called by a cron...


FORCE=$1


update()
{
cd $REPO
for index in 1 2 3 4 5
do
    br=${BRANCH[index]}
    echo "checkout $br..."
    
    git checkout $br
    OLDREV=`cat .git/refs/heads/$br`
    git pull origin $br
    NEWREV=`cat .git/refs/heads/$br`

    book=${BOOK[index]}
    if [ "$FORCE" != "" -o "$OLDREV" != "$NEWREV" ]; then
        cd $ROOTPATH
        rm -rf books/$book
        php $APP/scripts/manage.php gitiwiki~wiki:generateBook $book index
        cd $REPO
    fi
done
cd $ROOTPATH
}

BRANCH[1]="master"
BRANCH[2]="jelix-1.0"
BRANCH[3]="jelix-1.1"
BRANCH[4]="jelix-1.2"
BRANCH[5]="jelix-1.3"

BOOK[1]="manual-1.4"
BOOK[2]="manual-1.0"
BOOK[3]="manual-1.1"
BOOK[4]="manual-1.2"
BOOK[5]="manual-1.3"

APP=doc_en
REPO=repositories/en/jelix-manual-en/
ROOTPATH=../../..
update

BOOK[1]="manuel-1.4"
BOOK[2]="manuel-1.0"
BOOK[3]="manuel-1.1"
BOOK[4]="manuel-1.2"
BOOK[5]="manuel-1.3"

REPO=repositories/fr/jelix-manuel-fr/
APP=doc_fr
update

