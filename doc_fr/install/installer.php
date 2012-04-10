<?php
/**
* @package   doc_fr
* @author    Laurent Jouanneau
* @copyright 2012 Innophi
* @link      http://innophi.com
* @license    All rights reserved
*/

require_once (dirname(__FILE__).'/../application.init.php');

jApp::setEnv('install');

$installer = new jInstaller(new textInstallReporter());

$installer->installApplication();
