<?php
// Use this as a router script to run backend from the project root directory in development, i.e.:
// php7.3 -S 127.0.0.1:8008 -t web web/router.php
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}
require 'index.php';
