<?php

# Settings
$favourite_fruit = 'apple';

# Timezone
date_default_timezone_set("Europe/Moscow");

# Do not read bellow this line, eye bleeding might occur
if (!defined('APP_DIR')) {
	if (isset($_SERVER['APP_DIR']))
		define('APP_DIR', $_SERVER['APP_DIR']);
	elseif (isset($_SERVER["SCRIPT_FILENAME"]))
		define('APP_DIR', dirname($_SERVER["SCRIPT_FILENAME"]));
	else
		define('APP_DIR', __DIR__);
}

if (!defined('CORE_DIR')) {
	define('CORE_DIR', constant('APP_DIR') . '/' . 'core');
}

?>