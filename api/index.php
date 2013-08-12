<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim(
	array(
		'debug' => false,
		'log.enabled' => true,
		'log.level' => \Slim\Log::INFO,
		'log.writer' => \CodeMedic\Log::Init(LOG_INFO, 'myshare-api'),
		'view' => new \Slim\Views\Twig()
	)
);

$app->error(function (\Exception $e) use ($app) {
    echo __LINE__.$e->getMessage();
});

$view = $app->view();
$view->parserOptions = array(
	'debug' => true,
	'cache' => __DIR__.'/cache'
);

$app->get('/hello/:name', function ($name) {
	echo "Hello, $name";
	throw new Exception('XXX');
});


try
{
	$app->run();
}
catch (Exception $e)
{
    echo __LINE__.$e->getMessage();
	# $app->halt(500);
}
