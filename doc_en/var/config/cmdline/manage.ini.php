;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule = "gitiwiki"
startAction = "wiki:index"
[modules]
app.access=2

gitiwiki.access=2
gtwdocbook.access=2

[responses]


[fileLogger]
default=messages-manage.log
error=errors-manage.log
warning=errors-manage.log
notice=errors-manage.log
deprecated=errors-manage.log
strict=errors-manage.log
debug=debug-manage.log
