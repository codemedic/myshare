<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim(
    array(
        'debug' => true,
        'view' => new \Slim\Views\Twig()
    )
);

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => __DIR__.'/cache'
);

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$app->run();
