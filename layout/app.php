<?php

include 'config.php';
@include 'config.dev.php';

## CORE modules
include constant('CORE_DIR') . '/' . 'dispatch.php';  # Router/Dispatcher
#include constant('CORE_DIR') . '/' . 'dispatch2.php'; # Router/Dispatcher, Mark II
include constant('CORE_DIR') . '/' . 'db.php';        # Raw DB access
include constant('CORE_DIR') . '/' . 'db.orm.php';    # ORM
include constant('CORE_DIR') . '/' . 'qry5.php';      # Query builder
include constant('CORE_DIR') . '/' . 'form.php';      # Object<->Form converter
include constant('CORE_DIR') . '/' . 'domtempl.php';  # DOMtempl templating engine
include constant('CORE_DIR') . '/' . 'common.php';    # Misc common glue

## MODELS
#include constant('APP_DIR') . '/models/' . 'files.php';
include constant('APP_DIR') . '/models/' . 'settings.php';

## CONTROLLERS
include constant('APP_DIR') . '/controllers/' . 'ctr_attachments.php';
include constant('APP_DIR') . '/controllers/' . 'ctr_settings.php';
include constant('APP_DIR') . '/controllers/' . 'ctr_backup.php';
include constant('APP_DIR') . '/controllers/' . 'dbmodels.php';

## Third-Party libraries
#include constant('APP_DIR') . '/vendor/' . 'markdown/markdown.php';

## Run-time configuration
File::WORK_DIR($user_workdir);
# Do we need both ORM::loadModels and explicit 'includes' above?
# Usually, NO!
ORM::loadModels('models/');

##
## Dispatch here
##
try {

#	$ok = dispatch(...)

}
catch (Exception $e) {

}

##
## Project-specific helper functions
##
/* _debug_log is being called by several CORE modules, so it MUST be defined */
function _debug_log($str) {
	echo $str;
}

?>