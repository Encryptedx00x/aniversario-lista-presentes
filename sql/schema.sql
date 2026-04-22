-- sql/schema.sql
-- Phase 1: users table. Compatible with MySQL 5.7 and 8.0.
-- All other tables (gifts, reservations, contributions, settings) are added in Phases 2-5.

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
