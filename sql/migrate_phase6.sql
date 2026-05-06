-- migrate_phase6.sql
-- Phase 6: multi-quantity reservations + purchase confirmation flow.
--
-- Duas colunas novas em `reservations`:
--   quantity            — quantos slots/itens daquele gift o convidado pegou.
--                         Default 1 mantém compat com reservations antigas.
--   purchase_confirmed  — NULL  → ainda não respondeu (presente com link
--                                 da loja, esperando feedback)
--                         1     → "já comprei" (registra a compra externa)
--                         0     → "não comprei" (libera o estoque)
--
-- Como aplicar no Hostinger:
--   phpMyAdmin → escolhe o banco do site → aba SQL → cola TUDO abaixo
--   → executa.
--
-- Se já tiver rodado antes, MySQL devolve "Duplicate column name" — só
-- ignora o erro e segue, o estado fica correto. Pra checar manualmente:
--   SHOW COLUMNS FROM reservations LIKE 'quantity';
--   SHOW COLUMNS FROM reservations LIKE 'purchase_confirmed';

ALTER TABLE reservations
    ADD COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1
    AFTER action_type;

ALTER TABLE reservations
    ADD COLUMN purchase_confirmed TINYINT(1) NULL
    AFTER quantity;
