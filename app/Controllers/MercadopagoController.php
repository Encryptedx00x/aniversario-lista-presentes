<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\MercadoPagoClient;
use App\Helpers\MercadoPagoException;
use App\Models\GiftModel;
use App\Models\PaymentModel;
use App\Models\SettingsModel;
use App\Models\UserModel;

/**
 * MercadoPago PIX integration.
 *
 *   POST /pix/create              — guest creates a PIX cobrança from /pix?gift_id=X
 *   GET  /pix/status/{id}         — guest polls payment status (every ~4s in JS)
 *   POST /webhooks/mercadopago    — MP calls back when a payment status changes
 *
 * Settings used (managed via /admin/pix):
 *   mp_mode                  — 'test' | 'live'
 *   mp_access_token_test     — TEST-... (sandbox)
 *   mp_access_token_live     — APP_USR-... (production)
 *   mp_webhook_secret_test   — used to verify x-signature in sandbox
 *   mp_webhook_secret_live   — used to verify x-signature in production
 *   mp_public_url            — e.g. https://meusite.com.br (no trailing slash)
 *
 * Webhook flow is the source of truth. Polling is just so the user sees
 * the confirmation without refreshing — if they close the tab, the
 * reservation will still appear in /my-gifts because the webhook is
 * independent of the page.
 */
class MercadopagoController {

    private const MIN_AMOUNT_BRL = 1.00;
    private const PIX_EXPIRES_SECONDS = 86400; // 24h, MP default

    private const SETTINGS_KEYS = [
        'mp_mode',
        'mp_access_token_test',
        'mp_access_token_live',
        'mp_webhook_secret_test',
        'mp_webhook_secret_live',
        'mp_public_url',
    ];

    // ─── POST /pix/create ──────────────────────────────────────────────────
    public function createPix(array $params = []): void {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Faça login para gerar o PIX.'], 401);
        }

        // gift_id é opcional. Quando ausente/0, usa o sentinel "Contribuição
        // livre" — convidado mandou um PIX sem reservar item específico.
        $giftIdRaw = $_POST['gift_id'] ?? null;
        $giftId    = (int) filter_var($giftIdRaw, FILTER_VALIDATE_INT);
        if ($giftId < 0) $giftId = 0;
        $amount    = filter_var($_POST['amount']  ?? null, FILTER_VALIDATE_FLOAT);
        if (!$amount || $amount < self::MIN_AMOUNT_BRL) {
            $this->json(['success' => false, 'error' => 'Valor mínimo: R$ ' . number_format(self::MIN_AMOUNT_BRL, 2, ',', '.')], 422);
        }

        $cfg = (new SettingsModel)->getMany(self::SETTINGS_KEYS);
        $mode = ($cfg['mp_mode'] ?? 'test') === 'live' ? 'live' : 'test';
        $token = (string) ($cfg['mp_access_token_' . $mode] ?? '');
        $publicUrl = rtrim((string) ($cfg['mp_public_url'] ?? ''), '/');

        if ($token === '' || $publicUrl === '') {
            error_log('[mp-create] credentials missing for mode=' . $mode);
            $this->json([
                'success' => false,
                'error'   => 'PIX temporariamente indisponível. Tente em alguns minutos.',
            ], 503);
        }

        $giftModel = new GiftModel;
        if ($giftId > 0) {
            $gift = $giftModel->findById($giftId);
            if (!$gift || (int) ($gift['is_active'] ?? 0) !== 1) {
                $this->json(['success' => false, 'error' => 'Presente não encontrado.'], 404);
            }
        } else {
            $gift   = $giftModel->findOrCreateFreeContribution();
            $giftId = (int) $gift['id'];
        }

        $user = (new UserModel)->findById($userId);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'Sessão inválida.'], 401);
        }

        $paymentModel = new PaymentModel;

        // Reuse a still-pending cobrança so reload doesn't create duplicates.
        $existing = $paymentModel->findPendingForUserAndGift($userId, (int) $giftId);
        if ($existing) {
            $this->json([
                'success'        => true,
                'reused'         => true,
                'payment_id'     => (int) $existing['id'],
                'qr_code'        => (string) ($existing['mp_qr_code'] ?? ''),
                'qr_code_b64'    => (string) ($existing['mp_qr_code_b64'] ?? ''),
                'amount'         => (float) $existing['amount'],
                'expires_at'     => (string) ($existing['expires_at'] ?? ''),
            ]);
        }

        $client = new MercadoPagoClient($token, $mode);
        $idempotencyKey = sprintf('pix-%d-%d-%d', $userId, (int) $giftId, (int) (microtime(true) * 1000));
        // MP exige formato com milissegundos e offset com `:` (ex.:
        // 2026-05-02T13:00:00.000-03:00). PHP `date('c')` omite milissegundos.
        $expiresAtIso   = date('Y-m-d\TH:i:s.000P', time() + self::PIX_EXPIRES_SECONDS);

        $payerName  = trim((string) ($user['name'] ?? '')) ?: 'Convidado';
        $payerNames = preg_split('/\s+/', $payerName, 2) ?: [$payerName];

        // MP exige um email com formato válido em payer.email. Se o user
        // não tem email no DB (ou veio bagunçado), gera um placeholder
        // estável por user_id — em sandbox o MP só checa formato.
        $payerEmail = trim((string) ($user['email'] ?? ''));
        if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
            $payerEmail = 'convidado+' . $userId . '@listadepresentes.local';
        }

        // notification_url só é incluído se a URL pública for HTTPS e não
        // for um subdomínio gratuito de teste (hostingersite.com etc.).
        // MP às vezes devolve 500 internal_error ao tentar validar URLs
        // que considera não confiáveis. Sem o campo, o webhook continua
        // funcionando — basta estar configurado no painel MP > Webhooks.
        $payload = [
            'transaction_amount' => round((float) $amount, 2),
            'description'        => 'Lista de presentes: ' . (string) ($gift['name'] ?? 'Presente'),
            'payment_method_id'  => 'pix',
            'date_of_expiration' => $expiresAtIso,
            'payer' => [
                'email'      => $payerEmail,
                'first_name' => $payerNames[0],
                'last_name'  => $payerNames[1] ?? '',
            ],
            'metadata' => [
                'gift_id' => (int) $giftId,
                'user_id' => $userId,
            ],
        ];

        $isFreeSubdomain = (bool) preg_match('/(hostingersite\.com|ngrok|localhost|\.local)/i', $publicUrl);
        if ($publicUrl !== '' && str_starts_with($publicUrl, 'https://') && !$isFreeSubdomain) {
            $payload['notification_url'] = $publicUrl . '/webhooks/mercadopago';
        }

        try {
            $response = $client->createPixPayment($payload, $idempotencyKey);
        } catch (MercadoPagoException $e) {
            $mpResp = $e->getResponse();
            error_log('[mp-create] ' . $e->getMessage() . ' | ' . json_encode($mpResp));
            // Em modo sandbox, mostramos o detalhe do MP direto na mensagem
            // pra Maria entender o que falhou sem fuçar log do Hostinger.
            $userMsg = 'Não foi possível gerar o PIX agora. Tente novamente.';
            if ($mode === 'test') {
                $cause = '';
                if (is_array($mpResp)) {
                    if (!empty($mpResp['cause']) && is_array($mpResp['cause'])) {
                        $causeBits = [];
                        foreach ($mpResp['cause'] as $c) {
                            $causeBits[] = (string) ($c['description'] ?? $c['code'] ?? '');
                        }
                        $cause = implode(' / ', array_filter($causeBits));
                    }
                    $cause = $cause !== ''
                        ? $cause
                        : (string) ($mpResp['message'] ?? $mpResp['error'] ?? '');
                }
                $userMsg = '[sandbox] ' . $e->getMessage()
                    . ($cause !== '' ? ': ' . $cause : '');
            }
            $this->json([
                'success'    => false,
                'error'      => $userMsg,
                'mp_details' => $mode === 'test' ? $mpResp     : null,
                'mp_request' => $mode === 'test' ? $payload    : null,
            ], 502);
        }

        $mpId   = (string) ($response['id'] ?? '');
        $poi    = $response['point_of_interaction']['transaction_data'] ?? [];
        $qrText = (string) ($poi['qr_code'] ?? '');
        $qrB64  = (string) ($poi['qr_code_base64'] ?? '');

        if ($mpId === '' || $qrText === '') {
            error_log('[mp-create] malformed MP response: ' . json_encode($response));
            $errResp = [
                'success' => false,
                'error'   => 'MercadoPago retornou uma resposta incompleta. Tente novamente.',
            ];
            if ($mode === 'test') {
                $errResp['mp_response'] = $response;
            }
            $this->json($errResp, 502);
        }

        $paymentId = $paymentModel->create([
            'user_id'        => $userId,
            'gift_id'        => (int) $giftId,
            'amount'         => round((float) $amount, 2),
            'mp_payment_id'  => $mpId,
            'mp_status'      => (string) ($response['status'] ?? 'pending'),
            'mp_qr_code'     => $qrText,
            'mp_qr_code_b64' => $qrB64,
            'mp_mode'        => $mode,
            'expires_at'     => date('Y-m-d H:i:s', time() + self::PIX_EXPIRES_SECONDS),
            'raw_response'   => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);

        $this->json([
            'success'     => true,
            'reused'      => false,
            'payment_id'  => $paymentId,
            'qr_code'     => $qrText,
            'qr_code_b64' => $qrB64,
            'amount'      => round((float) $amount, 2),
            'expires_at'  => date('c', time() + self::PIX_EXPIRES_SECONDS),
        ]);
    }

    // ─── GET /pix/status/{id} ──────────────────────────────────────────────
    public function paymentStatus(array $params = []): void {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Sessão expirada.'], 401);
        }

        $paymentLocalId = (int) ($params['id'] ?? 0);
        if ($paymentLocalId <= 0) {
            $this->json(['success' => false, 'error' => 'Pagamento inválido.'], 404);
        }

        $paymentModel = new PaymentModel;
        $payment = $paymentModel->findByIdForUser($paymentLocalId, $userId);
        if (!$payment) {
            $this->json(['success' => false, 'error' => 'Pagamento não encontrado.'], 404);
        }

        // Self-healing: se o status local ainda é não-final, consulta o MP
        // direto. Garante que mesmo sem webhook (ou se o webhook falhou)
        // o polling eventualmente detecta o pagamento aprovado.
        $finalStatuses = ['approved', 'rejected', 'cancelled', 'refunded', 'charged_back'];
        if (!in_array((string) $payment['mp_status'], $finalStatuses, true)) {
            $cfg   = (new SettingsModel)->getMany(self::SETTINGS_KEYS);
            $mode  = (string) $payment['mp_mode'];
            $token = (string) ($cfg['mp_access_token_' . $mode] ?? '');
            $mpId  = (string) $payment['mp_payment_id'];

            if ($token !== '' && $mpId !== '') {
                try {
                    $remote = (new MercadoPagoClient($token, $mode))->getPayment($mpId);
                    $remoteStatus = (string) ($remote['status'] ?? '');
                    $rawJson      = json_encode($remote, JSON_UNESCAPED_UNICODE);

                    if ($remoteStatus === 'approved' && $payment['reservation_id'] === null) {
                        $this->approveAndCreateReservation(
                            (int) $payment['id'],
                            (int) $payment['user_id'],
                            (int) $payment['gift_id'],
                            $rawJson
                        );
                        $payment = $paymentModel->findByIdForUser($paymentLocalId, $userId) ?: $payment;
                    } elseif ($remoteStatus !== '' && $remoteStatus !== (string) $payment['mp_status']) {
                        $paymentModel->updateStatus((int) $payment['id'], $remoteStatus, $rawJson);
                        $payment['mp_status'] = $remoteStatus;
                    }
                } catch (MercadoPagoException $e) {
                    // Falha de consulta não interrompe o polling — só loga.
                    error_log('[mp-status] poll lookup failed: ' . $e->getMessage());
                }
            }
        }

        $this->json([
            'success'        => true,
            'status'         => (string) $payment['mp_status'],
            'reservation_id' => $payment['reservation_id'] !== null ? (int) $payment['reservation_id'] : null,
            'expires_at'     => (string) ($payment['expires_at'] ?? ''),
        ]);
    }

    // ─── POST /webhooks/mercadopago ────────────────────────────────────────
    public function webhook(array $params = []): void {
        $rawBody  = (string) file_get_contents('php://input');
        $headers  = $this->collectHeaders();
        $signature = (string) ($headers['x-signature'] ?? '');
        $requestId = (string) ($headers['x-request-id'] ?? '');

        $body = json_decode($rawBody, true);
        if (!is_array($body)) {
            error_log('[mp-webhook] invalid JSON: ' . substr($rawBody, 0, 500));
            $this->plain(400, 'invalid body');
        }

        // MP sometimes sends type='payment' or topic='payment' depending on
        // the webhook configuration. Normalize to a single check.
        $type  = (string) ($body['type'] ?? $body['topic'] ?? '');
        $dataId = (string) ($body['data']['id'] ?? $body['resource'] ?? '');

        if ($type !== 'payment' && stripos($type, 'payment') === false) {
            // Not a payment event — acknowledge so MP stops retrying.
            error_log('[mp-webhook] ignored type=' . $type);
            $this->plain(200, 'ignored');
        }

        if ($dataId === '') {
            error_log('[mp-webhook] missing data.id: ' . $rawBody);
            $this->plain(400, 'missing id');
        }

        $cfg = (new SettingsModel)->getMany(self::SETTINGS_KEYS);
        $paymentModel = new PaymentModel;
        $local = $paymentModel->findByMpId($dataId);

        // The mode of the secret used for verification matches the mode of
        // the payment record (stored at create time). If we don't have a
        // local record yet, fall back to the currently-configured mode.
        $mode   = $local
            ? (string) $local['mp_mode']
            : (($cfg['mp_mode'] ?? 'test') === 'live' ? 'live' : 'test');
        $secret = (string) ($cfg['mp_webhook_secret_' . $mode] ?? '');
        $token  = (string) ($cfg['mp_access_token_' . $mode]  ?? '');

        if ($token === '') {
            error_log('[mp-webhook] no access token for mode=' . $mode);
            $this->plain(503, 'not configured');
        }

        $client = new MercadoPagoClient($token, $mode);

        // If a secret is configured, the signature must validate. We allow an
        // empty secret only as an escape hatch during initial setup — Maria
        // should configure it before going live.
        if ($secret !== '' && !$client->verifyWebhookSignature($signature, $requestId, $dataId, $secret)) {
            error_log('[mp-webhook] signature mismatch for id=' . $dataId);
            $this->plain(401, 'bad signature');
        }

        // Always fetch the canonical payment from MP. The webhook body just
        // hints that something changed — we don't trust amounts or status
        // from the request body.
        try {
            $remote = $client->getPayment($dataId);
        } catch (MercadoPagoException $e) {
            error_log('[mp-webhook] getPayment failed: ' . $e->getMessage());
            $this->plain(502, 'mp lookup failed');
        }

        $remoteStatus = (string) ($remote['status'] ?? '');
        $rawJson      = json_encode($remote, JSON_UNESCAPED_UNICODE);

        if (!$local) {
            // Edge case: we never created a local row but MP is calling us.
            // Could happen if our INSERT failed after MP created the cobrança.
            // Reconstruct from metadata so Maria isn't owed money silently.
            $userId = (int) ($remote['metadata']['user_id'] ?? 0);
            $giftId = (int) ($remote['metadata']['gift_id'] ?? 0);
            if ($userId <= 0 || $giftId <= 0) {
                error_log('[mp-webhook] orphan payment, no metadata: ' . $dataId);
                $this->plain(200, 'orphan ignored');
            }
            $localId = $paymentModel->create([
                'user_id'        => $userId,
                'gift_id'        => $giftId,
                'amount'         => (float) ($remote['transaction_amount'] ?? 0),
                'mp_payment_id'  => $dataId,
                'mp_status'      => $remoteStatus,
                'mp_qr_code'     => '',
                'mp_qr_code_b64' => '',
                'mp_mode'        => $mode,
                'expires_at'     => null,
                'raw_response'   => $rawJson,
            ]);
            $local = $paymentModel->findByMpId($dataId);
            if (!$local) {
                error_log('[mp-webhook] reconstruction failed for id=' . $dataId);
                $this->plain(500, 'recon failed');
            }
        }

        // Idempotency: already approved + reservation linked → ack and stop.
        if ($local['mp_status'] === 'approved' && $local['reservation_id'] !== null) {
            $this->plain(200, 'already approved');
        }

        if ($remoteStatus === 'approved') {
            $this->approveAndCreateReservation((int) $local['id'], (int) $local['user_id'], (int) $local['gift_id'], $rawJson);
            error_log('[mp-webhook] approved local_id=' . $local['id'] . ' mp_id=' . $dataId);
        } else {
            // pending, in_process, rejected, cancelled, expired, ...
            $paymentModel->updateStatus((int) $local['id'], $remoteStatus, $rawJson);
            error_log('[mp-webhook] status=' . $remoteStatus . ' local_id=' . $local['id']);
        }

        $this->plain(200, 'ok');
    }

    // ─── helpers ───────────────────────────────────────────────────────────

    private function approveAndCreateReservation(int $paymentId, int $userId, int $giftId, string $rawJson): void {
        $paymentModel = new PaymentModel;
        $db = $paymentModel->getDb();

        try {
            $db->beginTransaction();

            // Insert a reservation row mirroring the contributePix shape so
            // /my-gifts and /admin/reservations pick it up automatically.
            $stmt = $db->prepare(
                "INSERT INTO reservations (gift_id, user_id, status, action_type)
                 VALUES (:gift_id, :user_id, 'active', 'pix')"
            );
            $stmt->execute([
                ':gift_id' => $giftId,
                ':user_id' => $userId,
            ]);
            $reservationId = (int) $db->lastInsertId();

            $paymentModel->markApprovedWithReservation($paymentId, $reservationId, $rawJson);

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[mp-webhook] approveAndCreateReservation failed: ' . $e->getMessage());
            // Don't 500 to MP — they retry forever. Leave the row pending
            // and let the next webhook attempt re-process it.
        }
    }

    /**
     * @return array<string,string> lowercased header names → values
     */
    private function collectHeaders(): array {
        if (function_exists('getallheaders')) {
            $raw = getallheaders() ?: [];
        } else {
            $raw = [];
            foreach ($_SERVER as $k => $v) {
                if (str_starts_with($k, 'HTTP_')) {
                    $name = strtolower(str_replace('_', '-', substr($k, 5)));
                    $raw[$name] = $v;
                }
            }
        }
        $out = [];
        foreach ($raw as $k => $v) {
            $out[strtolower((string) $k)] = (string) $v;
        }
        return $out;
    }

    private function json(array $payload, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function plain(int $statusCode, string $body): void {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $body;
        exit;
    }
}
