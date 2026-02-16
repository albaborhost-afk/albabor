# TASKS — Milestones (Backend + Blade + Filament, then Mobile)

## Milestone 0 — Setup ✅
- [x] Laravel app + DB
- [x] Auth (name/email/phone/password)
- [x] Roles (admin/user)
- [x] Filament install + admin auth
- [x] Blade layout + French localization skeleton

## Milestone 1 — Listings + Media
- [ ] listings + listing_media tables
- [ ] Upload max 10 images, compress/resize
- [ ] Create/edit/delete listing
- [ ] Basic Blade pages: create listing, my listings, listing detail

## Milestone 2 — Browse/Search + Featured section
- [ ] Home latest listings
- [ ] Search filters + sort
- [ ] Featured section with limited slots

## Milestone 3 — Favorites
- [ ] favorites table
- [ ] favorites_count counter cache
- [ ] Blade favorites page + API endpoints

## Milestone 4 — Views tracking
- [ ] listing_views table
- [ ] views_count unique-per-day logic

## Milestone 5 — Manual payments
- [ ] payments table
- [ ] upload proof (baridimob/ccp/bank)
- [ ] approve payments in Filament
- [ ] publish lifecycle (5000/365) + featured (12000/30)

## Milestone 6 — Moderation
- [ ] admin approve/reject with reasons
- [ ] only approved+paid listings become active
- [ ] scheduled jobs: expire listing/featured

## Milestone 7 — Mediation
- [ ] mediation_enabled toggle on listings
- [ ] mediation_tickets + Filament resource
- [ ] buyer request flow (French UI)

## Milestone 8 — Verification
- [ ] verification_requests + Filament approve/reject
- [ ] verified badge display

## Milestone 9 — Vendor subscription (engines/parts only)
- [ ] vendor_profiles
- [ ] plans + subscriptions
- [ ] manual proof approval -> activate subscription
- [ ] enforce subscription for engine/parts listings

## Milestone 10 — Mobile apps
iOS Swift:
- [ ] Auth, browse/search, listing details, favorites, my listings, create listing (upload images),
  payment proof upload, mediation request, verification upload, vendor store view

Android Kotlin:
- [ ] Same parity as iOS
