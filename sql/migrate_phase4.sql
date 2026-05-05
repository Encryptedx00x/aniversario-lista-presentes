-- migrate_phase4.sql
-- Phase 4: gift ownership states, PIX contributions and dynamic event settings.
-- Safe incremental migration for existing data.

ALTER TABLE gifts
    ADD COLUMN redirect_url VARCHAR(500) NULL AFTER image_path;

ALTER TABLE reservations
    ADD COLUMN action_type ENUM('reserved', 'pix') NOT NULL DEFAULT 'reserved' AFTER status;

ALTER TABLE reservations
    ADD INDEX idx_res_action_status (action_type, status);

-- Default settings — event metadata + PIX placeholders.
-- ON DUPLICATE preserves existing values so re-running this migration does
-- NOT overwrite settings the admin already customized via /admin/settings or
-- /admin/pix.
INSERT INTO settings (key_name, value) VALUES
    ('event_person_name', 'Maria'),
    ('event_age',         '30'),
    ('event_date',        '2026-05-20'),
    ('event_location',    'São Paulo'),
    ('pix_name',          ''),
    ('pix_key',           ''),
    ('pix_bank',          ''),
    ('pix_city',          ''),
    ('pix_code',          '')
ON DUPLICATE KEY UPDATE value = settings.value;
