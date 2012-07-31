

$(document).ready(function() {
  $('pre code[class]').each(function(i, e) {hljs.highlightBlock(e, '    ')});
});