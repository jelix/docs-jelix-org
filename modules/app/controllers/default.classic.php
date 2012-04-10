<?php
/**
* @package   app
* @subpackage app
* @author    Laurent Jouanneau
* @copyright 2012 Innophi
* @link      http://innophi.com
* @license    All rights reserved
*/

class defaultCtrl extends jController {
    /**
    *
    */
    function index() {
        $rep = $this->getResponse('html');

        // this is a call for the 'welcome' zone after creating a new application
        // remove this line !
        $rep->body->assignZone('MAIN', 'jelix~check_install');

        return $rep;
    }
}
