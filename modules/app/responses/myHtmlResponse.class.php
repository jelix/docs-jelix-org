<?php
/**
* @package   app
* @subpackage 
* @author    Laurent Jouanneau
* @copyright 2012 Innophi
* @link      http://doc.jelix.org
* @license    GPL
*/


require_once (JELIX_LIB_CORE_PATH.'response/jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {

    public $bodyTpl = 'app~main';

    function __construct() {
        parent::__construct();

        $this->addCssLink('/design/2011/design.css', array('media'=>'all', 'title'=>'Jelix'));
        $this->addCssLink('/design/2011/print.css', array('media'=>'print'));
    }

    protected function doAfterActions() {

        $this->title .= ($this->title !=''?' - ':'') . jLocale::get('app~site.title');
        $this->addMetaDescription(jLocale::get('app~site.description'));
        $this->addMetaKeywords(jLocale::get('app~site.keywords'));

        $this->addHeadContent(' 
   <link rel="icon" type="image/x-icon" href="/favicon.ico" />
   <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
   <meta name="DC.title" content="'.htmlspecialchars($this->title).'" />
   <meta name="DC.description" content="'.htmlspecialchars(jLocale::get('app~site.description')).'" />
   <meta name="robots" content="index,follow,all" />
');

       $this->body->assignIfNone('menu','');
       $this->body->assignIfNone('link_lang',false);
       $this->body->assignIfNone('MAIN','<p></p>');
       $this->body->assignIfNone('page_title','Jelix');
       $this->body->assignIfNone('MAINFOOTER','');
    }
}
