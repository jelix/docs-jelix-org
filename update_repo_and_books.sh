#!/bin/bash

# This script can be called by a cron...

FORCE=""
NOPDF=""
BOOKID=""
TARGETPATH=""

usage()
{
    echo "update_repo_and_book [options] [bookid]"
    echo "  For each book registered inside this script:"
    echo "  - it pull changes from the git repository"
    echo "  - if there are some changes, it generates book informations for the wiki"
    echo "    and it generates the pdf of the book"
    echo "  If a book id is given, only do these operations on the corresponding book"
    echo ""
    echo "options:"
    echo "  -f|--force  :  force the generation of the books, even if there are no changes"
    echo "  -p|--no-pdf : generate only book informations, not pdfs"
    echo "  --output-pdf=a/path/ : indicates a path where to move books"
    echo ""
}


for i in $*
do
case $i in
    -f|--force)
    FORCE="1"
    ;;
    -p|--no-pdf)
    NOPDF="1"
    ;;
    -h|--help)
    usage
    ;;
    --output-pdf=*)
        TARGETPATH=${i:13}
    ;;
    -*)
      echo "ERROR: Unknown option: $i"
      echo ""
      usage
      exit 1
    ;;
    *)
    if [ "$BOOKID" == "" ]
    then
        BOOKID=$i
    else
        echo "ERROR: Too many parameters: $i"
        echo ""
        usage
        exit 1
    fi
    ;;
esac
done

cd $(dirname $0)
ROOTPATH=`pwd`
REPOS_PATH=$ROOTPATH/repositories

MANUAL_LOCALE=""
BOOKGENERATED="0"


update()
{
cd $REPO
for index in 1 2 3 4 5 6 7 8
do
    br=${BRANCH[index]}
    book=${BOOK[index]}
    subdirtarget=${SUBDIR[index]}
    if [ "$BOOKID" == "" -o "$BOOKID" == "$book" ]
    then
        updateBook
        BOOKGENERATED="1"
    fi
done
cd $ROOTPATH
}

updateBook()
{
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
            if [ "$TARGETPATH" == "" ]; then
                MOVETO="$ROOTPATH/books/pdf/$MANUAL_LANG"
            else
                MOVETO="$TARGETPATH/$subdirtarget"
            fi

            if [ ! -d $MOVETO ]; then
                mkdir -p $MOVETO
            fi
            php $APP/scripts/manage.php gtwdocbook~docbook:index -lang $MANUAL_LOCALE $book index.gtw \
            && cd pdf_utils/ \
            && dblatex -V -p jelixdoc_params.xsl --texstyle=jelixdoc_$MANUAL_LANG.sty $ROOTPATH/books/$book/books/index.gtw/docbook.xml 2>&1 \
            && mv $ROOTPATH/books/$book/books/index.gtw/docbook.pdf $MOVETO/jelix-$book.pdf
        fi
        cd $REPO
    fi
}



BRANCH[1]="master"
BRANCH[2]="jelix-1.0"
BRANCH[3]="jelix-1.1"
BRANCH[4]="jelix-1.2"
BRANCH[5]="jelix-1.3"
BRANCH[6]="jelix-1.4"
BRANCH[7]="jelix-1.5"
BRANCH[8]="jelix-1.6"

SUBDIR[1]="1.7.x"
SUBDIR[2]="1.0.x"
SUBDIR[3]="1.1.x"
SUBDIR[4]="1.2.x"
SUBDIR[5]="1.3.x"
SUBDIR[6]="1.4.x"
SUBDIR[7]="1.5.x"
SUBDIR[8]="1.6.x"

BOOK[1]="manual-1.7"
BOOK[2]="manual-1.0"
BOOK[3]="manual-1.1"
BOOK[4]="manual-1.2"
BOOK[5]="manual-1.3"
BOOK[6]="manual-1.4"
BOOK[7]="manual-1.5"
BOOK[8]="manual-1.6"

APP=doc_en
REPO=$REPOS_PATH/en/jelix-manual-en/
MANUAL_LOCALE="en_US"
MANUAL_LANG="en"
update

BOOK[1]="manuel-1.7"
BOOK[2]="manuel-1.0"
BOOK[3]="manuel-1.1"
BOOK[4]="manuel-1.2"
BOOK[5]="manuel-1.3"
BOOK[6]="manuel-1.4"
BOOK[7]="manuel-1.5"
BOOK[8]="manuel-1.6"

MANUAL_LOCALE="fr_FR"
MANUAL_LANG="fr"

REPO=$REPOS_PATH/fr/jelix-manuel-fr/
APP=doc_fr
update

if [ "$BOOKID" != "" -a "$BOOKGENERATED" == "0" ]
then
    echo "ERROR: unknown book"
    exit 1
fi
