<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\View;
use App\Models\ContributionModel;
use App\Models\GiftModel;
use App\Models\ReservationModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class PixController {
    public function index(array $params = []): void {
        $settings = (new SettingModel)->getMany([
            'pix_key',
            'pix_receiver',
            'pix_city',
            'pix_qr_code',
            'pix_copy_paste',
        ]);

        $guest = null;
        if (($_SESSION['role'] ?? '') === 'guest' && !empty($_SESSION['user_id'])) {
            $guest = (new UserModel)->findById((int) $_SESSION['user_id']) ?: null;
        }

        $giftId = filter_var($_GET['gift_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $gift = $giftId > 0 ? (new GiftModel)->findById($giftId) : null;

        View::render('public/pix/index', [
            'title'             => APP_NAME . ' — Pix',
            'layout'            => 'main',
            'pixKey'            => $settings['pix_key'] ?? '',
            'pixReceiver'       => $settings['pix_receiver'] ?? '',
            'pixCity'           => $settings['pix_city'] ?? '',
            'pixQrCode'         => $settings['pix_qr_code'] ?? '',
            'pixCopyPaste'      => $settings['pix_copy_paste'] ?? '',
            'prefillGuestName'  => (string) ($guest['name'] ?? ''),
            'prefillGuestEmail' => (string) ($guest['email'] ?? ''),
            'selectedGift'      => $gift ?: null,
            'suggestedAmount'   => (float) ($_GET['amount'] ?? ($gift['price'] ?? 0)),
        ]);
    }

    public function contribute(array $params = []): void {
        $name = trim((string) ($_POST['guest_name'] ?? ''));
        $email = trim((string) ($_POST['guest_email'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $giftId = filter_var($_POST['gift_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $amountRaw = str_replace(',', '.', trim((string) ($_POST['amount'] ?? '')));
        $amount = filter_var($amountRaw, FILTER_VALIDATE_FLOAT);

        if ($name === '' || $email === '' || $amount === false) {
            Flash::set('error', 'Preencha nome, e-mail e valor corretamente.');
            header('Location: /pix');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Informe um e-mail válido.');
            header('Location: /pix');
            exit;
        }
        if ($amount <= 0) {
            Flash::set('error', 'O valor da contribuição deve ser maior que zero.');
            header('Location: /pix');
            exit;
        }

        if ($giftId > 0) {
            if (($_SESSION['role'] ?? '') !== 'guest' || empty($_SESSION['user_id'])) {
                Flash::set('error', 'Para marcar o presente como dado via Pix, faça login e tente novamente.');
                header('Location: /gifts');
                exit;
            }

            $reserveResult = (new ReservationModel)->reserve($giftId, (int) $_SESSION['user_id']);
            if (!($reserveResult['success'] ?? false)) {
                Flash::set('error', $reserveResult['error'] ?? 'Não foi possível reservar este presente via Pix.');
                header('Location: /gifts');
                exit;
            }
        }

        (new ContributionModel)->create([
            'guest_name'  => $name,
            'guest_email' => $email,
            'amount'      => $amount,
            'message'     => $message !== '' ? $message : null,
        ]);

        Flash::set('success', 'Contribuição registrada com sucesso! Status: pendente.');
        header('Location: /pix');
        exit;
    }
}
