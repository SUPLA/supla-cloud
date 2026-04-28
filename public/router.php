<?php
// Use this as a router script to run backend from the project root directory in development, i.e.:
// php8.4 -S 127.0.0.1:8008 -t public public/router.php
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}
return require 'index.php';
