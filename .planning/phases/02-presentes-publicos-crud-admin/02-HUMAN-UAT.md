---
status: partial
phase: 02-presentes-publicos-crud-admin
source: [02-VERIFICATION.md]
started: 2026-04-22T00:00:00Z
updated: 2026-04-22T00:00:00Z
---

## Current Test

[awaiting human testing]

## Tests

### 1. Card grid renders at /
expected: Active gifts display as Bootstrap cards (3 cols desktop, 2 tablet, 1 mobile) with image/placeholder, name, category badge, price, available qty, status badge, and Escolher/Indisponível button
result: [pending]

### 2. Filter bar AJAX behavior
expected: Typing in search debounces 300ms then fetches /gifts/filter and swaps #gifts-container innerHTML; category and sort dropdowns trigger immediately; loading opacity applied during fetch
result: [pending]

### 3. Auth modal signup success flow
expected: Valid signup → POST /auth/guest-signup → {success:true} → modal closes after 1200ms → page reflects logged-in state
result: [pending]

### 4. Auth modal login error display
expected: Wrong credentials → error string set via textContent (not innerHTML) in #login-error; no XSS vector
result: [pending]

### 5. Admin gift create with image upload
expected: Filling the form and uploading an image creates a gift row in DB; image saved to public/uploads/gifts/ with uniqid filename; redirects to list with flash
result: [pending]

### 6. Upload security rejection
expected: File over 2MB → rejected; non-whitelisted MIME (e.g. text/php) → rejected; non-whitelisted extension → rejected; all show flash error
result: [pending]

### 7. Delete guard with reservation
expected: Admin tries to delete a gift that has a reservation row → flash error shown, gift NOT deleted
result: [pending]

### 8. Inactive gift absent from public listing
expected: Gift with is_active=0 does not appear on GET / or GET /gifts/filter
result: [pending]

## Summary

total: 8
passed: 0
issues: 0
pending: 8
skipped: 0
blocked: 0

## Gaps
