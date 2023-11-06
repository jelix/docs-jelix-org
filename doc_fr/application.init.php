<?php
/**
* @package   doc_fr
* @subpackage
* @author    Laurent Jouanneau
* @copyright 2012-2023 Laurent Jouanneau
* @link      http://doc.jelix.org
* @license    GPL
*/

$appPath = __DIR__.'/';
require (__DIR__.'/../lib/vendor/autoload.php');

jApp::initPaths(
    $appPath,
    $appPath.'../www/',
    $appPath.'var/',
    getenv('DOCS_FR_JELIX_ORG_LOG_PATH'),
    $appPath.'var/config/',
    $appPath.'scripts/'
);
jApp::setTempBasePath(getenv('DOCS_FR_JELIX_ORG_TEMP_PATH'));

require (__DIR__.'/../lib/vendor/jelix_app_path.php');

