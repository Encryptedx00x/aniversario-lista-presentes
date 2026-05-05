<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Thrown by MercadoPagoClient when the API returns a non-2xx response or
 * when the cURL transport fails. The decoded MP payload (when available)
 * is exposed via getResponse() so the controller can log it for debugging.
 */
class MercadoPagoException extends \RuntimeException {
    private ?array $response;

    public function __construct(string $message, int $code = 0, ?array $response = null) {
        parent::__construct($message, $code);
        $this->response = $response;
    }

    public function getResponse(): ?array {
        return $this->response;
    }
}
