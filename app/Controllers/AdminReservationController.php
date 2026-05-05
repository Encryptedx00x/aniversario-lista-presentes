<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\ReservationModel;

class AdminReservationController {

    public function index(array $params = []): void {
        $action = (string) ($_GET['action_type'] ?? 'all'); // all | reserved | pix
        $status = (string) ($_GET['status']      ?? 'active'); // active | cancelled | all

        $action = in_array($action, ['all', 'reserved', 'pix'], true) ? $action : 'all';
        $status = in_array($status, ['all', 'active', 'cancelled'], true) ? $status : 'active';

        $model        = new ReservationModel;
        $reservations = $model->adminList($action, $status);
        $summary      = $model->adminSummary();

        View::render('admin/reservations/index', [
            'title'        => 'Reservas',
            'layout'       => 'admin',
            'reservations' => $reservations,
            'summary'      => $summary,
            'filters'      => [
                'action_type' => $action,
                'status'      => $status,
            ],
        ]);
    }
}
