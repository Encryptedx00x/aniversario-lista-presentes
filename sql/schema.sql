-- sql/schema.sql
-- Phase 1: complete schema — all tables created here to avoid ALTER TABLE in later phases (D-11).
-- Compatible with MySQL 5.7 and 8.0.
-- Admin seed data is in sql/seed.sql (D-12) — not here.

-- ─── Users ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)     NOT NULL,
    email         VARCHAR(255)     NOT NULL UNIQUE,
    password_hash VARCHAR(255)     NOT NULL,
    role          ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Gifts ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS gifts (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255)     NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    image_path    VARCHAR(500),
    quantity      INT UNSIGNED     NOT NULL DEFAULT 1,
    reserved      INT UNSIGNED     NOT NULL DEFAULT 0,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    sort_order    INT UNSIGNED     NOT NULL DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Reservations ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    gift_id       INT UNSIGNED     NOT NULL,
    guest_name    VARCHAR(120)     NOT NULL,
    guest_email   VARCHAR(255)     NOT NULL,
    token         VARCHAR(64)      NOT NULL UNIQUE,
    status        ENUM('active','cancelled') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_res_gift FOREIGN KEY (gift_id)
        REFERENCES gifts (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Contributions (Pix) ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contributions (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    guest_name    VARCHAR(120)     NOT NULL,
    guest_email   VARCHAR(255)     NOT NULL,
    amount        DECIMAL(10,2)    NOT NULL,
    message       TEXT,
    confirmed     TINYINT(1)       NOT NULL DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Settings ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    key_name      VARCHAR(100)     NOT NULL UNIQUE,
    value         TEXT,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
