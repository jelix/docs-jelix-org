/**
 * Adds the toggle switch to the TOC
 */
function addTocToggle() {
    if(!document.getElementById) return;
    var header = $('toc__header');
    if(!header) return;
    var toc = $('toc__inside');

    var obj          = document.createElement('span');
    obj.id           = 'toc__toggle';
    obj.style.cursor = 'pointer';
    if (toc && toc.style.display == 'none') {
        obj.innerHTML    = '<span>+</span>';
        obj.className    = 'toc_open';
    } else {
        obj.innerHTML    = '<span>&minus;</span>';
        obj.className    = 'toc_close';
    }

    prependChild(header,obj);
    obj.parentNode.onclick = toggleToc;
    obj.parentNode.style.cursor = 'pointer';
}

/**
 * This toggles the visibility of the Table of Contents
 */
function toggleToc() {
  var toc = $('toc__inside');
  var obj = $('toc__toggle');
  if(toc.style.display == 'none') {
    toc.style.display   = '';
    obj.innerHTML       = '<span>&minus;</span>';
    obj.className       = 'toc_close';
  } else {
    toc.style.display   = 'none';
    obj.innerHTML       = '<span>+</span>';
    obj.className       = 'toc_open';
  }
}


$(document).ready(function() {
  $('pre code').each(function(i, e) {hljs.highlightBlock(e, '    ')});
});