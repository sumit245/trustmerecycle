<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Suppress PDO deprecation warnings from Laravel vendor files (PHP 8.5+)
// These warnings come from Laravel's vendor config and will be fixed in future updates
// To show them during development, comment out the line below
if (PHP_VERSION_ID >= 80500) {
    // Suppress only deprecation warnings (keep other errors visible)
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // Only suppress PDO deprecation warnings
        if ($errno === E_DEPRECATED && strpos($errstr, 'PDO::MYSQL_ATTR_SSL_CA') !== false) {
            return true; // Suppress this specific warning
        }
        return false; // Let other errors through
    }, E_DEPRECATED);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
