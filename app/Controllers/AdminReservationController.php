<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
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

    public function delete(array $params = []): void {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Flash::set('error', 'Reserva inválida.');
            header('Location: /admin/reservations');
            exit;
        }

        $result = (new ReservationModel)->adminDelete($id);

        if (!($result['success'] ?? false)) {
            Flash::set('error', $result['error'] ?? 'Não foi possível apagar agora.');
        } else {
            $msg = !empty($result['stock_released'])
                ? 'Reserva apagada — ' . (int) $result['released_qty'] . ' '
                    . ((int) $result['released_qty'] === 1 ? 'unidade' : 'unidades')
                    . ' devolvidas ao estoque.'
                : 'Reserva apagada.';
            Flash::set('success', $msg);
        }

        // Preserva os filtros atuais ao voltar.
        $back = '/admin/reservations';
        $qs   = [];
        if (!empty($_POST['action_type'])) { $qs['action_type'] = (string) $_POST['action_type']; }
        if (!empty($_POST['status']))      { $qs['status']      = (string) $_POST['status']; }
        if ($qs) { $back .= '?' . http_build_query($qs); }

        header('Location: ' . $back);
        exit;
    }
}
