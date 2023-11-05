<?php
/**
* @package   app
* @subpackage app
* @author    Laurent Jouanneau
* @copyright 2012 Innophi
* @link      http://doc.jelix.org
* @license    GPL
*/

class defaultCtrl extends jController {
    /**
    *
    */
    function index() {
        $rep = $this->getResponse('redirectUrl');
        $rep->url = '/en';
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return $rep;

        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach($languages as $bl){
            if(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                $lang = strtolower($match[1]);
                if ($lang == 'en' || $lang == 'fr') {
                    $rep->url = '/'.$lang;
                    break;
                }
            }
        }
    
        return $rep;
    }
}
