# Sistema de Aniversário — Lista de Presentes

## Project Context

Sistema de lista de presentes para aniversário. PHP 8.1+, MySQL, PDO, Bootstrap 5 via CDN. Deploy em Hostinger shared hosting. Sem Node.js, sem build step em produção.

## GSD Workflow

Este projeto usa GSD (Get Shit Done) para planejamento e execução estruturada.

- **Planejar fase:** `/gsd-plan-phase <N>`
- **Executar fase:** `/gsd-execute-phase <N>`
- **Ver progresso:** `/gsd-progress`
- **Retomar sessão:** `/gsd-resume-work`

## Architecture Principles

- MVC leve customizado (sem Composer, sem framework externo)
- Front controller: `public/index.php` + `.htaccess` com mod_rewrite
- PDO com prepared statements em TODAS as queries
- CSRF token em TODOS os formulários POST
- Escape de output com `htmlspecialchars()` em TODA saída HTML
- Sessões PHP nativas para autenticação
- Transações com SELECT FOR UPDATE para baixa de estoque

## Directory Structure

```
/app/Controllers/   — Lógica de negócio e routing
/app/Models/        — Acesso ao banco (PDO)
/app/Views/         — Templates PHP
/app/Middleware/    — Auth, CSRF
/app/Helpers/       — Funções utilitárias
/config/            — Configurações (DB, app)
/public/            — Document root (index.php, .htaccess, assets)
/sql/               — Schema e seeds SQL
/storage/           — Logs, temp
```

## Security Requirements (Non-Negotiable)

1. PDO prepared statements — nunca interpolação de variáveis em SQL
2. CSRF token em formulários — validar no backend antes de processar
3. `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` antes de qualquer echo de dado do usuário
4. `password_hash($pass, PASSWORD_DEFAULT)` para armazenar, `password_verify()` para checar
5. Upload: validar MIME real (finfo), extensão whitelist, tamanho máximo, renomear arquivo
6. Admin routes: verificar `$_SESSION['role'] === 'admin'` no middleware

## Coding Standards

- PHP com strict types onde aplicável
- Classes com namespace `App\Controllers`, `App\Models`, etc.
- Métodos pequenos e com responsabilidade única
- Sem lógica de banco em Views
- Bootstrap 5 para layout — sem CSS frameworks adicionais
- JavaScript vanilla ou jQuery mínimo (sem bundler)
