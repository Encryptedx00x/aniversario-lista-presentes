<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\View;
use App\Models\SettingsModel;

class AdminPixController {

    /**
     * Legacy "manual PIX" display fields (still rendered for reference on /pix).
     */
    private const LEGACY_KEYS = [
        'pix_name',
        'pix_key',
        'pix_bank',
        'pix_city',
        'pix_code',
    ];

    /**
     * MercadoPago credentials + public URL. Two pairs (test/live) so the
     * admin can swap modes without losing the other pair.
     */
    private const MP_KEYS = [
        'mp_mode',
        'mp_access_token_test',
        'mp_access_token_live',
        'mp_webhook_secret_test',
        'mp_webhook_secret_live',
        'mp_public_url',
    ];

    public function index(array $params = []): void {
        $allKeys = array_merge(self::LEGACY_KEYS, self::MP_KEYS);
        $stored  = (new SettingsModel)->getMany($allKeys);

        $defaults = array_fill_keys($allKeys, '');
        $defaults['mp_mode'] = 'test';

        View::render('admin/pix/index', [
            'title'    => 'Configurações do PIX',
            'layout'   => 'admin',
            'settings' => array_merge($defaults, $stored),
        ]);
    }

    public function save(array $params = []): void {
        // Legacy display fields
        $name = trim((string) ($_POST['pix_name'] ?? ''));
        $key  = trim((string) ($_POST['pix_key']  ?? ''));
        $bank = trim((string) ($_POST['pix_bank'] ?? ''));
        $city = trim((string) ($_POST['pix_city'] ?? ''));
        $code = trim((string) ($_POST['pix_code'] ?? ''));

        // MercadoPago fields
        $mode      = ($_POST['mp_mode'] ?? 'test') === 'live' ? 'live' : 'test';
        $tokenTest = trim((string) ($_POST['mp_access_token_test']   ?? ''));
        $tokenLive = trim((string) ($_POST['mp_access_token_live']   ?? ''));
        $secretTst = trim((string) ($_POST['mp_webhook_secret_test'] ?? ''));
        $secretLiv = trim((string) ($_POST['mp_webhook_secret_live'] ?? ''));
        $publicUrl = trim((string) ($_POST['mp_public_url']          ?? ''));

        if ($name === '') {
            Flash::set('error', 'Nome do recebedor é obrigatório.');
            header('Location: /admin/pix');
            exit;
        }

        if ($publicUrl !== '' && filter_var($publicUrl, FILTER_VALIDATE_URL) === false) {
            Flash::set('error', 'URL pública inválida (ex.: https://meusite.com.br).');
            header('Location: /admin/pix');
            exit;
        }
        // Strip any trailing slash so we can always concat /webhooks/...
        $publicUrl = rtrim($publicUrl, '/');

        // Light credential format checks — gives the admin a fast signal that
        // they pasted the wrong field. Empty is allowed for the mode they
        // are not currently using.
        if ($tokenTest !== '' && !str_starts_with($tokenTest, 'TEST-')) {
            Flash::set('error', 'O Access Token de Sandbox deve começar com "TEST-".');
            header('Location: /admin/pix');
            exit;
        }
        if ($tokenLive !== '' && !str_starts_with($tokenLive, 'APP_USR-')) {
            Flash::set('error', 'O Access Token de Produção deve começar com "APP_USR-".');
            header('Location: /admin/pix');
            exit;
        }

        // Going to live mode requires the live pair + public URL to be filled.
        if ($mode === 'live' && ($tokenLive === '' || $publicUrl === '')) {
            Flash::set('error', 'Pra usar Modo Produção, preencha o Access Token (Produção) e a URL pública.');
            header('Location: /admin/pix');
            exit;
        }
        if ($mode === 'test' && $tokenTest !== '' && $publicUrl === '') {
            Flash::set('error', 'Pra usar Modo Sandbox, preencha também a URL pública (use ngrok em dev).');
            header('Location: /admin/pix');
            exit;
        }

        (new SettingsModel)->upsertMany([
            'pix_name'               => $name,
            'pix_key'                => $key,
            'pix_bank'               => $bank,
            'pix_city'               => $city,
            'pix_code'               => $code,
            'mp_mode'                => $mode,
            'mp_access_token_test'   => $tokenTest,
            'mp_access_token_live'   => $tokenLive,
            'mp_webhook_secret_test' => $secretTst,
            'mp_webhook_secret_live' => $secretLiv,
            'mp_public_url'          => $publicUrl,
        ]);

        Flash::set('success', 'Configurações do PIX atualizadas.');
        header('Location: /admin/pix');
        exit;
    }
}
