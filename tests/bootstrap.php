<?php

// Suppress strict errors
error_reporting(E_ALL ^ E_STRICT);

// Set up autoloading (after composer install)
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('', __DIR__);
