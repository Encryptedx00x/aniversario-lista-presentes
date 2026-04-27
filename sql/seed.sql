-- sql/seed.sql
-- Sample data for local/Hostinger first deploy.
-- Run after sql/schema.sql

-- Admin default user (password: admin123)
INSERT INTO users (name, email, phone, password_hash, role)
VALUES ('Administrador', 'admin@aniversario.local', NULL, '$2y$12$6p1femJKdjeMOka..6gNhOQ/TS/zrGb5cCv8veeLcQ9GVNlqYB0A6', 'admin');

-- Guest sample user (password: convidado123)
INSERT INTO users (name, email, phone, password_hash, role)
VALUES ('Convidado Demo', 'guest@aniversario.local', '(11) 90000-0000', '$2y$12$WpwaatthWxQ/kboRaioPzeXw15YSMU7ZIFQwRF5/cueu4kGr0f37K', 'guest');

-- Gifts
INSERT INTO gifts
    (name, slug, description, purchase_url, category, type, price, image_path, quantity, available_qty, reserved, is_active, featured, sort_order)
VALUES
    ('Camisa Preta Tamanho G', 'camisa-preta-g', 'Camisa básica preta, tamanho G.', 'https://exemplo.com/camisa-preta-g', 'Roupas', 'single', 100.00, NULL, 1, 1, 0, 1, 1, 1),
    ('Jogo de Toalhas', 'jogo-toalhas', 'Jogo com 5 toalhas de banho.', 'https://exemplo.com/jogo-toalhas', 'Casa', 'multi', 120.00, NULL, 3, 3, 0, 1, 0, 2),
    ('Caixa de Chocolates', 'caixa-chocolates', 'Caixa premium de chocolates.', 'https://exemplo.com/chocolates', 'Doces', 'multi', 60.00, NULL, 5, 5, 0, 1, 0, 3),
    ('Tênis Casual 40', 'tenis-casual-40', 'Tênis casual cor branca, número 40.', 'https://exemplo.com/tenis-casual-40', 'Calçados', 'single', 250.00, NULL, 1, 1, 0, 1, 1, 4),
    ('Kit Café Especial', 'kit-cafe-especial', 'Kit com cafés especiais e acessórios.', 'https://exemplo.com/kit-cafe-especial', 'Gourmet', 'single', 180.00, NULL, 1, 1, 0, 1, 0, 5);

-- Pix settings
INSERT INTO settings (key_name, value) VALUES
    ('pix_key', 'chave-pix-demo@aniversario.local'),
    ('pix_receiver', 'Aniversariante Demo'),
    ('pix_city', 'São Paulo'),
    ('pix_qr_code', ''),
    ('pix_copy_paste', '00020126360014BR.GOV.BCB.PIX0114chave-pix-demo5204000053039865802BR5920Aniversariante Demo6009Sao Paulo62070503***6304ABCD');
