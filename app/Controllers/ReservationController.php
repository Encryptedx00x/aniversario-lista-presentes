<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\View;
use App\Middleware\CsrfMiddleware;
use App\Models\ReservationModel;
use App\Models\UserModel;

class ReservationController {

    public function store(array $params = []): void {
        $giftId = filter_var($_POST['gift_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$giftId || (int) $giftId <= 0) {
            $this->json([
                'success' => false,
                'is_exhausted' => false,
                'error' => 'Presente inválido.',
            ], 422);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->json([
                'success' => false,
                'is_exhausted' => false,
                'requires_auth' => true,
                'error' => 'Faça login para reservar um presente.',
            ], 401);
        }

        $guest = (new UserModel)->findById($userId);
        if (!$guest || ($guest['role'] ?? '') !== 'guest') {
            $_SESSION = [];
            session_regenerate_id(true);
            $this->json([
                'success' => false,
                'is_exhausted' => false,
                'requires_auth' => true,
                'error' => 'Sua sessão expirou. Entre novamente para continuar.',
            ], 401);
        }

        $result = (new ReservationModel)->reserve((int) $giftId, $userId);

        if (!($result['success'] ?? false)) {
            $statusCode = ($result['is_exhausted'] ?? false) ? 409 : 422;
            $this->json([
                'success' => false,
                'is_exhausted' => (bool) ($result['is_exhausted'] ?? false),
                'error' => $result['error'] ?? 'Não foi possível concluir sua reserva agora. Tente novamente.',
            ], $statusCode);
        }

        $this->json([
            'success' => true,
            'available_qty' => (int) ($result['available_qty'] ?? 0),
            'is_exhausted' => (bool) ($result['is_exhausted'] ?? false),
        ]);
    }

    public function myGifts(array $params = []): void {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: /gifts');
            exit;
        }

        $reservations = (new ReservationModel)->getActiveByUser($userId);

        View::render('public/my-gifts/index', [
            'title' => APP_NAME . ' — Meus Presentes',
            'layout' => 'main',
            'reservations' => $reservations,
        ]);
    }

    public function cancel(array $params = []): void {
        $reservationId = filter_var($_POST['reservation_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$reservationId || (int) $reservationId <= 0) {
            $this->respondCancelError('Reserva inválida.', 422);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->respondCancelError('Faça login para cancelar uma reserva.', 401);
        }

        $result = (new ReservationModel)->cancel((int) $reservationId, $userId);

        if (!($result['success'] ?? false)) {
            $this->respondCancelError(
                $result['error'] ?? 'Não foi possível cancelar sua reserva agora. Tente novamente.',
                422
            );
        }

        if ($this->expectsJson()) {
            $this->json([
                'success' => true,
                'available_qty' => (int) ($result['available_qty'] ?? 0),
            ]);
        }

        Flash::set('success', 'Reserva cancelada com sucesso.');
        header('Location: /my-gifts');
        exit;
    }

    public function csrfToken(array $params = []): void {
        $this->json([
            'token' => CsrfMiddleware::token(),
        ]);
    }

    private function respondCancelError(string $message, int $statusCode): void {
        if ($this->expectsJson()) {
            $this->json([
                'success' => false,
                'error' => $message,
            ], $statusCode);
        }

        Flash::set('error', $message);
        header('Location: /my-gifts');
        exit;
    }

    private function expectsJson(): bool {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $xhr = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

        return str_contains($accept, 'application/json') || $xhr === 'xmlhttprequest';
    }

    private function json(array $payload, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
