<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;

class GuestAuthController {

    public function signupPost(array $params = []): void {
        header('Content-Type: application/json');

        // CsrfMiddleware::validate() already called by Router for all POST requests

        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $password =      $_POST['password'] ?? '';

        // Validate required fields
        if ($name === '' || $email === '' || $password === '') {
            echo json_encode(['error' => 'Preencha todos os campos obrigatórios.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'E-mail inválido.']);
            exit;
        }
        if (strlen($password) < 6) {
            echo json_encode(['error' => 'A senha deve ter no mínimo 6 caracteres.']);
            exit;
        }

        $userModel = new UserModel;
        if ($userModel->findByEmail($email)) {
            echo json_encode(['error' => 'Este e-mail já está cadastrado. Tente entrar.']);
            exit;
        }

        $id = $userModel->create([
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone !== '' ? $phone : null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'guest',
        ]);

        // AUTH-03: auto-login immediately after signup
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['role']    = 'guest';
        $_SESSION['name']    = $name;
        unset($_SESSION['csrf_token']); // force new CSRF token

        echo json_encode(['success' => true]);
        exit;
    }

    public function loginPost(array $params = []): void {
        header('Content-Type: application/json');

        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            echo json_encode(['error' => 'Preencha todos os campos obrigatórios.']);
            exit;
        }

        $user = (new UserModel)->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            echo json_encode(['error' => 'E-mail ou senha incorretos.']);
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];
        unset($_SESSION['csrf_token']);

        echo json_encode(['success' => true]);
        exit;
    }

    public function logout(array $params = []): void {
        // AUTH-06: clear session data, invalidate cookie, then destroy session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: /');
        exit;
    }
}
