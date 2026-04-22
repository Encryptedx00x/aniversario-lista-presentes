<?php
declare(strict_types=1);

define('APP_NAME', 'Lista de Presentes');
define('APP_URL',  getenv('APP_URL') ?: 'http://localhost:8000');

$env = getenv('APP_ENV') ?: 'production';

if ($env === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/storage/logs/errors.log');
}
