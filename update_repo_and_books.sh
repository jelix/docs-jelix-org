#!/bin/bash

# This script can be called by a cron...

ARG1=$1
ARG2=$2
FORCE=""
NOPDF=""

if [ "$ARG1" == "--force" -o "$ARG2" == "--force" ]
then
    FORCE="1"
fi


if [ "$ARG1" == "--no-pdf" -o "$ARG2" == "--no-pdf" ]
then
    NOPDF="1"
fi


cd $(dirname $0)
ROOTPATH=`pwd`
REPOS_PATH=$ROOTPATH/repositories

MANUAL_LOCALE=""

update()
{
cd $REPO
for index in 1 2 3 4 5 6
do
    br=${BRANCH[index]}
    book=${BOOK[index]}
    echo ""
    echo "--------------------------------- $book -------------------------------------"

    echo "checkout $br..."
    
    git checkout $br
    OLDREV=`cat .git/refs/heads/$br`
    git pull origin $br
    NEWREV=`cat .git/refs/heads/$br`

    if [ "$FORCE" != "" -o "$OLDREV" != "$NEWREV" ]; then
        echo "Generate Book $book"
        cd $ROOTPATH
        rm -rf books/$book
        php $APP/scripts/manage.php gitiwiki~wiki:generateBook $book index
        if [ "$NOPDF" == "" ]
        then
            if [ ! -d $ROOTPATH/books/pdf/$MANUAL_LANG ]; then
                mkdir -p $ROOTPATH/books/pdf/$MANUAL_LANG
            fi
            php $APP/scripts/manage.php gtwdocbook~docbook:index -lang $MANUAL_LOCALE $book index.gtw \
            && cd pdf_utils/ \
            && dblatex -V -p jelixdoc_params.xsl --texstyle=jelixdoc_$MANUAL_LANG.sty $ROOTPATH/books/$book/books/index.gtw/docbook.xml 2>&1 \
            && mv $ROOTPATH/books/$book/books/index.gtw/docbook.pdf $ROOTPATH/books/pdf/$MANUAL_LANG/jelix-$book.pdf
        fi
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
MANUAL_LOCALE="en_US"
MANUAL_LANG="en"
update

BOOK[1]="manuel-1.5"
BOOK[2]="manuel-1.0"
BOOK[3]="manuel-1.1"
BOOK[4]="manuel-1.2"
BOOK[5]="manuel-1.3"
BOOK[6]="manuel-1.4"

MANUAL_LOCALE="fr_FR"
MANUAL_LANG="fr"

REPO=$REPOS_PATH/fr/jelix-manuel-fr/
APP=doc_fr
update

