# SPEC — DZ Boats Marketplace (French UI)

## 1) Goal
Build a marketplace in Algeria for:
- Boats
- JetSki
- Engines
- Parts

Platforms:
- iOS (Swift)
- Android (Kotlin)
- Website (Laravel Blade)
- Admin (Filament)
Backend:
- Laravel REST API used by apps and website

UI Language:
- French (fr) for all UI strings and messages.

## 2) User Types
- Guest: browse/search listings
- User: create/manage listings, favorite, request mediation, upload payment proof, request verification
- Vendor (monthly subscription): can publish engine/parts listings
- Admin: moderation, payment approval, mediation tickets, verification approvals, plan/subscription management

## 3) Authentication
Registration:
- name (required)
- email (required, unique)
- phone (required)
- password (required)
No OTP, no verification.

## 4) Listings
Categories: boat | jetski | engine | parts

Core fields:
- title, description
- category
- price_dzd (integer) + negotiable (bool)
- wilaya (string/enum)
- condition: new|used
- status:
  - draft
  - awaiting_payment
  - pending_review
  - active
  - rejected
  - sold
  - expired
  - paused (vendor subscription expired)
- published_until (datetime nullable)
- featured_until (datetime nullable)
- mediation_enabled (bool)
- views_count (int, default 0)
- favorites_count (int, default 0)
- specs (json) for category-specific details

Category-specific suggested specs (minimal MVP):
- boat: brand, model, year, length_m (opt), beam_m (opt)
- jetski: brand, model, year, hours (opt), power_hp (opt)
- engine: brand, model, year, power_hp (opt), fuel_type (opt)
- parts: part_type (opt), compatibility (opt)

Media:
- Images only
- Max 10 per listing
- Server compress/resize (store full + thumbnail)
- No video

## 5) Browse & Search
Pages / Screens:
- Home: categories + latest listings
- Search results with filters:
  - category
  - wilaya
  - min_price / max_price
  - condition
- Sort:
  - newest (default)
  - price_low_to_high
  - price_high_to_low

Featured:
- If featured_until > now, show in a top section.
- Top section shows a limited number of slots (configurable in admin).
- If overflow, rotate ordering (simple fairness).

## 6) Favorites
- Users can favorite/unfavorite listings
- favorites_count is maintained as counter cache

## 7) Views Tracking
- views_count increments only once per day per visitor (approx).
- Implement via listing_views:
  - listing_id
  - viewer_user_id nullable
  - viewer_ip_hash
  - view_date
- Prevent duplicate increments per day.

## 8) Mediation (Seller-Controlled)
Seller can enable mediation per listing.
When mediation_enabled = true:
- Hide seller phone and direct contact buttons
- Buyer creates a mediation ticket to contact Admin
- Admin acts as intermediary for a fee (fee configured later)

When mediation_enabled = false:
- Show seller phone + direct call button

Mediation tickets:
- status: new | in_progress | awaiting_payment | closed | cancelled
- fee_amount_dzd nullable
- payment_status: unpaid | paid | waived
- buyer_message (optional), admin_notes (optional)

## 9) Payments (MVP manual proofs)
Payment methods:
- BaridiMob
- CCP
- Bank transfer

All payments in MVP are manual:
- User uploads proof (image)
- Admin approves/rejects

Payment types:
- publish_listing
- featured_listing
- vendor_subscription
- mediation_fee

Listing publish rules:
- For boat/jetski:
  - publish fee 5000 DZD one-time
  - once admin approves payment and listing is approved:
    - status = active
    - published_until = now + 365 days

Featured rules:
- 12000 DZD for 30 days
- once approved: featured_until = now + 30 days

## 10) Vendor Subscription (Engines/Parts only)
Vendor:
- users.account_type = vendor
- vendor has a monthly subscription (manual proof approval)

Rule:
- Creating engine/parts listing requires active subscription.
- If subscription expired after grace period, engine/parts listings become paused (not public).

Boats/jetski are NOT covered by vendor subscription; they follow the 5000 DZD publish model.

## 11) Verification (Verified badge)
User uploads ID document (image).
Admin approves/rejects.
If approved:
- user.verified_badge = true
Badge is displayed on profile and listing cards.

States:
- none | pending | approved | rejected

## 12) Admin (Filament)
Resources:
- Users: block/unblock, set account_type, view stats, manage verification status
- Listings: moderation queue, approve/reject with reason, mark sold/expired, feature status
- Payments: inbox pending proofs, approve/reject, link to listing/subscription/ticket
- MediationTickets: manage status, set fee, mark paid, add notes
- Plans & Subscriptions: manage vendor plans and subscription periods
- Settings: featured slots, durations (365 days, 30 days), prices

## 13) Localization (French UI)
- Use Laravel translations for Blade and server validation messages.
- Define consistent French vocabulary (examples):
  - Publier, Mettre en avant, Favoris, Vues, Contacter, Contacter l'administration,
    Vendu, Expiré, En attente de paiement, En attente de validation

## 14) Jobs / Automation
Daily job:
- expire listings where published_until < now
- expire featured where featured_until < now
- pause vendor listings if subscription expired after grace period
