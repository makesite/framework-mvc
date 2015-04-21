<?php

define('DEBUG', 1);

/*
 * This file overrides the default `config.php`,  setting up
 * some developer-environment-specific settings.
 *
 * This file should not exists on production, if you see 
 * it there, delete it.
 */

$favourite_fruit = 'orange';

/* This declaration overrides the default `db.conf.php` configuration: */
/*
$db_conf = array(
	'type'  => 'mysql',
	'host'  => 'localhost',
	'login' => 'DEV_LOGIN',
	'pass'  => 'DEV_PASSWORD',
	'base'  => 'DEV_DATABASE',
	'prefix'=> '',

	'utf'    => true,
	'persist'=> true,
);
*/

?>