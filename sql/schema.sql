-- sql/schema.sql
-- Canonical schema for the Birthday Wishlist app.
--
-- This file represents the CURRENT desired shape of the database (after all
-- phase migrations). Use it for fresh installs. For an existing deployment,
-- apply the migrate_phaseN.sql files in order instead.
--
-- Compatible with MySQL 5.7 and 8.0. Admin/sample seed data lives in seed.sql.

-- ─── Users ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)     NOT NULL,
    email         VARCHAR(255)     NOT NULL UNIQUE,
    phone         VARCHAR(30)      DEFAULT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    role          ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Gifts ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS gifts (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255)     NOT NULL,
    slug          VARCHAR(255)     NOT NULL DEFAULT '',
    description   TEXT,
    category      VARCHAR(100)     DEFAULT NULL,
    type          ENUM('single','multi') NOT NULL DEFAULT 'single',
    price         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    image_path    VARCHAR(500),
    redirect_url  VARCHAR(500)     DEFAULT NULL,
    quantity      INT UNSIGNED     NOT NULL DEFAULT 1,
    available_qty INT UNSIGNED     NOT NULL DEFAULT 0,
    reserved      INT UNSIGNED     NOT NULL DEFAULT 0,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    featured      TINYINT(1)       NOT NULL DEFAULT 0,
    sort_order    INT UNSIGNED     NOT NULL DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_gifts_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Reservations ───────────────────────────────────────────────────────────
-- One row per "Quero esse" (action_type=reserved) or "PIX confirmado"
-- (action_type=pix) action by an authenticated guest.
CREATE TABLE IF NOT EXISTS reservations (
    id                  INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    gift_id             INT UNSIGNED     NOT NULL,
    user_id             INT UNSIGNED     NOT NULL,
    status              ENUM('active','cancelled') NOT NULL DEFAULT 'active',
    action_type         ENUM('reserved','pix')     NOT NULL DEFAULT 'reserved',
    quantity            INT UNSIGNED     NOT NULL DEFAULT 1,
    purchase_confirmed  TINYINT(1)       NULL,
    created_at          TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_res_gift FOREIGN KEY (gift_id)
        REFERENCES gifts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_res_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_res_user_status_created (user_id, status, created_at),
    INDEX idx_res_gift_status         (gift_id, status),
    INDEX idx_res_action_status       (action_type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Settings ───────────────────────────────────────────────────────────────
-- Generic key/value bag for runtime-configurable values (event date, PIX, ...).
CREATE TABLE IF NOT EXISTS settings (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    key_name      VARCHAR(100)     NOT NULL UNIQUE,
    value         TEXT,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Payments ──────────────────────────────────────────────────────────────
-- One row per MercadoPago PIX payment attempt. When MP confirms a payment
-- via webhook, we link the row to the reservation that was created for the
-- contribution (action_type='pix') so the admin and the user can audit it.
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

-- ─── Seed: default settings ─────────────────────────────────────────────────
-- Safe to re-run: ON DUPLICATE KEY only refreshes when a row is inserted blank.
INSERT INTO settings (key_name, value) VALUES
    ('event_person_name',      'Maria'),
    ('event_age',              '30'),
    ('event_date',             '2026-05-20'),
    ('event_location',         'São Paulo'),
    ('pix_name',               ''),
    ('pix_key',                ''),
    ('pix_bank',               ''),
    ('pix_city',               ''),
    ('pix_code',               ''),
    ('mp_mode',                'test'),
    ('mp_access_token_test',   ''),
    ('mp_access_token_live',   ''),
    ('mp_webhook_secret_test', ''),
    ('mp_webhook_secret_live', ''),
    ('mp_public_url',          '')
ON DUPLICATE KEY UPDATE value = settings.value;
