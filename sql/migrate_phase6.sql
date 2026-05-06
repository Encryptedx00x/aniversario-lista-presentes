-- migrate_phase6.sql
-- Phase 6: multi-quantity reservations + purchase confirmation flow.
--
-- Two new columns on `reservations`:
--   quantity            — quantos slots/itens daquele gift o convidado
--                         pegou. Default 1 mantém compat com reservations
--                         antigas.
--   purchase_confirmed  — NULL  → ainda não respondeu (presente
--                                 redirecionado, esperando feedback)
--                         1     → marcou "já comprei"
--                         0     → marcou "não comprei" (caminho alternativo
--                                 ao cancelamento explícito).
--
-- Safe re-run: ADD COLUMN IF NOT EXISTS exige MariaDB 10.0.2+ / MySQL 8 —
-- usamos ALTER TABLE ... ADD COLUMN protegido por information_schema.

SET @col_qty := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'reservations'
      AND COLUMN_NAME  = 'quantity'
);
SET @sql_qty := IF(@col_qty = 0,
    'ALTER TABLE reservations ADD COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1 AFTER action_type',
    'SELECT 1');
PREPARE stmt FROM @sql_qty; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_pc := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'reservations'
      AND COLUMN_NAME  = 'purchase_confirmed'
);
SET @sql_pc := IF(@col_pc = 0,
    'ALTER TABLE reservations ADD COLUMN purchase_confirmed TINYINT(1) NULL AFTER quantity',
    'SELECT 1');
PREPARE stmt FROM @sql_pc; EXECUTE stmt; DEALLOCATE PREPARE stmt;
