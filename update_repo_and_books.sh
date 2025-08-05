 #!/bin/bash

# This script can be called by a cron...

FORCE="1"
NOPDF=""
BOOKID=""
TARGET_BRANCH=""
MANUAL_LANG=""
ALL_LANG="Y"
TARGETPATH=""
ONLYCURRENTBRANCH=""
NO_GIT_PULL=""
GIT_REMOTE_NAME=origin
set -e

usage()
{
    echo "update_repo_and_book [options] <target-branch> [<lang>]"
    echo " "
    echo "  For each book registered inside this script:"
    echo "  - it pull changes from the git repository"
    echo "  - if there are some changes, it generates book informations for the wiki"
    echo "    and it generates the pdf of the book"
    echo ""
    echo "Parameters:"
    echo " - target-branch: the branch to use (jelix-1.8, jelix-1.9...)"
    echo " - lang: en or fr"
    echo ""
    echo "options:"
    echo "  -f|--force  :  force the generation of the books, even if there are no changes"
    echo "  --current-branch : only update for the current branch"
    echo "  -p|--no-pdf : generate only book informations, not pdfs"
    echo "  --output-pdf=a/path/ : indicates a path where to move books"
    echo "  --no-pull: do not git pull"
    echo "  --book=<bookid> the book to generate"
    echo "  --remote=<gitremote"
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
    --current-branch)
    ONLYCURRENTBRANCH="1"
    ;;
    --no-pull)
    NO_GIT_PULL="1"
    ;;
    -h|--help)
    usage
    ;;
    --output-pdf=*)
        TARGETPATH=${i:13}
    ;;
    --book=*)
        BOOKID=${i:7}
    ;;
    --remote=*)
        GIT_REMOTE_NAME=${i:9}
    ;;
    -*)
      echo "ERROR: Unknown option: $i"
      echo ""
      usage
      exit 1
    ;;
    *)
      echo "parameter " $i
      if [ "$TARGET_BRANCH" == "" ]
        then
            TARGET_BRANCH=$i
        else
            if [ "$MANUAL_LANG" == "" ]
            then
                MANUAL_LANG=$i
                ALL_LANG=""
            else
                echo "ERROR: Too many parameters: $i"
                echo ""
                exit 1
            fi
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
current_branch_name="$(git symbolic-ref HEAD 2>/dev/null)" ||
current_branch_name="(unnamed branch)"     # detached HEAD
current_branch_name=${current_branch_name##refs/heads/}

for index in 1 2 3 4 5 6 7 8 9 10
do
    br=${BRANCH[index]}
    book=${BOOK[index]}
    subdirtarget=${SUBDIR[index]}

    if [ "$ONLYCURRENTBRANCH" == "1" ]
    then
        if [ "$br" == "$current_branch_name" ]
        then
            if [ "$BOOKID" == "" -o "$BOOKID" == "$book" ]
            then
                updateBook
                BOOKGENERATED="1"
            fi
        fi
    else
        if [ "$TARGET_BRANCH" == "$br" -o  "$TARGET_BRANCH" == "" ]
        then
            updateBook
            BOOKGENERATED="1"
        fi
    fi
done
cd $ROOTPATH
}

updateBook()
{
    echo ""
    echo "--------------------------------- $book -------------------------------------"

    if [ "$ONLYCURRENTBRANCH" == "" ]
    then
        echo "checkout $br..."

        git checkout $br
        OLDREV=`cat .git/refs/heads/$br`
        if [ "$NO_GIT_PULL" == "" ]; then
          git pull $GIT_REMOTE_NAME $br
        fi
        NEWREV=`cat .git/refs/heads/$br`
    else
        OLDREV="0"
        NEWREV="1"
    fi

    if [ "$FORCE" != "" -o "$OLDREV" != "$NEWREV" ]; then
        echo "Generate Book $book"
        cd $ROOTPATH
        #rm -rf books/$book
        php $APP/console.php gitiwiki:book $book index
        #php $APP/console.php gitiwiki:docbook --lang $MANUAL_LOCALE $book index.gtw
        #echo "php $APP/console.php gitiwiki:book $book index"
        #echo "php $APP/console.php gitiwiki:docbook --lang $MANUAL_LOCALE $book index.gtw"

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
            cd pdf_utils/ \
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
BRANCH[9]="jelix-1.7"
BRANCH[10]="jelix-1.8"

SUBDIR[1]="1.9.x"
SUBDIR[2]="1.0.x"
SUBDIR[3]="1.1.x"
SUBDIR[4]="1.2.x"
SUBDIR[5]="1.3.x"
SUBDIR[6]="1.4.x"
SUBDIR[7]="1.5.x"
SUBDIR[8]="1.6.x"
SUBDIR[9]="1.7.x"
SUBDIR[10]="1.8.x"

if [ "$BOOKID" != "" ]; then
    # retrieve branch and manual lang
    if [ $BOOKID == manual* ]; then
        MANUAL_LANG=en
    else
        MANUAL_LANG=fr
    fi
    ALL_LANG=""
    TARGET_BRANCH="jelix-"${BOOKID:7}
    if [ $TARGET_BRANCH == "jelix-1.9" ]; then
        TARGET_BRANCH=master
    fi
fi

echo "BOOKID=$BOOKID"
echo "MANUAL_LANG=$MANUAL_LANG"
echo "ALL_LANG=$ALL_LANG"
echo "TARGET_BRANCH=$TARGET_BRANCH"

if [ "$MANUAL_LANG" == "en" -o "$ALL_LANG" == "Y" ];
then
    BOOK[1]="manual-1.9"
    BOOK[2]="manual-1.0"
    BOOK[3]="manual-1.1"
    BOOK[4]="manual-1.2"
    BOOK[5]="manual-1.3"
    BOOK[6]="manual-1.4"
    BOOK[7]="manual-1.5"
    BOOK[8]="manual-1.6"
    BOOK[9]="manual-1.7"
    BOOK[10]="manual-1.8"

    APP=doc_en
    REPO=$REPOS_PATH/en/jelix-manual-en/
    MANUAL_LOCALE="en_US"
    MANUAL_LANG="en"
    update
fi

if [ "$MANUAL_LANG" == "fr" -o "$ALL_LANG" == "Y" ];
then
    BOOK[1]="manuel-1.9"
    BOOK[2]="manuel-1.0"
    BOOK[3]="manuel-1.1"
    BOOK[4]="manuel-1.2"
    BOOK[5]="manuel-1.3"
    BOOK[6]="manuel-1.4"
    BOOK[7]="manuel-1.5"
    BOOK[8]="manuel-1.6"
    BOOK[9]="manuel-1.7"
    BOOK[10]="manuel-1.8"

    APP=doc_fr
    REPO=$REPOS_PATH/fr/jelix-manuel-fr/
    MANUAL_LOCALE="fr_FR"
    MANUAL_LANG="fr"
    update
fi

if [ "$BOOKGENERATED" == "0" ]
then
    echo "ERROR: unknown book"
    exit 1
fi
