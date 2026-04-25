-- migrate_phase3.sql
-- Phase 3: atomic reservation flow for authenticated guests.
-- Apply after schema.sql + migrate_phase2.sql.

-- Clean up legacy reservation rows that do not belong to a guest user.
DELETE FROM reservations WHERE user_id IS NULL;

-- Normalize reservation shape (legacy guest_name/guest_email/token are no longer used).
ALTER TABLE reservations
    DROP COLUMN guest_name,
    DROP COLUMN guest_email,
    DROP COLUMN token;

-- Enforce ownership via users FK.
ALTER TABLE reservations
    MODIFY COLUMN user_id INT UNSIGNED NOT NULL;

ALTER TABLE reservations
    ADD CONSTRAINT fk_res_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- Query performance for "Meus Presentes" and stock checks.
ALTER TABLE reservations
    ADD INDEX idx_res_user_status_created (user_id, status, created_at),
    ADD INDEX idx_res_gift_status (gift_id, status);

-- Reconcile denormalized counters from active reservations.
UPDATE gifts g
LEFT JOIN (
    SELECT gift_id, COUNT(*) AS active_count
    FROM reservations
    WHERE status = 'active'
    GROUP BY gift_id
) r ON r.gift_id = g.id
SET g.reserved = COALESCE(r.active_count, 0),
    g.available_qty = GREATEST(0, g.quantity - COALESCE(r.active_count, 0));
