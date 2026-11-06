# Recuperação histórica de presentes — preparação, sem publicação automática

Estas ferramentas não são migrations carregadas pelo app. Não leem suas
credenciais implicitamente, não importam usuários e não executam o dump SQL.
Qualquer escrita em produção depende de aprovação prévia do dono para o hash
exato do pacote e para a estratégia abaixo. O modo padrão é `plan`, somente leitura.

## Escopo preparado

- 26 presentes históricos: IDs, slugs, descrições, preços decimais em string,
  categoria, referências de imagem, links, estoque e timestamps preservados.
- `purchase_url` é preservado no arquivo de proveniência. Quando houver valor
  e `redirect_url` estiver vazio, o construtor propõe o mapeamento; dois links
  diferentes interrompem a preparação. Neste export, todos os 26 valores são NULL.
- 6 reservas ficam no JSON histórico do lote, com os IDs originais de reserva,
  presente e usuário. Não são inseridas em `reservations` nem associadas a contas
  atuais. Usuários, hashes, senhas, pagamentos e configurações não são importados.
- Estoque histórico não é recalculado a partir das reservas. Divergências são
  exibidas na prévia para decisão do dono. Timestamps usam UTC, explicitado no dump.

## Preparação offline

Requisitos: Python 3.10+, PHP 8.1+ com PDO MySQL/mbstring, MariaDB/MySQL com InnoDB.
O teste real foi feito em MariaDB 11.8.8/PHP 8.3, em container de rede `none`.

```powershell
python scripts/recovery/build_package.py --dump <export-original.sql> --public-root <public-antigo> --out <pasta-privada-nova>
python -m unittest discover -s tests -p historical_package_test.py -v
```

O construtor só aceita 26 IDs distintos e export com diretiva explícita UTC.
O pacote contém `manifest.json`, `payload.json`, `assets/` e `PREVIA.html`.
Abra a prévia localmente. Não versionar o pacote, dumps, contas, recibos ou logs.
Pacote/recibos devem ficar fora do docroot e do repositório, com acesso privado.

## Aprovação e publicação futura — NÃO executadas durante a preparação

1. Aprovar por escrito o hash `payload_sha256` exibido no relatório, os quatro
   esgotados, os quatro links ausentes, o link suspeito do ID 7 e a estratégia de
   arquivar reservas sem importar contas. Qualquer edição gera novo pacote/hash
   e exige nova revisão/testes. Não calcular um hash novo e tratá-lo como aprovado.
2. Revalidar exclusividade da operação, schema, contagens e arquivos na VPS.
   Separar uma janela sem cadastro/edição/reserva de presentes durante importação
   e eventual rollback. Não reiniciar serviços de outros projetos.
3. Fazer backup dedicado de `dyogosite` (schema + dados), autenticado por arquivo
   privado de opções do cliente, nunca senha em argumento/saída. Validar gzip e
   hash. Guardar também inventário/hash dos uploads e recibo do código/pacote em
   `/srv/sites/backups/dyogo-recovery-<data>/`, modo 700. Não fazer reset de banco.
4. Transferir ferramentas e pacote aprovado para esse diretório privado. Verificar
   hash do `payload.json` e cada imagem. Não copiar o dump antigo para o docroot.
5. Provisionar as variáveis abaixo privadamente, sem imprimir seus valores.
   `RECOVERY_DSN` deve conter `charset=utf8mb4` e o nome explícito da base;
   `RECOVERY_ALLOWED_DATABASE` deve ser exatamente a base aprovada. A ferramenta
   não busca `config/database.php` nem `.env` como fallback.

```text
RECOVERY_DSN=mysql:host=<host-interno>;dbname=dyogosite;charset=utf8mb4
RECOVERY_DB_USER=<conta-provisionada-privadamente>
RECOVERY_DB_PASSWORD=<segredo-provisionado-privadamente>
RECOVERY_ALLOWED_DATABASE=dyogosite
RECOVERY_PUBLIC_ROOT=/srv/sites/dyogo.site/public
```

Os comandos a seguir usam shell Linux e caminhos das ferramentas transferidas.
`PACKAGE`, `TOOLS`, `PUBLIC_ROOT`, `RECEIPT` e `APPROVED_SHA256` são variáveis da
operação, definidas conforme o recibo aprovado; não são credenciais.

```sh
php "$TOOLS/recover.php" --package="$PACKAGE" --mode=plan
python3 "$TOOLS/assets.py" --package="$PACKAGE" --public-root="$PUBLIC_ROOT" --mode=plan
```

6. Conferir `insert=26`, `already_identical=[]`, nenhuma colisão, seis reservas
   arquivadas e zero reservas operacionais a inserir. Arquivos existentes com hash
   diferente interrompem a operação. Incorporar/revisar qualquer edição direta
   encontrada antes de continuar, sem sobrescrevê-la.
7. Preparar previamente `public/uploads/gifts` com as permissões/dono aprovados.
   O instalador não cria diretórios do app. Instalar apenas imagens ausentes;
   arquivos idênticos são preservados. O recibo deve estar fora do docroot e no
   mesmo filesystem para manter provas de propriedade por inode/hardlink.

```sh
python3 "$TOOLS/assets.py" --package="$PACKAGE" --public-root="$PUBLIC_ROOT" --receipt="$RECEIPT" --mode=apply --approved-sha256="$APPROVED_SHA256"
php "$TOOLS/recover.php" --package="$PACKAGE" --mode=init-ledger --approved-sha256="$APPROVED_SHA256"
php "$TOOLS/recover.php" --package="$PACKAGE" --mode=apply --approved-sha256="$APPROVED_SHA256"
```

O PHP precisa enxergar pacote, ferramentas e `RECOVERY_PUBLIC_ROOT`. Na VPS atual,
isso pode ser feito no PHP CLI do stack, usando o bind de `/srv/sites` e ambiente
provisionado de forma privada. Não alterar Caddy, DNS, Compose, bancos vizinhos
ou reiniciar o stack para executar essas ferramentas.

`init-ledger` cria somente `dyogo_recovery_batches` e `dyogo_recovery_rows`.
DDL fica separado porque MariaDB faz commit implícito de DDL. `apply` executa uma
transação única: presentes + arquivo histórico + recibos; usa lock de recuperação,
locks de linhas e FKs normais. Não usa `INSERT IGNORE`, REPLACE ou desativa FKs.
Colisões divergentes falham; linhas idênticas preexistentes não são substituídas.
Repetição do lote aplicado valida proveniência/recibos/linhas e não duplica dados.

8. Verificar 26 presentes, IDs 1–27 sem ID 4, seis reservas no arquivo do lote,
   nenhuma conta/pagamento/reserva operacional nova, preços e estoque iguais ao
   pacote, 26 imagens legíveis, HTTP/visual de `/gifts`, filtros e logs. Fazer prints
   em 1280×800, 1440×900 e 390×844. Registrar backup, hash e resultado. Não publicar
   uma lista fictícia para substituir dados faltantes.

## Rollback futuro

```sh
php "$TOOLS/recover.php" --package="$PACKAGE" --mode=rollback --approved-sha256="$APPROVED_SHA256"
php "$TOOLS/recover.php" --package="$PACKAGE" --mode=plan
```

O rollback apaga somente linhas inseridas pelo lote, validando hash de cada uma.
Recusa presentes alterados, recibos incompletos e qualquer relacionamento novo
por FK, inclusive reserva/pagamento. Preserva linhas idênticas que já existiam,
outros presentes e o arquivo de auditoria. Não reverte AUTO_INCREMENT nem restaura
um dump integral. Um lote revertido não é reaplicado sem nova revisão operacional.

**Antes de remover imagens:** confirmar que o rollback do banco terminou e que
`image_references=0` no plano posterior. Esse contador considera todos os presentes,
inclusive registros posteriores que reutilizem uma imagem. Se for maior que zero,
preservar imagens e resolver as referências com o dono. Manter a janela sem escrita
para não criar referências entre a consulta e a remoção. Só então:

```sh
python3 "$TOOLS/assets.py" --package="$PACKAGE" --public-root="$PUBLIC_ROOT" --receipt="$RECEIPT" --mode=rollback --approved-sha256="$APPROVED_SHA256" --database-rollback-confirmed="$APPROVED_SHA256"
```

O instalador confere inode e hash; não remove arquivos preexistentes ou substituídos.
Recibo/arquivos de prova permanecem privados. Se houver lock abandonado, primeiro
comprovar que seu processo terminou; não apagar lock nem reiniciar por timeout.
Se o rollback for recusado, parar e relatar; não forçar DELETE, reset ou restauração.

## Testes

`tests/historical_recovery_test.php` só aceita `dyogo_recovery_test` e requer
`RECOVERY_TEST_DSN`, `RECOVERY_TEST_USER`, `RECOVERY_TEST_PASSWORD` e
`RECOVERY_TEST_PACKAGE`. Ele recria exclusivamente tabelas dessa base de teste.
Executar somente no ambiente isolado, jamais com a configuração da aplicação.

```sh
php tests/historical_recovery_test.php
```

Valida importação fiel, repetição e concorrência, UTC/acentos/preços, conflitos,
arquivo das reservas, proteção de alterações/vínculos novos e rollback atômico.
Testes Python cobrem parser sem execução de SQL, links, escape HTML, integridade,
imagens, idempotência e rollback de arquivos. Falhas legadas do app devem ser
registradas separadamente; não confundir estes testes com uma suíte legada verde.
