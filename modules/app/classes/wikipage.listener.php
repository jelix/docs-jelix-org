<?php

class wikipageListener extends jEventListener{
    
   function onGtwBeforeWikipage ($event) {
       
       $repo = new gtwRepo($event->getParam('repository'));
       $repoConfig = $repo->config();

       $defaultRepo = new gtwRepo('default');
       $defaultRepoConfig = $defaultRepo->config();
       $defaultUrl = null;
       if( $repoConfig['linkToDefault'] &&
           $defaultRepoConfig['branch'] != $repoConfig['branch'] &&
           $defaultRepo->findFile($event->getParam('page')) !== null ) {
               $defaultUrl = jUrl::get( 'wiki:page', array('repository'=>$defaultRepo->getName(), 'page'=>$event->getParam('page') ) );

               $defaultRepoTitle = $defaultRepoConfig['title'];

               $event->add(
                   '<div id="book-page-default-url">' .
                   jLocale::get( 'app~site.switch.defaultRepo.html', array($defaultUrl, $defaultRepoTitle) ) .
                   '</div>'
               );
           }
   }
}
