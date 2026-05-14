<?php
// Route table: METHOD => [URI => [ControllerClass, method, ?middleware]]
// Third element can be 'auth' (admin), 'guest' (logged user), or
// 'public_webhook' (skips CSRF — controller authenticates via signed payload).
// Dynamic segments use {param} syntax — Router extracts and passes $params array to the action.
return [
    'GET' => [
        '/'                               => ['HomeController',        'index'],
        '/pix'                            => ['HomeController',        'pix'],
        '/pix/status/{id}'                => ['MercadopagoController', 'paymentStatus','guest'],
        '/gifts'                          => ['GiftController',        'index'],
        '/gifts/filter'                   => ['GiftController',        'filter'],
        '/csrf-token'                     => ['ReservationController', 'csrfToken'],
        '/my-gifts'                       => ['ReservationController', 'myGifts',      'guest'],
        '/admin/login'                    => ['AdminAuthController',   'loginForm'],
        '/admin/dashboard'                => ['AdminAuthController',   'dashboard',    'auth'],
        '/admin/gifts'                    => ['AdminGiftController',   'index',        'auth'],
        '/admin/gifts/create'             => ['AdminGiftController',   'create',       'auth'],
        '/admin/gifts/{id}/edit'          => ['AdminGiftController',   'edit',         'auth'],
        '/admin/reservations'             => ['AdminReservationController', 'index',   'auth'],
        '/admin/pix'                      => ['AdminPixController',    'index',        'auth'],
        '/admin/settings'                 => ['AdminSettingsController','index',       'auth'],
    ],
    'POST' => [
        '/reserve'                        => ['ReservationController', 'store',        'guest'],
        '/pix/create'                     => ['MercadopagoController', 'createPix',    'guest'],
        '/webhooks/mercadopago'           => ['MercadopagoController', 'webhook',      'public_webhook'],
        '/my-gifts/cancel'                => ['ReservationController', 'cancel',       'guest'],
        '/my-gifts/{id}/confirm-purchase' => ['ReservationController', 'confirmPurchase', 'guest'],
        '/admin/login'                    => ['AdminAuthController',   'loginPost'],
        '/admin/logout'                   => ['AdminAuthController',   'logout',       'auth'],
        '/admin/gifts/store'              => ['AdminGiftController',   'store',        'auth'],
        '/admin/gifts/{id}/update'        => ['AdminGiftController',   'update',       'auth'],
        '/admin/gifts/{id}/delete'        => ['AdminGiftController',   'delete',       'auth'],
        '/admin/gifts/{id}/toggle-active' => ['AdminGiftController',   'toggleActive', 'auth'],
        '/admin/pix'                      => ['AdminPixController',    'save',         'auth'],
        '/admin/payments/{id}/dismiss'    => ['AdminPaymentController','dismiss',     'auth'],
        '/admin/payments/{id}/delete'     => ['AdminPaymentController','delete',      'auth'],
        '/admin/reservations/{id}/delete' => ['AdminReservationController', 'delete', 'auth'],
        '/admin/settings'                 => ['AdminSettingsController','save',        'auth'],
        '/auth/guest-signup'              => ['GuestAuthController',   'signupPost'],
        '/auth/guest-login'               => ['GuestAuthController',   'loginPost'],
        '/auth/guest-logout'              => ['GuestAuthController',   'logout'],
    ],
];
