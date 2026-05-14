<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Models\PaymentModel;

/**
 * Admin actions sobre payments. Hoje só tem o "descartar pendente"
 * usado pelo dashboard, mas serve de casa pra futuras ações (refund,
 * forçar requery no MP, etc.).
 */
class AdminPaymentController {

    public function dismiss(array $params = []): void {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Flash::set('error', 'Pagamento inválido.');
            header('Location: /admin/dashboard');
            exit;
        }

        $model   = new PaymentModel;
        $payment = $model->findByIdAdmin($id);
        if (!$payment) {
            Flash::set('error', 'Pagamento não encontrado.');
            header('Location: /admin/dashboard');
            exit;
        }

        if (($payment['mp_status'] ?? '') !== 'pending') {
            Flash::set('error', 'Esse PIX já foi processado e não pode ser descartado.');
            header('Location: /admin/dashboard');
            exit;
        }

        $ok = $model->adminDismiss($id);
        if ($ok) {
            Flash::set('success', 'PIX pendente descartado.');
        } else {
            Flash::set('error', 'Não consegui descartar agora. Tente novamente.');
        }
        header('Location: /admin/dashboard');
        exit;
    }

    public function delete(array $params = []): void {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Flash::set('error', 'Pagamento inválido.');
            header('Location: /admin/reservations');
            exit;
        }

        $ok = (new PaymentModel)->adminDelete($id);
        Flash::set(
            $ok ? 'success' : 'error',
            $ok ? 'Pagamento apagado.' : 'Não foi possível apagar esse pagamento.'
        );

        $back = '/admin/reservations';
        $qs   = [];
        if (!empty($_POST['action_type'])) { $qs['action_type'] = (string) $_POST['action_type']; }
        if (!empty($_POST['status']))      { $qs['status']      = (string) $_POST['status']; }
        if ($qs) { $back .= '?' . http_build_query($qs); }
        header('Location: ' . $back);
        exit;
    }
}
