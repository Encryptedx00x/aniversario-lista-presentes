<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Minimal MercadoPago REST client (no SDK, no Composer).
 *
 * Talks to https://api.mercadopago.com/v1/payments using the bearer-token
 * pattern documented in the public API:
 *   https://www.mercadopago.com.br/developers/pt/reference/payments/_payments/post
 *
 * The same endpoint is used in both sandbox and live modes — the difference
 * is the access token prefix (`TEST-` vs `APP_USR-`). Mode is stored only
 * for record-keeping (logged in `payments.mp_mode`); the API call itself
 * doesn't need it.
 */
class MercadoPagoClient {

    private const API_BASE = 'https://api.mercadopago.com';

    private string $accessToken;
    private string $mode;

    public function __construct(string $accessToken, string $mode) {
        $this->accessToken = $accessToken;
        $this->mode        = $mode === 'live' ? 'live' : 'test';
    }

    public function getMode(): string {
        return $this->mode;
    }

    /**
     * Creates a PIX payment. Returns the decoded MP response (associative
     * array). Throws MercadoPagoException on HTTP/transport errors and on
     * non-2xx responses, with the upstream payload included for debugging.
     *
     * @param array $payload         transaction_amount, description, payer, metadata, etc.
     * @param string $idempotencyKey  unique per business intent (we use "pix-<userId>-<giftId>-<unix-ms>")
     */
    public function createPixPayment(array $payload, string $idempotencyKey): array {
        return $this->request(
            'POST',
            '/v1/payments',
            $payload,
            ['X-Idempotency-Key: ' . $idempotencyKey]
        );
    }

    /**
     * Fetches the canonical state of a payment. Used by the webhook handler
     * to avoid trusting the body MP posted (which is just a hint that the
     * resource changed) — we always re-read the truth.
     */
    public function getPayment(string $paymentId): array {
        return $this->request('GET', '/v1/payments/' . urlencode($paymentId));
    }

    /**
     * Validates an MP webhook signature.
     *
     * MP sends a header `x-signature: ts=<unix>,v1=<hash>`. The hash is
     * HMAC-SHA256, key = webhook secret, message = `id:<dataId>;request-id:<x-request-id>;ts:<ts>;`
     * (note the trailing semicolon). When `data.id` is numeric, MP normalises
     * it to lowercase string — our manifest matches the documented format.
     *
     * Returns false on any parse error, missing field, or hash mismatch.
     */
    public function verifyWebhookSignature(
        string $signatureHeader,
        string $requestId,
        string $dataId,
        string $secret
    ): bool {
        if ($signatureHeader === '' || $secret === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $piece) {
            $piece = trim($piece);
            if ($piece === '') continue;
            $kv = explode('=', $piece, 2);
            if (count($kv) !== 2) continue;
            $parts[strtolower(trim($kv[0]))] = trim($kv[1]);
        }

        $ts   = $parts['ts'] ?? '';
        $hash = $parts['v1'] ?? '';
        if ($ts === '' || $hash === '') {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    // ───────────────────────────────────────────────────────────────────────
    // Internals
    // ───────────────────────────────────────────────────────────────────────

    /**
     * @throws MercadoPagoException
     */
    private function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array {
        $url = self::API_BASE . $path;
        $ch  = curl_init($url);

        $headers = array_merge([
            'Authorization: Bearer ' . $this->accessToken,
            'Accept: application/json',
            'Content-Type: application/json',
        ], $extraHeaders);

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            // SSL: verify host + peer (Hostinger ships an up-to-date CA bundle).
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
        }
        curl_setopt_array($ch, $opts);

        $rawResponse = curl_exec($ch);
        $httpStatus  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr     = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false) {
            throw new MercadoPagoException('cURL transport error: ' . $curlErr, 0);
        }

        $decoded = json_decode((string) $rawResponse, true);
        if (!is_array($decoded)) {
            // MP returned non-JSON (rare, but possible on 5xx HTML pages).
            throw new MercadoPagoException(
                'Resposta inválida do MercadoPago (HTTP ' . $httpStatus . ').',
                $httpStatus
            );
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            $msg = (string) ($decoded['message'] ?? $decoded['error'] ?? 'erro desconhecido');
            throw new MercadoPagoException(
                "MercadoPago HTTP {$httpStatus}: {$msg}",
                $httpStatus,
                $decoded
            );
        }

        return $decoded;
    }
}
