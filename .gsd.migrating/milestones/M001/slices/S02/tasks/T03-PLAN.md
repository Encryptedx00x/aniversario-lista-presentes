# T03: 02-presentes-publicos-crud-admin 03

**Slice:** S02 — **Milestone:** M001

## Description

Public gifts page: GiftController (index + filter endpoints), the full public gifts view (filter bar + card grid + auth modal structure), and the _cards.php partial for AJAX filter responses.

Purpose: This is the primary user-facing feature of Phase 2. Guests browse gifts, use the filter/search/sort bar, and see the auth modal when they click "Escolher este presente". Plan 04 wires the JS; this plan delivers the HTML structure those scripts target.

Output: GiftController.php, app/Views/public/gifts/index.php, app/Views/public/gifts/_cards.php, updated config/routes.php.

## Must-Haves

- [ ] "GET / renders the public gifts page with all active gifts displayed as Bootstrap cards (3 cols desktop, 2 tablet, 1 mobile)"
- [ ] "Each card shows: image (or placeholder), name, short description, category badge if present, price if present, available qty, status badge, and Escolher/Indisponível button"
- [ ] "Sold-out card has .gift-card--esgotado class and disabled button labeled Indisponível"
- [ ] "Featured card has .gift-card__featured-badge with text DESTAQUE (bg-warning)"
- [ ] "Filter bar search input, category dropdown, and sort dropdown are present above the card grid"
- [ ] "GET /gifts/filter returns only the card grid HTML fragment (no layout wrapper) — no DOCTYPE, no <html>"
- [ ] "Auth modal div#authModal exists in the page with data-csrf-token attribute and two tab forms"
- [ ] "Clicking Escolher este presente sets window._pendingGiftId (handled by Plan 04 JS — button must have data-gift-id attribute)"

## Files

- `app/Controllers/GiftController.php`
- `app/Views/public/gifts/index.php`
- `app/Views/public/gifts/_cards.php`
- `config/routes.php`
