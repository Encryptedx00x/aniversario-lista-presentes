# S03: Reserva Atomica Meus Presentes

**Goal:** Create the complete backend for Phase 3: schema migration, ReservationModel with transactional reserve/cancel, ReservationController with store/myGifts/cancel/csrfToken actions, a new requireGuest() middleware method, and new routes.
**Demo:** Create the complete backend for Phase 3: schema migration, ReservationModel with transactional reserve/cancel, ReservationController with store/myGifts/cancel/csrfToken actions, a new requireGuest() middleware method, and new routes.

## Must-Haves


## Tasks

- [ ] **T01: 03-reserva-atomica-meus-presentes 01**
  - Create the complete backend for Phase 3: schema migration, ReservationModel with transactional reserve/cancel, ReservationController with store/myGifts/cancel/csrfToken actions, a new requireGuest() middleware method, and new routes.

Purpose: The atomic reservation engine is the core deliverable of this phase. The SELECT FOR UPDATE transaction pattern prevents concurrent over-reservation. The /csrf-token endpoint solves the stale-token pitfall that would cause 403 on every first-time reservation after modal login.

Output: 5 files created/modified. Backend fully functional; view layer (Plan 03) and JS wiring (Plan 02) depend on these contracts.
- [ ] **T02: 03-reserva-atomica-meus-presentes 02**
  - Wire the frontend reservation flow: implement the Phase-3 branch in initChooseGift, add _fetchFreshCsrfToken before the re-click in _onAuthSuccess, implement _onReserveSuccess (toast + card update) and _onReserveError (card exhausted), and add the Bootstrap Toast HTML to the gifts page.

Purpose: This is the user-facing side of the reservation system. Plan 01 delivers the backend; this plan connects the existing JS stubs to it. The stale CSRF token fix (_fetchFreshCsrfToken) is critical — without it every first-time reservation after modal login returns 403.

Output: app.js Phase-3 branch fully wired; Toast HTML in gifts/index.php.
- [ ] **T03: 03-reserva-atomica-meus-presentes 03**
  - Create the "Meus Presentes" view and add the conditional nav link. ReservationController::myGifts() (from Plan 01) already passes the $reservations array; this plan provides the templates that render it.

Purpose: Completes RESERVE-03 — the user can see their reservations and cancel them. The empty state (D-13) and conditional nav link (D-10) are part of this deliverable.

Output: 2 files modified. Phase 3 is fully functional after this plan.

## Files Likely Touched

- `sql/migrate_phase3.sql`
- `app/Models/ReservationModel.php`
- `app/Controllers/ReservationController.php`
- `app/Middleware/AuthMiddleware.php`
- `config/routes.php`
- `public/assets/js/app.js`
- `app/Views/public/gifts/index.php`
- `app/Views/public/my-gifts/index.php`
- `app/Views/layouts/main.php`
