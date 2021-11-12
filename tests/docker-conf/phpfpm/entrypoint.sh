#!/bin/sh

set -e
set -x

#APPDIR="/srv/docs.jelix.org/doc_fr"
#if [ ! -f $APPDIR/var/config/profiles.ini.php ]; then
#    echo "It seems databases and app are not configured yet. Please execute"
#    echo "   ./app-ctl reset"
#    echo "in order to setup databases and the app, after containers will be ready."
#fi

echo "launch exec $@"
exec "$@"
