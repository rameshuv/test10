# Delivery Checklist — Bonus Hunt Guesser v8.0.16 (2025-01-26)

Status legend: ✅ Complete · ⚠️ Needs verification · ❌ Missing / action required

## Automated Verification Snapshot
- ✅ `vendor/bin/phpunit` (11 tests / 80 assertions).【10e721†L1-L8】
- ❌ `composer phpcs` — WordPress Coding Standards failures remain across admin templates and the main plugin bootstrap (see sample violations in `admin/views/dashboard.php` and `bonus-hunt-guesser.php`).【54026f†L1-L210】

---

## 0) Plugin Header & Bootstrapping
- ✅ Plugin header advertises version 8.0.16, PHP 7.4, WordPress 6.3.5+, and MySQL 5.5.5 metadata as required.【F:bonus-hunt-guesser.php†L1-L16】
- ✅ Text domain loads via `load_plugin_textdomain()` during plugin initialization.【F:bonus-hunt-guesser.php†L389-L404】
- ❌ Coding standards remediation outstanding — normalize indentation and sanitize flagged `$_POST` inputs in `bonus-hunt-guesser.php` and admin views before delivery.【54026f†L1-L210】

## 1) Admin Dashboard (Latest Hunts)
- ✅ Submenu renamed to “Dashboard” and routed through `BHG_Admin::dashboard()`.【F:admin/class-bhg-admin.php†L60-L76】
- ✅ “Latest Hunts” card lists the three most recent hunts with per-winner rows, formatted balances, and closed timestamps.【F:admin/views/dashboard.php†L83-L200】

## 2) Bonus Hunts (List · Edit · Results)
- ✅ List view exposes Final Balance (em dash when open), Affiliate, Winners count, Guessing toggle, and Delete/Results actions.【F:admin/views/bonus-hunts.php†L245-L309】
- ✅ Add/Edit form limits tournaments to active entries, provides dual prize selectors (regular/premium), configurable winners count, and participant roster with profile links and removal controls.【F:admin/views/bonus-hunts.php†L400-L475】【F:admin/views/bonus-hunts-edit.php†L80-L220】
- ✅ Results screen highlights winners, renders “There are no winners yet” empty state, and shows Price column alongside affiliate-aware prize logic.【F:admin/views/bonus-hunts-results.php†L349-L420】
- ✅ Database migrations ensure `guessing_enabled`, affiliate linkage, winners count, and hunt↔tournament tables are provisioned.【F:includes/class-bhg-db.php†L250-L344】

## 3) Tournaments (List · Edit)
- ✅ Admin form surfaces Title, Description, Type (weekly/monthly/quarterly/yearly/all time), and Participants Mode controls with points mapping.【F:admin/views/tournaments.php†L300-L402】
- ✅ Pagination, sorting, and CRUD actions present on the tournaments list view.【F:admin/views/tournaments.php†L200-L296】

## 4) Users (Admin)
- ✅ Custom `WP_List_Table` implements search, sortable columns, affiliate toggles, and 30-per-page pagination with navigation.【F:admin/views/users.php†L1-L36】【F:admin/class-bhg-users-table.php†L27-L200】

## 5) Affiliates (Sync)
- ✅ Affiliate website CRUD with nonce-protected edit/delete and site list is available in admin.【F:admin/views/affiliate-websites.php†L1-L88】
- ⚠️ Confirm that adding/removing affiliates updates existing user profiles and hunt associations (exercise end-to-end with staging data). *Files to validate:* `includes/class-bhg-users.php`, `includes/class-bhg-models.php`, `admin/views/bonus-hunts.php`.

## 6) Prizes (Admin · Frontend · Shortcode)
- ✅ Admin table and modal cover prize CRUD, category selection, CSS panel, and three image slots (small/medium/big).【F:admin/views/prizes.php†L60-L216】
- ⚠️ Large-image uploads (1200×800 PNG) and frontend grid/carousel responsiveness require runtime testing. *Files to exercise:* `admin/views/prizes.php`, `includes/class-bhg-prizes.php`, `assets/js/bhg-prizes.js`.
- ❌ Requested prize enhancements pending:
  - Add **Prize Link** field and click-behavior options (same window / new window / image popup) to prize form and frontend renderer.【F:admin/views/prizes.php†L130-L216】【F:includes/class-bhg-prizes.php†L117-L220】
  - Implement category management UI (name/link/toggle) instead of fixed `get_categories()` list.【F:includes/class-bhg-prizes.php†L22-L24】
  - Surface image size labels (e.g., “Small (300×200)”) next to upload controls.【F:admin/views/prizes.php†L150-L176】
  - Wire dual prize set selection into hunt edit modal for premium prizes (ensure values load/save via `regular_prize_ids[]` and `premium_prize_ids[]`).【F:admin/views/bonus-hunts.php†L424-L448】
  - Carousel controls (visible count, total items, auto-scroll, show/hide title/category/description toggles) absent from settings/UI.【F:admin/views/prizes.php†L130-L216】【F:includes/class-bhg-shortcodes.php†L900-L1060】
  - Frontend should drop automatic “Prizes” heading and respect optional category links/toggles. Review shortcode rendering in `includes/class-bhg-shortcodes.php`.

## 7) Shortcodes (Catalog & Pages)
- ✅ “Info & Help” screen documents shortcode catalogue, including jackpot entries.【F:admin/views/shortcodes.php†L12-L210】
- ✅ `[bhg_hunts]` exposes Winners count and Details column (Guess Now / Show Results) with white header styling applied globally.【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:assets/css/bhg-shortcodes.css†L509-L546】
- ⚠️ Verify each required WordPress page embeds the correct shortcode stack and that pagination/sorting options behave on live content. *Files/pages to confirm:* `admin/views/page-template.php`, site Pages list.

## 8) Notifications
- ⚠️ Admin notification blocks (winners, tournaments, bonushunts) exist but need live email tests for BCC handling and toggle persistence. *Files:* `admin/views/notifications.php`, `bonus-hunt-guesser.php` (`bhg_handle_notifications_settings`).

## 9) Ranking & Points
- ✅ Tournament edit screen exposes editable placement points and ranking scope toggle.【F:admin/views/tournaments.php†L300-L389】
- ⚠️ Run recalculation/unit coverage for ranking service and ensure frontend highlights Top 3/winners as expected. *Files:* `includes/class-bhg-models.php`, `tests/RankingServiceTest.php`.

## 10) Global CSS / Color Panel
- ✅ Settings page offers typography/color controls for title blocks, headings, descriptions, and body text.【F:admin/views/settings.php†L167-L210】
- ⚠️ Confirm applied styles propagate across shortcode tables and widgets on the frontend.

## 11) Currency System
- ✅ Currency helpers (`bhg_currency_symbol`, `bhg_format_money`) present and dashboard leverages them for monetary columns.【F:bonus-hunt-guesser.php†L1029-L1050】【F:admin/views/dashboard.php†L120-L197】
- ⚠️ Test EUR↔USD toggle to ensure all admin/frontend outputs respect the option. *Files:* `admin/views/settings.php`, `includes/helpers.php`.

## 12) Database & Migrations
- ✅ `BHG_DB` provisions required columns plus jackpot and hunt↔tournament tables via `dbDelta()` safeguards.【F:includes/class-bhg-db.php†L250-L344】
- ⚠️ Execute migration on a clean and upgraded database to confirm idempotence (especially translations table seeding). *Files:* `includes/class-bhg-db.php`, `admin/views/database.php`.

## 13) Security & i18n
- ⚠️ Spot checks show nonces/escapes, but PHPCS flags unsanitized `$_POST` access (e.g., win limit settings). Audit and sanitize inputs in `bonus-hunt-guesser.php`, `admin/views/settings.php`, and AJAX handlers before delivery.【54026f†L170-L210】

## 14) Backward Compatibility
- ⚠️ Validate legacy hunts/guesses migrate cleanly, especially with new jackpot/prize schemas. Exercise upgrade on staging backup. *Files:* `includes/class-bhg-db.php`, `includes/class-bhg-models.php`.

## 15) Global UX Guarantees
- ✅ Admin tables implement search/sort/pagination; shortcode tables adopt consistent styling and affiliate lights.【F:admin/views/users.php†L15-L36】【F:assets/css/bhg-shortcodes.css†L509-L558】
- ⚠️ Re-run frontend smoke tests for timeline filters (This Week/Month/Year) across hunts/tournaments/leaderboards. *Files:* `includes/class-bhg-shortcodes.php`.

## 16) Release & Documentation
- ✅ README expanded with v8.0.16 highlights, manual dbDelta guidance, and QA checklist; Info & Help shortcodes catalogue already covers jackpot/prize functionality. *Files:* `README.md`, `admin/views/shortcodes.php`, `CHANGELOG.md`.

## 17) QA (Acceptance)
- ⚠️ Perform end-to-end flows: create/close hunts, enforce win limits, toggle guessing, switch currency, verify notifications, and confirm prize grids/carousels. Document evidence (screenshots/logs) before sign-off.

## Add-On: Winner Limits per User
- ✅ Settings UI for hunt/tournament limits plus enforcement hooks and notices are in place.【F:admin/views/settings.php†L100-L148】【F:includes/class-bhg-models.php†L320-L369】
- ⚠️ Validate rolling-window calculations with staged data and confirm admin notices communicate skipped entrants. *Files:* `includes/helpers.php`, `includes/class-bhg-models.php`, `admin/views/bonus-hunts-results.php`.

## Add-On: Jackpot Feature
- ✅ Jackpot schema deployed via migrations (`bhg_jackpots`, `bhg_jackpot_events`).【F:includes/class-bhg-db.php†L252-L293】
- ✅ `BHG_Jackpots` service handles CRUD, linkage filters, and hunt-close accounting.【F:includes/class-bhg-jackpots.php†L1-L200】
- ✅ Admin screen supports create/update/delete/reset with nonce protection and hunt/affiliate selectors.【F:admin/views/jackpots.php†L1-L120】
- ✅ Shortcodes `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` registered and styled via shortcode handler.【F:includes/class-bhg-shortcodes.php†L40-L92】
- ⚠️ Conduct functional QA (closing hunts hitting jackpots, ticker display, shortcode rendering) on staging.

---

## Outstanding Actions Before Delivery
1. Resolve PHPCS violations in `bonus-hunt-guesser.php`, `admin/views/dashboard.php`, and other flagged templates; re-run `composer phpcs` until clean.【54026f†L1-L210】
2. Complete prize enhancement backlog (link field, category manager, click behaviors, carousel controls) across `admin/views/prizes.php`, `includes/class-bhg-prizes.php`, `includes/class-bhg-shortcodes.php`, and related JS/CSS.
3. Execute full QA matrix covering affiliates sync, prize grids/carousels, win limits, notifications, timeline filters, and currency toggle. Capture evidence for client acceptance.
