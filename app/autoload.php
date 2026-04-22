<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $base   = dirname(__DIR__) . '/app/';
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file     = $base . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Router lives at app/Router.php without a namespace — require it directly
require dirname(__DIR__) . '/app/Router.php';
