<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GiftModel;
use App\Models\PaymentModel;
use App\Models\ReservationModel;
use App\Helpers\{View, Flash};

class AdminAuthController {
    public function loginForm(array $params = []): void {
        View::render('admin/login', ['title' => 'Admin Login', 'layout' => 'admin_bare']);
    }

    public function loginPost(array $params = []): void {
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Flash::set('error', 'Preencha e-mail e senha.');
            header('Location: /admin/login');
            exit;
        }

        $user = (new UserModel)->findByEmail($email);

        // SEC-04: password_verify against stored hash; also confirm admin role
        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);          // SEC-04/SEC-06: prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = 'admin';
            $_SESSION['name']    = $user['name'];
            unset($_SESSION['csrf_token']);        // Force CSRF token regeneration after privilege change
            header('Location: /admin/dashboard');
            exit;
        }

        // Generic error — do NOT reveal whether email or password was wrong (SEC-06)
        Flash::set('error', 'Credenciais inválidas.');
        header('Location: /admin/login');
        exit;
    }

    public function dashboard(array $params = []): void {
        // AuthMiddleware already verified $_SESSION['role'] === 'admin'
        $reservModel  = new ReservationModel;
        $paymentModel = new PaymentModel;

        View::render('admin/dashboard', [
            'title'          => 'Dashboard',
            'layout'         => 'admin',
            'giftStats'      => (new GiftModel)->adminStats(),
            'reservSummary'  => $reservModel->adminSummary(),
            'paymentStats'   => $paymentModel->adminStats(),
            'pendingPayments'=> $paymentModel->getPendingForAdmin(10),
            'recent'         => $reservModel->recentActivity(6),
        ]);
    }

    public function logout(array $params = []): void {
        $_SESSION = [];
        session_unset();
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}
