<h3>Documentation de Jelix</h3>

<ul>
    {foreach $repolist as $name=>$title}
    <li><a href="{jurl 'gitiwiki~wiki:page', array('repository'=>$name)}">{$title|eschtml}</a></li>
    {/foreach}
</ul>