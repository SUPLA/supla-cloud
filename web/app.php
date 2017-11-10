<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../app/autoload.php';

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'prod'));

$kernel = new AppKernel(APPLICATION_ENV, APPLICATION_ENV == 'dev');
if (APPLICATION_ENV === 'dev') {
    Symfony\Component\Debug\Debug::enable();
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
