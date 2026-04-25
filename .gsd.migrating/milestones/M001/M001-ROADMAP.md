# M001: M001: Migration

**Vision:** Site de aniversário completo com lista de presentes e contribuição via Pix.

## Slices

- [x] **S01: S01** `risk:medium` `depends:[]`
  > After this: Build the static skeleton: directory structure, Apache routing, autoloader, router, route table, config files, SQL schema, and the credential placeholder template.

- [ ] **S02: Presentes Publicos Crud Admin** `risk:medium` `depends:[S01]`
  > After this: Foundation infrastructure for Phase 2: schema migration adding missing gift columns, Router extension for dynamic path segments, Bootstrap Icons in both layouts, upload directory security, and gift card CSS visual states.

- [ ] **S03: Reserva Atomica Meus Presentes** `risk:medium` `depends:[S02]`
  > After this: Create the complete backend for Phase 3: schema migration, ReservationModel with transactional reserve/cancel, ReservationController with store/myGifts/cancel/csrfToken actions, a new requireGuest() middleware method, and new routes.
