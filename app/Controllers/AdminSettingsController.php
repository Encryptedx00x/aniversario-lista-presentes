<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\SiteConfig;
use App\Helpers\View;
use App\Models\SettingsModel;

class AdminSettingsController {

    public function index(array $params = []): void {
        $cfg = SiteConfig::getPublicSettings();
        View::render('admin/settings/index', [
            'title' => 'Configurações',
            'layout' => 'admin',
            'settings' => [
                'event_person_name' => (string) ($cfg['eventPersonName'] ?? ''),
                'event_age' => (string) ($cfg['eventAge'] ?? ''),
                'event_date' => (string) ($cfg['eventDate'] ?? ''),
                'event_location' => (string) ($cfg['eventLocation'] ?? 'São Paulo'),
            ],
        ]);
    }

    public function save(array $params = []): void {
        $name = trim((string) ($_POST['event_person_name'] ?? ''));
        $age = max(1, (int) ($_POST['event_age'] ?? 0));
        $date = trim((string) ($_POST['event_date'] ?? ''));
        $location = trim((string) ($_POST['event_location'] ?? ''));

        if ($name === '' || $date === '') {
            Flash::set('error', 'Nome e data do evento são obrigatórios.');
            header('Location: /admin/settings');
            exit;
        }

        (new SettingsModel)->upsertMany([
            'event_person_name' => $name,
            'event_age' => (string) $age,
            'event_date' => $date,
            'event_location' => $location,
        ]);

        Flash::set('success', 'Configurações do evento atualizadas.');
        header('Location: /admin/settings');
        exit;
    }
}
