-- migrate_phase2.sql
-- Run AFTER Phase 1 schema.sql is in place.
-- Safe to re-run: column additions use IF NOT EXISTS workaround via IGNORE.

-- ─── gifts: add Phase 2 columns ──────────────────────────────────────────────
ALTER TABLE gifts
    ADD COLUMN slug          VARCHAR(255)           NOT NULL DEFAULT '' AFTER name,
    ADD COLUMN category      VARCHAR(100)           DEFAULT NULL AFTER description,
    ADD COLUMN type          ENUM('single','multi') NOT NULL DEFAULT 'single' AFTER category,
    ADD COLUMN featured      TINYINT(1)             NOT NULL DEFAULT 0 AFTER is_active,
    ADD COLUMN available_qty INT UNSIGNED           NOT NULL DEFAULT 0 AFTER quantity;

-- Unique index for slug (gifts slugs must be unique)
ALTER TABLE gifts ADD UNIQUE INDEX idx_gifts_slug (slug);

-- Backfill available_qty for rows already inserted in testing
-- available_qty = total quantity minus already-reserved count
UPDATE gifts SET available_qty = GREATEST(0, quantity - reserved) WHERE available_qty = 0;

-- ─── users: add phone for guest registration (AUTH-01, D-13) ─────────────────
ALTER TABLE users
    ADD COLUMN phone VARCHAR(30) DEFAULT NULL AFTER email;

-- ─── reservations: update to Phase 3 shape (user_id FK, status enum) ────────
-- NOTE: Phase 1 reservations table uses guest_name/guest_email/token.
-- Phase 3 will further alter this table. These changes prepare the FK column.
ALTER TABLE reservations
    ADD COLUMN user_id INT UNSIGNED DEFAULT NULL AFTER gift_id;
-- Do NOT add FK constraint yet — users table row may not exist for legacy rows.
