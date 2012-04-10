<?php
/**
* @package   app
* @author    Laurent Jouanneau
* @copyright 2012 Innophi
* @link      http://doc.jelix.org
* @license    GPL
*/

require_once (dirname(__FILE__).'/../application.init.php');

jApp::setEnv('install');

$installer = new jInstaller(new textInstallReporter());

$installer->installApplication();
