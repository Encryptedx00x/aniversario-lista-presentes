<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\View;
use App\Models\SettingModel;

class AdminSettingsController {
    public function index(array $params = []): void {
        $settings = (new SettingModel)->getMany([
            'birthday_date',
            'pix_key',
            'pix_receiver',
            'pix_city',
            'pix_qr_code',
            'pix_copy_paste',
        ]);

        View::render('admin/settings/index', [
            'title' => 'Configurações',
            'layout' => 'admin',
            'settings' => $settings,
        ]);
    }

    public function save(array $params = []): void {
        if (!\App\Middleware\CsrfMiddleware::validate($_POST['csrf_token'] ?? null)) {
            Flash::set('error', 'Token CSRF inválido.');
            header('Location: /admin/settings');
            exit;
        }

        $birthdayDate = trim((string) ($_POST['birthday_date'] ?? ''));
        if ($birthdayDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdayDate)) {
            Flash::set('error', 'Data de aniversário inválida. Use o formato AAAA-MM-DD.');
            header('Location: /admin/settings');
            exit;
        }

        $model = new SettingModel;
        $model->set('birthday_date', $birthdayDate);
        $model->set('pix_key', trim((string) ($_POST['pix_key'] ?? '')));
        $model->set('pix_receiver', trim((string) ($_POST['pix_receiver'] ?? '')));
        $model->set('pix_city', trim((string) ($_POST['pix_city'] ?? '')));
        $model->set('pix_qr_code', trim((string) ($_POST['pix_qr_code'] ?? '')));
        $model->set('pix_copy_paste', trim((string) ($_POST['pix_copy_paste'] ?? '')));

        Flash::set('success', 'Configurações salvas com sucesso.');
        header('Location: /admin/settings');
        exit;
    }
}
