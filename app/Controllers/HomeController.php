<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;

class HomeController {
    public function index(): void {
        View::render('public/home', ['title' => APP_NAME, 'layout' => 'main']);
    }
}
