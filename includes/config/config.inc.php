<?php
	date_default_timezone_set('Europe/Madrid');
 	setlocale(LC_TIME, 'es_ES.UTF-8');
 	setlocale(LC_MONETARY, 'es_ES');
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(dirname(dirname(__FILE__))).DS);
	define('API_VERSION','v.0.0.1');
 	require_once 'config.db.inc.php';