---
phase: 1
slug: base-infraestrutura
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-04-21
---

# Phase 1 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | None — manual browser testing + PHP built-in server |
| **Config file** | none — Phase 1 success criteria are browser-verifiable |
| **Quick run command** | `php -S localhost:8000 -t public` |
| **Full suite command** | Manual checklist (see Success Criteria in ROADMAP.md) |
| **Estimated runtime** | ~5 minutes manual |

---

## Sampling Rate

- **After every task commit:** Run `php -S localhost:8000 -t public` — manually hit `/` and `/admin/login`
- **After every plan wave:** Run all 5 success criteria from ROADMAP.md manually
- **Before `/gsd-verify-work`:** All 5 success criteria pass
- **Max feedback latency:** ~5 minutes

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Threat Ref | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|------------|-----------------|-----------|-------------------|-------------|--------|
| 1-01-01 | 01 | 1 | SEC-01 | SQL injection | PDO prepare/execute on all queries | code review | `grep -r "prepare(" app/Models/` | ❌ W0 | ⬜ pending |
| 1-01-02 | 01 | 1 | SEC-02 | CSRF | CSRF token validated on POST | manual + smoke | `curl -X POST /admin/login` (no token) → 403 | ❌ W0 | ⬜ pending |
| 1-01-03 | 01 | 1 | SEC-03 | XSS | htmlspecialchars on all output | code review | `grep -r "echo \$" app/Views/` | ❌ W0 | ⬜ pending |
| 1-01-04 | 01 | 1 | SEC-04 | Password exposure | password_hash for storage | code review | `grep "password_hash" app/Controllers/` | ❌ W0 | ⬜ pending |
| 1-01-05 | 01 | 1 | SEC-05 | Path traversal | Upload helper scaffold with validation | code review | `test -f app/Helpers/UploadHelper.php` | ❌ W0 | ⬜ pending |
| 1-01-06 | 01 | 1 | SEC-06 | Input validation | Backend validation on all endpoints | manual | Submit empty login form → error shown | ❌ W0 | ⬜ pending |
| 1-01-07 | 01 | 1 | SEC-07 | Info leakage | Errors not exposed to browser | manual | Trigger DB error → no stack trace visible | ❌ W0 | ⬜ pending |
| 1-01-08 | 01 | 1 | DEPLOY-03 | N/A | config.example.php exists with placeholders | file check | `test -f config.example.php` | ❌ W0 | ⬜ pending |
| 1-01-09 | 01 | 1 | DEPLOY-04 | N/A | .htaccess routes all requests to index.php | manual | `curl localhost:8000/admin/login` → 200 | ❌ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- All test infrastructure is manual for Phase 1 (no automated test framework)
- Automated grep checks are built into task acceptance_criteria, not a separate framework
- [ ] Verify PHP built-in server starts cleanly: `php -S localhost:8000 -t public`
- [ ] Verify `.htaccess` routing with a curl smoke test

*All phase success criteria are verified manually via browser + curl.*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| `php -S localhost:8000 -t public` starts without errors | DEPLOY-04 | Server startup is environmental | Run command, check for no PHP errors |
| Rota inválida retorna 404 amigável | SEC-07 | Browser rendering check | Navigate to `/nonexistent` — see custom 404 page |
| Admin login redireciona para dashboard | AUTH-04 | Session state after redirect | Login at `/admin/login` → lands on `/admin` |
| Acesso sem login redireciona para `/admin/login` | SEC-05 | Session middleware check | Navigate to `/admin/gifts` unauthenticated → redirect |
| Formulário sem CSRF token retorna 403 | SEC-02 | HTTP response code check | `curl -X POST /admin/login -d "email=a&password=b"` → 403 |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 5 minutes
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
