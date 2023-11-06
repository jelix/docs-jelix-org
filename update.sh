#!/bin/bash
set -e
source /etc/profile.d/php_vars.sh
php doc_en/install/configurator.php --no-interaction
php doc_en/install/installer.php
php doc_fr/install/configurator.php  --no-interaction
php doc_fr/install/installer.php
