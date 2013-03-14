<div id="top-box">
    <div class="top-container">
        <div id="accessibility">
            Quick links:        
            <a href="#article">Content</a> -
            <a href="#topmenubar">sections</a> -
            <a href="#submenubar">sub sections</a>
        </div>
        <div id="lang-box">
            <strong>EN</strong>
            <a href="/fr" hreflang="fr" title="version française">FR</a>
        </div>
    </div>
</div>

<div id="header">
    <div class="top-container">
        <h1 id="logo">
             <a href="/" title="Homepage"><img src="/design/logo/logo_jelix_moyen4.png" alt="Jelix" /></a>
        </h1>

        <ul id="topmenubar">
            <li><a href="http://jelix.org/en/">About</a></li>
            <li><a href="http://jelix.org/articles/en/download">Download</a></li>
            <li class="selected"><a href="http://jelix.org/articles/en/documentation" class="selected">Documentation</a></li>
            <li><a href="http://jelix.org/articles/en/community">Community</a></li>
        </ul>
    </div>
</div>
<div id="main-content">
    <div class="top-container">
        <div id="content-header">
            <ul id="submenubar">
             <li><a href="http://jelix.org/articles/en/tutorials">Tutorials</a></li>
                {foreach array('manual-1.5', 'manual-1.4', 'manual-1.3',  'manual-1.2', 'manual-1.1', 'manual-1.0') as $repo}
                    <li{if $repo===$currentRepoName} class="selected"{/if}><a href="{jurl 'gitiwiki~wiki:page', array('repository'=>$repo, 'page' => '/')}">{jlocale 'app~site.submenubar.title.' . str_replace('-', '_', $repo)}</a></li>
                {/foreach}
             <li><a href="http://jelix.org/reference/index.php.en">API reference</a></li>
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
            is supported by <a href="http://innophi.com">Innophi</a>.</p>
        <p>Jelix is released under <br/>the LGPL Licence</p>
        </div>

        <div class="footer-box">
            <ul>
                <li><a href="http://jelix.org/en/news/">News</a></li>
                <li><a href="http://jelix.org/articles/en/faq">FAQ</a></li>
                <li><a href="http://jelix.org/articles/en/hall-of-fame">Hall of fame</a></li>
                <li><a href="http://jelix.org/articles/en/credits">Credits</a></li>
                <li><a href="http://jelix.org/articles/en/support">Contacts</a></li>
                <li><a href="http://jelix.org/articles/en/goodies">Goodies</a></li>
            </ul>
        </div>

        <div class="footer-box">
            <ul>
                <li><a href="http://jelix.org/articles/en/download/nightly">download nightlies</a></li>
                <li><a href="http://jelix.org/articles/en/changelog">changelog</a></li>
                <li><a href="http://developer.jelix.org/wiki/en">issues tracker</a></li>
                <li><a href="http://developer.jelix.org/roadmap">roadmap</a></li>
                <li><a href="http://developer.jelix.org/wiki/en/contribute">How to contribute</a></li>
                <li><a href="https://github.com/jelix/jelix">Code source repository</a></li>
            </ul>
        </div>

        <p id="footer-legend">
            Copyright 2006-2013 Jelix team. <br/>
            Icons used on this page come from <a href="http://schollidesign.deviantart.com/art/Human-O2-Iconset-105344123">Human-O2</a>
            and <a href="http://www.oxygen-icons.org/">Oxygen</a> icons sets.<br/>
            Design by Laurentj. <br/>
            Powered by <img src="/design/btn_jelix_powered.png" alt="Jelix" /> <br/>
            and <a href="https://github.com/laurentj/gitiwiki">Gitiwiki</a>
        </p>
    </div>
</div>
