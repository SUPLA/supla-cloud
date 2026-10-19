<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// vendor/ may be a symlink to a shared install whose composer autoloader
// resolves App\ classes from that install's source tree. Register a prepended
// PSR-4 autoloader bound to THIS checkout so tests always exercise the code in
// this working tree. In a normal (non-symlinked) checkout this maps to the
// same directories the shared autoloader already uses, so it is a no-op.
$worktreeAutoloader = new \Composer\Autoload\ClassLoader();
$worktreeAutoloader->addPsr4('App\\Tests\\', dirname(__DIR__) . '/tests');
$worktreeAutoloader->addPsr4('App\\', dirname(__DIR__) . '/src');
$worktreeAutoloader->register(true);

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
