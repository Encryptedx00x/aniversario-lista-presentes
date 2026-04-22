<?php
// Route table: METHOD => [URI => [ControllerClass, method, ?middleware]]
// Third element 'auth' triggers AuthMiddleware::handle() before the controller runs.
// Dynamic segments use {param} syntax — Router extracts and passes $params array to the action.
return [
    'GET' => [
        '/'                               => ['HomeController',      'index'],
        '/admin/login'                    => ['AdminAuthController', 'loginForm'],
        '/admin/dashboard'                => ['AdminAuthController', 'dashboard',   'auth'],
        '/admin/gifts'                    => ['AdminGiftController', 'index',       'auth'],
        '/admin/gifts/create'             => ['AdminGiftController', 'create',      'auth'],
        '/admin/gifts/{id}/edit'          => ['AdminGiftController', 'edit',        'auth'],
    ],
    'POST' => [
        '/admin/login'                    => ['AdminAuthController', 'loginPost'],
        '/admin/logout'                   => ['AdminAuthController', 'logout',      'auth'],
        '/admin/gifts/store'              => ['AdminGiftController', 'store',       'auth'],
        '/admin/gifts/{id}/update'        => ['AdminGiftController', 'update',      'auth'],
        '/admin/gifts/{id}/delete'        => ['AdminGiftController', 'delete',      'auth'],
        '/admin/gifts/{id}/toggle-active' => ['AdminGiftController', 'toggleActive','auth'],
    ],
];
