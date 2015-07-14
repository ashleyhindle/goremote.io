<?php
ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

$debug = (getenv('APP_DEBUG'));

if ('cli' !== php_sapi_name()) {
    ExceptionHandler::register();
}

if ($debug) {
    ErrorHandler::register(null, true);
    ini_set('display_errors', 1);
    error_reporting(-1);
    Debug::enable();
}

$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/controllers.php';

$app->run();

