<div id="top-box">
    <div class="top-container">
        <div id="accessibility">
            Raccourcis&nbsp;:
            <a href="#article">Contenu</a> -
            <a href="#topmenubar">rubriques</a> -
            <a href="#submenubar">sous rubriques</a>
        </div>
        <div id="lang-box">
            <a href="/en" hreflang="en" title="english version">EN</a>
            <strong>FR</strong>
        </div>
    </div>
</div>

<div id="header">
    <div class="top-container">
        <h1 id="logo">
             <a href="/" title="Page d'accueil du site"><img src="/design/logo/logo_jelix_moyen4.png" alt="Jelix" /></a>
        </h1>

        <ul id="topmenubar">
            <li><a href="http://jelix.org/fr/">À propos</a></li>
            <li><a href="http://jelix.org/articles/fr/telechargement">Téléchargement</a></li>
            <li class="selected"><a href="http://jelix.org/articles/fr/documentation" class="selected">Documentation</a></li>
            <li><a href="http://jelix.org/articles/fr/communaute">Communauté</a></li>
            <li><a href="http://jelix.org/articles/fr/support">Support</a></li>
        </ul>
    </div>
</div>
<div id="main-content">
    <div class="top-container">
        <div id="content-header">
            <ul id="submenubar">
                <li><a href="http://jelix.org/articles/fr/tutoriels">Tutoriels</a></li>
                {foreach array('manuel-1.5', 'manuel-1.4', 'manuel-1.3',  'manuel-1.2', 'manuel-1.1', 'manuel-1.0') as $repo}
                    <li{if $repo===$currentRepoName} class="selected"{/if}><a href="{jurl 'gitiwiki~wiki:page', array('repository'=>$repo, 'page' => '/')}">{jlocale 'app~site.submenubar.title.' . str_replace('-', '_', $repo)}</a></li>
                {/foreach}
                <li><a href="http://jelix.org/reference/index.html.fr">Référence API</a></li>
            </ul>
        </div>
        <div id="article">
        {$MAIN}
        </div>
        {if $MAINFOOTER}
        <div id="mainfooter">
        {$MAINFOOTER}
        </div>
        {/if}
    </div>
</div>
<div id="footer">
    <div class="top-container">
        <div class="footer-box">
        <p><img src="/design/logo/logo_jelix_moyen5.png" alt="Jelix" /><br/>
            est sponsorisé par <a href="http://innophi.com">Innophi</a>.</p>
        <p>Jelix est publié sous <br/>la licence LGPL</p>
        </div>
        
        <div class="footer-box">
            <ul>
                <li><a href="http://jelix.org/fr/news/">Actualités</a></li>
                <li><a href="http://jelix.org/articles/fr/faq">FAQ</a></li>
                <li><a href="http://jelix.org/articles/fr/hall-of-fame">Hall of fame</a></li>
                <li><a href="http://jelix.org/articles/fr/credits">Credits</a></li>
                <li><a href="http://jelix.org/articles/fr/support">Contacts</a></li>
                <li><a href="http://jelix.org/articles/fr/goodies">Goodies</a></li>
            </ul>
        </div>


        <div class="footer-box">
            <ul>
                <li><a href="http://jelix.org/articles/fr/telechargement/nightly">Téléchargement nightlies</a></li>
                <li><a href="http://jelix.org/articles/fr/changelog">Journal des changements</a></li>
                <li><a href="http://developer.jelix.org/wiki/fr">Suivi des bugs</a></li>
                <li><a href="http://developer.jelix.org/roadmap">roadmap</a></li>
                <li><a href="http://developer.jelix.org/wiki/fr/contribuer">Comment contribuer</a></li>
                <li><a href="https://github.com/jelix/jelix">Dépôt des sources</a></li>
            </ul>
        </div>
<!--
        <div class="footer-box">
            <ul>
                <li><a href="">jtpl standalone</a></li>
                <li><a href="">jbuildtools</a></li>
                <li><a href="">wikirenderer</a></li>
            </ul>
        </div>-->

        <p id="footer-legend">
            Copyright 2006-2013 Jelix team. <br/>
            Les icônes utilisées sur cette page viennent des paquets
            <a href="http://schollidesign.deviantart.com/art/Human-O2-Iconset-105344123">Human-O2</a>
            et <a href="http://www.oxygen-icons.org/">Oxygen</a>.<br/>
            Design par Laurentj. <br/>
            Site motorisé par <img src="/design/btn_jelix_powered.png" alt="Jelix" /><br />
            et <a href="https://github.com/laurentj/gitiwiki">Gitiwiki</a>
        </p>
    </div>
</div>
