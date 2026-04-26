<?php
// Route table: METHOD => [URI => [ControllerClass, method, ?middleware]]
// Third element can be 'auth' (admin) or 'guest' (logged guest).
// Dynamic segments use {param} syntax — Router extracts and passes $params array to the action.
return [
    'GET' => [
        '/'                               => ['HomeController',        'index'],
        '/gifts'                          => ['GiftController',        'index'],
        '/gifts/filter'                   => ['GiftController',        'filter'],
        '/pix'                            => ['PixController',         'index'],
        '/csrf-token'                     => ['ReservationController', 'csrfToken'],
        '/my-gifts'                       => ['ReservationController', 'myGifts',      'guest'],
        '/admin/login'                    => ['AdminAuthController',   'loginForm'],
        '/admin/dashboard'                => ['AdminAuthController',   'dashboard',    'auth'],
        '/admin/gifts'                    => ['AdminGiftController',   'index',        'auth'],
        '/admin/gifts/create'             => ['AdminGiftController',   'create',       'auth'],
        '/admin/gifts/{id}/edit'          => ['AdminGiftController',   'edit',         'auth'],
    ],
    'POST' => [
        '/reserve'                        => ['ReservationController', 'store',        'guest'],
        '/my-gifts/cancel'                => ['ReservationController', 'cancel',       'guest'],
        '/pix/contribute'                 => ['PixController',         'contribute'],
        '/admin/login'                    => ['AdminAuthController',   'loginPost'],
        '/admin/logout'                   => ['AdminAuthController',   'logout',       'auth'],
        '/admin/gifts/store'              => ['AdminGiftController',   'store',        'auth'],
        '/admin/gifts/{id}/update'        => ['AdminGiftController',   'update',       'auth'],
        '/admin/gifts/{id}/delete'        => ['AdminGiftController',   'delete',       'auth'],
        '/admin/gifts/{id}/toggle-active' => ['AdminGiftController',   'toggleActive', 'auth'],
        '/auth/guest-signup'              => ['GuestAuthController',   'signupPost'],
        '/auth/guest-login'               => ['GuestAuthController',   'loginPost'],
        '/auth/guest-logout'              => ['GuestAuthController',   'logout'],
    ],
];
