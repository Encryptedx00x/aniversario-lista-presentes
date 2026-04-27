-- migrate_phase4.sql
-- Phase 4: gift ownership states, PIX contributions and dynamic event settings.
-- Safe incremental migration for existing data.

ALTER TABLE gifts
    ADD COLUMN redirect_url VARCHAR(500) NULL AFTER image_path;

ALTER TABLE reservations
    ADD COLUMN action_type ENUM('reserved', 'pix') NOT NULL DEFAULT 'reserved' AFTER status;

ALTER TABLE reservations
    ADD INDEX idx_res_action_status (action_type, status);

INSERT INTO settings (key_name, value) VALUES
    ('event_person_name', 'Maria'),
    ('event_age', '30'),
    ('event_date', '2026-06-12'),
    ('event_location', 'São Paulo')
ON DUPLICATE KEY UPDATE value = VALUES(value);
