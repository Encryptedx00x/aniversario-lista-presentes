# Requirements

This file is the explicit capability and coverage contract for the project.

## Validated

### AUTH-04 — Untitled
- Status: validated
- Validation: AdminAuthController provides login form/post flow with UserModel lookup, password_verify, role check, flash handling, and dashboard route integration (S01 T03/T04).

### AUTH-05 — Untitled
- Status: validated
- Validation: AuthMiddleware enforces ($_SESSION['role'] ?? '') === 'admin', and Router applies it to auth-tagged admin routes (/admin/dashboard, /admin/logout, gifts stubs) (S01 T01/T02).

### DEPLOY-01 — Untitled
- Status: validated
- Validation: sql/schema.sql now contains complete 5-table schema (users, gifts, reservations, contributions, settings) with IF NOT EXISTS and InnoDB/utf8mb4, import-ready for cPanel (S01 T04).

### DEPLOY-03 — Untitled
- Status: validated
- Validation: config.example.php committed with credential placeholders (e.g., YOUR_DATABASE_NAME) for environment setup (S01 T01).

### DEPLOY-04 — Untitled
- Status: validated
- Validation: public/.htaccess routes non-file/non-directory requests to public/index.php via RewriteRule, enabling front controller routing (S01 T01).

### SEC-01 — Untitled
- Status: validated
- Validation: BaseModel forces PDO prepare/execute path with ATTR_EMULATE_PREPARES=false and ERRMODE_EXCEPTION (S01 T02).

### SEC-02 — Untitled
- Status: validated
- Validation: CsrfMiddleware generates random_bytes token and validates via hash_equals; Router enforces CSRF on POST; login/logout forms include csrf_token (S01 T02/T04).

### SEC-03 — Untitled
- Status: validated
- Validation: View helper injects htmlspecialchars ENT_QUOTES UTF-8 escape closure and templates use escaped output (S01 T02/T04).

### SEC-04 — Untitled
- Status: validated
- Validation: AdminAuthController login verifies password_hash via password_verify() and regenerates session ID on successful auth (S01 T03).

### SEC-05 — Untitled
- Status: validated
- Validation: Upload helper validates MIME using finfo(FILEINFO_MIME_TYPE) and extension whitelist contract for secure uploads (S01 T03 scaffold).

### SEC-07 — Untitled
- Status: validated
- Validation: config/app.php defaults to display_errors=0 in production and 500 error view is generic without internal stack details (S01 T01/T04).

## Traceability

| ID | Class | Status | Primary owner | Supporting | Proof |
|---|---|---|---|---|---|
| AUTH-04 |  | validated | none | none | AdminAuthController provides login form/post flow with UserModel lookup, password_verify, role check, flash handling, and dashboard route integration (S01 T03/T04). |
| AUTH-05 |  | validated | none | none | AuthMiddleware enforces ($_SESSION['role'] ?? '') === 'admin', and Router applies it to auth-tagged admin routes (/admin/dashboard, /admin/logout, gifts stubs) (S01 T01/T02). |
| DEPLOY-01 |  | validated | none | none | sql/schema.sql now contains complete 5-table schema (users, gifts, reservations, contributions, settings) with IF NOT EXISTS and InnoDB/utf8mb4, import-ready for cPanel (S01 T04). |
| DEPLOY-03 |  | validated | none | none | config.example.php committed with credential placeholders (e.g., YOUR_DATABASE_NAME) for environment setup (S01 T01). |
| DEPLOY-04 |  | validated | none | none | public/.htaccess routes non-file/non-directory requests to public/index.php via RewriteRule, enabling front controller routing (S01 T01). |
| SEC-01 |  | validated | none | none | BaseModel forces PDO prepare/execute path with ATTR_EMULATE_PREPARES=false and ERRMODE_EXCEPTION (S01 T02). |
| SEC-02 |  | validated | none | none | CsrfMiddleware generates random_bytes token and validates via hash_equals; Router enforces CSRF on POST; login/logout forms include csrf_token (S01 T02/T04). |
| SEC-03 |  | validated | none | none | View helper injects htmlspecialchars ENT_QUOTES UTF-8 escape closure and templates use escaped output (S01 T02/T04). |
| SEC-04 |  | validated | none | none | AdminAuthController login verifies password_hash via password_verify() and regenerates session ID on successful auth (S01 T03). |
| SEC-05 |  | validated | none | none | Upload helper validates MIME using finfo(FILEINFO_MIME_TYPE) and extension whitelist contract for secure uploads (S01 T03 scaffold). |
| SEC-07 |  | validated | none | none | config/app.php defaults to display_errors=0 in production and 500 error view is generic without internal stack details (S01 T01/T04). |

## Coverage Summary

- Active requirements: 0
- Mapped to slices: 0
- Validated: 11 (AUTH-04, AUTH-05, DEPLOY-01, DEPLOY-03, DEPLOY-04, SEC-01, SEC-02, SEC-03, SEC-04, SEC-05, SEC-07)
- Unmapped active requirements: 0
