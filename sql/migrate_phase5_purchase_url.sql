-- migrate_phase5_purchase_url.sql
-- Adds optional purchase URL field so guests can open the exact product page.
ALTER TABLE gifts
    ADD COLUMN purchase_url VARCHAR(500) DEFAULT NULL AFTER description;
