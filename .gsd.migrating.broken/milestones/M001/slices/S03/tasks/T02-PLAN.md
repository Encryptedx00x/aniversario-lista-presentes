# T02: 03-reserva-atomica-meus-presentes 02

**Slice:** S03 — **Milestone:** M001

## Description

Wire the frontend reservation flow: implement the Phase-3 branch in initChooseGift, add _fetchFreshCsrfToken before the re-click in _onAuthSuccess, implement _onReserveSuccess (toast + card update) and _onReserveError (card exhausted), and add the Bootstrap Toast HTML to the gifts page.

Purpose: This is the user-facing side of the reservation system. Plan 01 delivers the backend; this plan connects the existing JS stubs to it. The stale CSRF token fix (_fetchFreshCsrfToken) is critical — without it every first-time reservation after modal login returns 403.

Output: app.js Phase-3 branch fully wired; Toast HTML in gifts/index.php.

## Must-Haves

- [ ] "Clicking 'Escolher este presente' when loggedIn==='1' POSTs to /reserve (not console.log)"
- [ ] "After successful reservation, Bootstrap Toast appears with 'Ver meus presentes' link"
- [ ] "On success, card's available qty counter decrements in-DOM without page reload"
- [ ] "If gift is exhausted (is_exhausted=true), card button replaced with disabled 'Indisponível'"
- [ ] "On race-condition error from /reserve, card updates to exhausted state and toast shows error message"
- [ ] "_onAuthSuccess fetches /csrf-token before re-clicking button to avoid 403"

## Files

- `public/assets/js/app.js`
- `app/Views/public/gifts/index.php`
