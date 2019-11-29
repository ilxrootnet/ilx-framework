<?php


// Composer autoloader
require dirname(__DIR__).'/vendor/autoload.php';

use Ilx\Ilx;

Ilx::setRootPath(dirname(__DIR__));
Ilx::run();