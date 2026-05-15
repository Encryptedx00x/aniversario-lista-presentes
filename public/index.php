<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/app.php';

use App\Helpers\Session;
use App\Models\ReservationModel;

Session::start();

// Lazy expiry de reservas paradas. Roda uma vez por request, ANTES de
// qualquer leitura de gifts/reservations — garante que a listagem já
// pegue o estoque devolvido se alguém deixou um item parado >30 min
// sem confirmar a compra.
ReservationModel::expireStaleOnce();

// REDIRECT_URL contém a URI original antes do rewrite do Apache (ex: root .htaccess → public/index.php).
// REQUEST_URI após rewrite interno vira "/public/index.php" — não serve para roteamento.
$rawUri = $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'];
$uri    = strtok($rawUri, '?');
$uri    = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = require ROOT_PATH . '/config/routes.php';

\Router::dispatch($uri, $method, $routes);
