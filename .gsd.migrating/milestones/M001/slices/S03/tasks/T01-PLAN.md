# T01: 03-reserva-atomica-meus-presentes 01

**Slice:** S03 — **Milestone:** M001

## Description

Create the complete backend for Phase 3: schema migration, ReservationModel with transactional reserve/cancel, ReservationController with store/myGifts/cancel/csrfToken actions, a new requireGuest() middleware method, and new routes.

Purpose: The atomic reservation engine is the core deliverable of this phase. The SELECT FOR UPDATE transaction pattern prevents concurrent over-reservation. The /csrf-token endpoint solves the stale-token pitfall that would cause 403 on every first-time reservation after modal login.

Output: 5 files created/modified. Backend fully functional; view layer (Plan 03) and JS wiring (Plan 02) depend on these contracts.

## Must-Haves

- [ ] "POST /reserve with valid gift_id + CSRF returns JSON {success:true, available_qty:N, is_exhausted:bool}"
- [ ] "available_qty is decremented in gifts table atomically; never goes negative"
- [ ] "Two concurrent requests for the last unit result in exactly one INSERT to reservations"
- [ ] "POST /reserve when gift is exhausted returns JSON {success:false, error:'Esse presente acabou de ficar indisponível.'}"
- [ ] "GET /csrf-token returns JSON {token: '<string>'}"
- [ ] "GET /my-gifts without session redirects to /gifts"
- [ ] "POST /my-gifts/cancel with valid reservation_id returns stock and sets status=cancelled"

## Files

- `sql/migrate_phase3.sql`
- `app/Models/ReservationModel.php`
- `app/Controllers/ReservationController.php`
- `app/Middleware/AuthMiddleware.php`
- `config/routes.php`
