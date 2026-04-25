# Decisions

<!-- Append-only register of architectural and pattern decisions -->

| ID | Decision | Rationale | Date |
|----|----------|-----------|------|
| D001 | Router entrega `$params` para todas as actions e controllers aceitam `array $params = []` por padrão | Suporta rotas dinâmicas sem quebrar actions já existentes sem parâmetros | 2026-04-22 |
| D002 | Segurança de upload reforçada com regra dedicada em `public/uploads/.htaccess` e diretório `public/uploads/gifts/` versionado via `.gitkeep` | Evita execução indevida e garante estrutura mínima para upload seguro | 2026-04-22 |
| D003 | `public/uploads/**` permanece ignorado no Git, com exceções explícitas para arquivos de controle (`.gitkeep`, `.htaccess`) | Mantém repositório limpo sem perder estrutura e proteções necessárias no deploy | 2026-04-22 |
| D004 | Arquitetura-alvo permanece PHP 8.1 + MySQL + PDO + Bootstrap CDN, sem framework e sem build step | Compatibilidade direta com Hostinger shared hosting e menor complexidade operacional | 2026-04-21 |
