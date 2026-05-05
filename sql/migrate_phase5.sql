-- migrate_phase5.sql
-- Phase 5: MercadoPago PIX integration.
-- Adds the `payments` table to track MP cobranças end-to-end and 6 new
-- settings keys for storing MP credentials (sandbox + live) plus the
-- public site URL used to build webhook callback URLs.
--
-- Safe re-run: the CREATE TABLE uses IF NOT EXISTS, and the settings
-- inserts use ON DUPLICATE KEY UPDATE that preserves existing values.

CREATE TABLE IF NOT EXISTS payments (
    id              INT UNSIGNED        AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL,
    gift_id         INT UNSIGNED        NOT NULL,
    reservation_id  INT UNSIGNED        NULL,
    amount          DECIMAL(10,2)       NOT NULL,
    mp_payment_id   VARCHAR(64)         NOT NULL UNIQUE,
    mp_status       VARCHAR(40)         NOT NULL DEFAULT 'pending',
    mp_qr_code      TEXT                NULL,
    mp_qr_code_b64  MEDIUMTEXT          NULL,
    mp_mode         ENUM('test','live') NOT NULL,
    expires_at      DATETIME            NULL,
    raw_response    JSON                NULL,
    created_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pay_user        FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
    CONSTRAINT fk_pay_gift        FOREIGN KEY (gift_id)        REFERENCES gifts(id)        ON DELETE CASCADE,
    CONSTRAINT fk_pay_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    INDEX idx_payments_status   (mp_status, created_at),
    INDEX idx_payments_user     (user_id, created_at),
    INDEX idx_payments_pending  (user_id, gift_id, mp_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MercadoPago credentials + public URL.
INSERT INTO settings (key_name, value) VALUES
    ('mp_mode',                'test'),
    ('mp_access_token_test',   ''),
    ('mp_access_token_live',   ''),
    ('mp_webhook_secret_test', ''),
    ('mp_webhook_secret_live', ''),
    ('mp_public_url',          '')
ON DUPLICATE KEY UPDATE value = settings.value;
