<?php
// Route table: METHOD => [URI => [ControllerClass, method, ?middleware]]
// Third element 'auth' triggers AuthMiddleware::handle() before the controller runs.
return [
    'GET' => [
        '/'                => ['HomeController',      'index'],
        '/admin/login'     => ['AdminAuthController', 'loginForm'],
        '/admin/dashboard' => ['AdminAuthController', 'dashboard',  'auth'],
        '/admin/gifts'     => ['AdminAuthController', 'giftsStub',  'auth'],
    ],
    'POST' => [
        '/admin/login'     => ['AdminAuthController', 'loginPost'],
        '/admin/logout'    => ['AdminAuthController', 'logout', 'auth'],
    ],
];
