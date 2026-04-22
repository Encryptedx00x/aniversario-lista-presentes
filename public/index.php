<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/app.php';

use App\Helpers\Session;

Session::start();

$uri    = strtok($_SERVER['REQUEST_URI'], '?');
$uri    = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = require ROOT_PATH . '/config/routes.php';

\Router::dispatch($uri, $method, $routes);
