#!/bin/bash

# This script can be called by a cron...


FORCE=$1
cd $(dirname $0)
ROOTPATH=`pwd`
REPOS_PATH=$ROOTPATH/repositories

MANUAL_LANG=""

update()
{
cd $REPO
for index in 1 2 3 4 5 6
do
    br=${BRANCH[index]}
    echo "checkout $br..."
    
    git checkout $br
    OLDREV=`cat .git/refs/heads/$br`
    git pull origin $br
    NEWREV=`cat .git/refs/heads/$br`

    book=${BOOK[index]}
    if [ "$FORCE" != "" -o "$OLDREV" != "$NEWREV" ]; then
        echo "Generate Book $book"
        cd $ROOTPATH
        rm -rf books/$book
        php $APP/scripts/manage.php gitiwiki~wiki:generateBook $book index
        php $APP/scripts/manage.php gtwdocbook~docbook:index -lang $MANUAL_LANG $book index.gtw
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
BRANCH[6]="jelix-1.4"

BOOK[1]="manual-1.5"
BOOK[2]="manual-1.0"
BOOK[3]="manual-1.1"
BOOK[4]="manual-1.2"
BOOK[5]="manual-1.3"
BOOK[6]="manual-1.4"

APP=doc_en
REPO=$REPOS_PATH/en/jelix-manual-en/
MANUAL_LANG="en_EN"
update

BOOK[1]="manuel-1.5"
BOOK[2]="manuel-1.0"
BOOK[3]="manuel-1.1"
BOOK[4]="manuel-1.2"
BOOK[5]="manuel-1.3"
BOOK[6]="manuel-1.4"

MANUAL_LANG="fr_FR"

REPO=$REPOS_PATH/fr/jelix-manuel-fr/
APP=doc_fr
update

