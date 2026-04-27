-- sql/schema.sql
-- Consolidated schema (Phase 6 ready for fresh deploys on Hostinger)
-- Compatible with MySQL 5.7+ / MariaDB 10.4+

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
    slug          VARCHAR(255)     NOT NULL,
    description   TEXT,
    purchase_url  VARCHAR(500)     DEFAULT NULL,
    category      VARCHAR(100)     DEFAULT NULL,
    type          ENUM('single','multi') NOT NULL DEFAULT 'single',
    price         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    image_path    VARCHAR(500),
    quantity      INT UNSIGNED     NOT NULL DEFAULT 1,
    available_qty INT UNSIGNED     NOT NULL DEFAULT 0,
    reserved      INT UNSIGNED     NOT NULL DEFAULT 0,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    featured      TINYINT(1)       NOT NULL DEFAULT 0,
    sort_order    INT UNSIGNED     NOT NULL DEFAULT 0,
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_gifts_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Reservations ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    gift_id       INT UNSIGNED     NOT NULL,
    user_id       INT UNSIGNED     DEFAULT NULL,
    guest_name    VARCHAR(120)     DEFAULT NULL,
    guest_email   VARCHAR(255)     DEFAULT NULL,
    token         VARCHAR(64)      DEFAULT NULL,
    status        ENUM('reserved','confirmed','cancelled') NOT NULL DEFAULT 'reserved',
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_res_gift FOREIGN KEY (gift_id)
        REFERENCES gifts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_res_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Contributions (Pix) ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contributions (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    guest_name    VARCHAR(120)     NOT NULL,
    guest_email   VARCHAR(255)     NOT NULL,
    guest_phone   VARCHAR(30)      DEFAULT NULL,
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
