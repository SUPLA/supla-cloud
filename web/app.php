<?php

use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'prod'));
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel(APPLICATION_ENV, APPLICATION_ENV == 'dev');
if (APPLICATION_ENV == 'prod') {
    $kernel->loadClassCache();
}

//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
