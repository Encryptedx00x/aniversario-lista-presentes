# T03: 03-reserva-atomica-meus-presentes 03

**Slice:** S03 — **Milestone:** M001

## Description

Create the "Meus Presentes" view and add the conditional nav link. ReservationController::myGifts() (from Plan 01) already passes the $reservations array; this plan provides the templates that render it.

Purpose: Completes RESERVE-03 — the user can see their reservations and cancel them. The empty state (D-13) and conditional nav link (D-10) are part of this deliverable.

Output: 2 files modified. Phase 3 is fully functional after this plan.

## Must-Haves

- [ ] "GET /my-gifts renders a list of the logged-in user's reservations with thumbnail, gift name, category, status, date"
- [ ] "Each reservation with status=reserved shows a 'Cancelar reserva' form button"
- [ ] "Reservations with status=confirmed or status=cancelled do NOT show the cancel button"
- [ ] "If user has no reservations, a friendly empty state with link to /gifts is shown"
- [ ] "The nav in main.php shows 'Meus Presentes' link only when role=guest"
- [ ] "Visiting /my-gifts while not logged in redirects to /gifts (handled by Plan 01 AuthMiddleware)"

## Files

- `app/Views/public/my-gifts/index.php`
- `app/Views/layouts/main.php`
