<?php
/**
* @package   doc_fr
* @subpackage
* @author    Laurent Jouanneau
* @copyright 2012 Innophi
* @link      http://doc.jelix.org
* @license    GPL
*/

$appPath = __DIR__.'/';
require ($appPath.'../lib/jelix/init.php');

jApp::initPaths(
    $appPath,
    $appPath.'../www/',
    $appPath.'var/',
    getenv('DOCS_FR_JELIX_ORG_LOG_PATH'),
    $appPath.'var/config/',
    $appPath.'scripts/'
);
jApp::setTempBasePath(getenv('DOCS_FR_JELIX_ORG_TEMP_PATH'));
