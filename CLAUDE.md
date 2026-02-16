# CLAUDE.md — DZ Boats Marketplace

## Stack
- Backend: Laravel (REST API)
- Website: Laravel Blade
- Admin: Filament
- Mobile: iOS (Swift), Android (Kotlin)
- Deployment: Laravel Cloud

## Language (CRITICAL)
- All UI and user-facing strings in **French (fr)** from day 1.
- Use Laravel localization: resources/lang/fr/*.php
- Blade must use `__('...')` translations.
- API responses may be English for dev, but anything shown to users must be French.
- User-entered text can be any language.

## Working rules
1) Always restate requirements and assumptions before coding.
2) Provide a plan + list of files to change.
3) Implement **one milestone at a time**, then stop.
4) No TODOs left behind.
5) After each milestone, output:
   - How to run
   - How to test
   - Checklist mapping work to requirements

## Product rules (do not deviate)
Auth:
- name + email + phone + password
- No OTP, no verification in MVP.

Listings:
- Categories: boat, jetski, engine, parts
- Wilaya only
- Images only, max 10; compress/resize on upload; no video

Business:
- Boats/JetSki publish: 5000 DZD one-time, valid up to 365 days OR until deleted/sold
- Featured: 12000 DZD valid 30 days
- Vendor subscription monthly: ONLY engines/parts publishing requires active subscription

Payments:
- Manual proof upload for BaridiMob / CCP / Bank transfer
- Admin approves manually in Filament

Mediation:
- Seller-controlled toggle per listing
- If ON: hide seller phone; contact via Admin ticket
- If OFF: show seller phone + direct call button

Stats:
- Track listing views (unique per day) + favorites count

Verification:
- User uploads ID doc
- Admin approves to enable Verified badge

## Security & quality
- Rate-limit listing creation and mediation requests.
- Validate and sanitize listing descriptions (basic anti-spam).
- Use policies for authorization (owner/admin).
- Add indexes for search filters and status queries.
