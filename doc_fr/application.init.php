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
require (realpath($appPath.'../lib/jelix/').'/'.'init.php');

jApp::initPaths(
    $appPath,
    $appPath.'../www/',
    $appPath.'var/',
    $appPath.'var/log/',
    $appPath.'var/config/',
    $appPath.'scripts/'
);
jApp::setTempBasePath(realpath($appPath.'../temp/doc_fr/').'/');
