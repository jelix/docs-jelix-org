#!/bin/bash

DOCTEMP="temp/doc_en temp/doc_fr doc_en/var/log/ doc_fr/var/log/"
chown www-data:$1 $DOCTEMP
chmod ug+ws $DOCTEMP