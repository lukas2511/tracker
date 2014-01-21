<?php
	
	$time = microtime(true);
	
	define('ROOT', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
	define('LIBRARY', ROOT . 'Library' . DIRECTORY_SEPARATOR);
	define('APPLICATION', ROOT . 'Application' . DIRECTORY_SEPARATOR);
	
	date_default_timezone_set('Europe/Berlin');
	
	require LIBRARY . 'Application.php';
	
	requires(
		'AccessControl',
		'Controller',
		'Database/PostgreSQL',
		'Log',
		'Router',
		'View',
		'/Controller/Application'
	);
	
	Log::enableErrorHandler();
	
	require ROOT . 'Config/Config.php';
	require ROOT . 'Config/AccessControl.php';
	
	Router::addRoutes(ROOT . 'Config/Routes.php');
	
	try {
		$requested = Controller::runWithRequest();
		
		$time = microtime(true) - $time;
		Log::info(sprintf(
			'Processed %s::%s in %.4fs (%d reqs/sec) (View: %d%%, DB: %d%%)',
			$requested['controller'],
			$requested['action'],
			$time, 1 / $time,
			(Log::getTimer('View') / $time) * 100,
			(Log::getTimer('Database') / $time) * 100
		));
	} catch (Exception $e) {
		Log::handleException($e);
		
		$c = ($e->getCode() > 0)? $e->getCode() : 500;
		echo Controller::renderTemplate($c . '.tpl', [], null, new Response($c));
	}
	
?>