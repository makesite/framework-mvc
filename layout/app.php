<?php
##
## APPname
##
ini_set('display_errors', 1);
error_reporting(E_ALL | E_NOTICE | E_STRICT);

# Development environment?
if (isset($_SERVER['DEBUG_HOST']))
	define('DEBUG', TRUE);

# Config
include 'config.php';
@include 'config.dev.php';

# System
include constant('CORE_DIR') . '/' . 'dispatch.php';  # Router/Dispatcher
#include constant('CORE_DIR') . '/' . 'dispatch2.php'; # Router/Dispatcher, Mark II
include constant('CORE_DIR') . '/' . 'db.php';        # Raw DB access
include constant('CORE_DIR') . '/' . 'db.orm.php';    # ORM
include constant('CORE_DIR') . '/' . 'qry5.php';      # Query builder
include constant('CORE_DIR') . '/' . 'form.php';      # Object<->Form converter
include constant('CORE_DIR') . '/' . 'domtempl.php';  # DOMtempl templating engine
include constant('CORE_DIR') . '/' . 'common.php';    # Misc common glue

# Models
include constant('APP_DIR') . '/models/' . 'files.php';
include constant('APP_DIR') . '/models/' . 'settings.php';

# Controllers
include constant('APP_DIR') . '/controllers/' . 'ctr_attachments.php';
include constant('APP_DIR') . '/controllers/' . 'ctr_settings.php';
include constant('APP_DIR') . '/controllers/' . 'ctr_backup.php';
include constant('APP_DIR') . '/controllers/' . 'dbmodels.php';

# Third-Party libraries
#include constant('APP_DIR') . '/vendor/' . 'markdown/markdown.php';

# Run-time configuration
File::WORK_DIR($user_workdir);
ORM::loadModels('models/'); // Do we need both ORM::loadModels and explicit 'includes' above? Usually, NO!
//DigestDummy::$realm = $app_name . " Realm"; # Auth realm
//WAuth::enable('DigestDummy'); # Auth provider

##
## Dispatch here
##
try {
	if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
	{
	    $ok = dispatch(_REST(), 'del_');
	    if (!$ok) _view_500();
	}
	else if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$ok = dispatch(_REST(), 'do_');
		if (!$ok) _view_500();
	}
	else
	{
		$found = dispatch(_GET(), 'view_');
	}
} catch (Exception $e) {
	_view_500($e);
};

##
## Project-specific helper functions
##
/* _debug_log is being called by several CORE modules, so it MUST be defined */
function _debug_log($str) {
	echo $str;
}

/* See if we're authorized */
function http_auth() {
	if (!WAuth::forcelogin()) {
		return false;
	}
	return true;
}

/* Common responses */
function ajax_failure($data) {
	if (is_string($data)) {
		$data = array(
			'error_message' => $data
		);
	}
	if (defined('DEBUG')) {
		global $_debug_logs;
		$data['debug'] = $_debug_logs;
	}
	//header("Connection: close", '500 Internal Server Error');
	//header("Content-type: application/json");
	echo json_encode(array(
		'result' => 'failure',
		'data' => $data
	));
	return true;
}

function ajax_success($data) {
	//header("Content-type: application/json");
	echo json_encode(array(
		'result' => 'success',
		'data' => $data
	));
	return true;
}


function _view_403() {
	header("Connection: close", '403 Forbidden');
	if (_FORMAT('json') || isset($_REQUEST['json'])) {
		return ajax_failure( 'Forbidden' );
	}
	$l = html_page();
	$l->assign('article', array(
		'class' => '',
		'title' => _L('Error 403'),
		'body' => _L('Access to <em>%s</em> is denied', $_SERVER['NODE_URI']),
	));
	$l->out();
	return true;
}

function _view_404() {
	header("Connection: close", '404 Not Found');
	if (_FORMAT('json') || isset($_REQUEST['json'])) {
		return ajax_failure( 'Not Found' );
	}
	$l = html_page();
	$l->assign('article', array(
		'class' => '',	
		'title' => _L('Error 404'),
		'body' => _L('Requested resource <em>%s</em> was not found.', $_SERVER['NODE_URI']),
	));
	if (defined('DEBUG')) {
		global $_debug_logs;
		$l->vars['article']['body'] .= er($_debug_logs, 1);
	} 
	$l->out();
	return true;
}

function _view_500($ex = null) {
	header("Connection: close", '500 Internal Server Error');
	if (_FORMAT('json') || isset($_REQUEST['json'])) {
		return ajax_failure( ($ex ? $ex->getMessage() : 'Internal Server Error' ));
	}
	$l = html_page();
	$l->assign('article', array(
		'class' => '',
		'title' => _L('Error 500'),
		'body' => _L('Internal Server Error.'),
	));
	if (defined('DEBUG')) {
		global $_debug_logs;
		$l->vars['article']['body'] .= er($_debug_logs,1);
	}
	if ($ex) $l->vars['article']['body'] .= "<pre>".draw_exception($ex, defined('DEBUG'))."</pre>";
	$l->out();
	return true;
}

function _raw_404() {
	header("Connection: close", _L('404 Not found'));
	exit;
}


?>