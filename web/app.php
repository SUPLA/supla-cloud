<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../app/autoload.php';

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'prod'));

ini_set('log_errors', 1);
ini_set('error_log', AppKernel::VAR_PATH . '/logs/php_error.log');

$kernel = new AppKernel(APPLICATION_ENV, APPLICATION_ENV === 'dev');
if (APPLICATION_ENV === 'dev') {
    Symfony\Component\ErrorHandler\Debug::enable();
    umask(0000);
}

$trustedProxies = array_filter(array_map('trim', explode(',', getenv('TRUSTED_PROXIES', true) ?: '172.18.0.0/27, 192.168.0.0/16')));
if ($trustedProxies) {
    Request::setTrustedProxies(array_values($trustedProxies), Request::HEADER_FORWARDED | Request::HEADER_X_FORWARDED_FOR);
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
