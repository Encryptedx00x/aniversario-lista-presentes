# Sistema de Aniversário — Lista de Presentes

Aplicação PHP + MySQL para lista de presentes, reservas por convidado e contribuições via Pix.

## Requisitos

- PHP 8.1+
- MySQL/MariaDB
- Extensão PDO MySQL habilitada
- Hospedagem compartilhada com `.htaccess` (Hostinger/cPanel)

## Estrutura de banco (fresh install)

Use nesta ordem:

1. `sql/schema.sql`
2. `sql/seed.sql`

> Em projetos já existentes (upgrade), aplique também os migrations incrementais conforme necessário:
> - `sql/migrate_phase2.sql`
> - `sql/migrate_phase3.sql`
> - `sql/migrate_phase5_purchase_url.sql`

## Credenciais de demonstração (seed)

- Admin:
  - E-mail: `admin@aniversario.local`
  - Senha: `admin123`
- Guest demo:
  - E-mail: `guest@aniversario.local`
  - Senha: `convidado123`

## Deploy na Hostinger (passo a passo)

1. Crie um banco MySQL no hPanel (anote host, db, usuário e senha).
2. Importe `sql/schema.sql` no phpMyAdmin.
3. Importe `sql/seed.sql`.
4. Se estiver atualizando um ambiente antigo, importe também migrations pendentes.
5. Faça upload de todos os arquivos do projeto para a hospedagem.
6. Garanta que o `Document Root` aponte para a pasta `public/` (ou mova conteúdo de `public` para `public_html` mantendo paths).
7. Copie `config.example.php` para `config.php` (ou ajuste seu arquivo real de config) com credenciais de produção.
8. Valide permissões de escrita para `storage/` e `public/uploads/gifts/`.
9. Acesse `/admin/login` e entre com o admin seeded (ou crie seu admin final).
10. Atualize chave Pix e dados do recebedor no banco (`settings`) ou via painel quando disponível.
11. Teste fluxo público:
    - `/gifts` (reservar presente),
    - `/my-gifts`,
    - `/pix` (contribuição).
12. Verifique logs PHP/servidor e desative exibição de erros em produção.

## Rotas principais

- Público:
  - `GET /gifts`
  - `POST /reserve`
  - `GET /my-gifts`
  - `POST /my-gifts/cancel`
  - `GET /pix`
  - `POST /pix/contribute`
- Admin:
  - `GET /admin/login`
  - `GET /admin/dashboard`
  - `GET /admin/gifts`

## Observações

- CSRF é obrigatório em todos os POSTs.
- Reservas usam transação e lock pessimista para evitar corrida de estoque.
- Pix contribution pode ser vinculada ao presente (`gift_id`) para marcar o presente como dado.
