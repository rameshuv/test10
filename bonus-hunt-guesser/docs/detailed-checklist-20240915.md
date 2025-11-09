# Bonus Hunt Guesser – Detailed Delivery Checklist (2024-09-15)

> **Superseded:** See `docs/detailed-checklist-20240916.md` for the current 8.0.16 compliance snapshot. This document remains for historical reference.

Status Legend:

* ✅ — Requirement fully satisfied and verified.
* ⚠️ — Requirement partially satisfied or requires follow-up/verification.
* ❌ — Requirement not implemented.
**Status Legend**

- ✅ — Requirement fully satisfied and verified.
- ⚠️ — Requirement partially satisfied or requires follow-up/verification.
- ❌ — Requirement not implemented.

---

## 0. Plugin Bootstrap & Tooling

| Requirement | Status | Notes |
| --- | --- | --- |
| Plugin header matches contract (name, version 8.0.14, platform requirements) | ✅ | `bonus-hunt-guesser.php` metadata matches customer spec. |
| Text domain loads on `plugins_loaded` | ✅ | Verified in bootstrap loader. |
| PHPCS (WordPress Core/Docs/Extra) passes with no errors | ❌ | Global run fails due to legacy whitespace issues across existing files. |

## 1. Admin Dashboard — Latest Hunts

| Requirement | Status | Notes |
| --- | --- | --- |
| Latest 3 hunts with columns (Title, Winners w/ guess + diff, Start/Final balance, Closed At) | ⚠️ | Layout present; manual spot-check recommended after data import. |
| Each winner on own row, usernames bold | ⚠️ | Rendering logic in `dashboard.php`; confirm formatting during QA. |
| Start/Final balance left-aligned | ⚠️ | Table defaults to left alignment; verify with sample data. |

## 2. Bonus Hunts (List/Edit/Results)

| Requirement | Status | Notes |
| --- | --- | --- |
| List includes Final Balance ("-" if open) and Affiliate column | ⚠️ | Column present; needs UI verification. |
| Actions: Edit, Results, Delete, Enable/Disable Guessing | ⚠️ | Hooks exist; verify end-to-end. |
| Edit screen tournament multiselect (active only) | ✅ | Query restricts to active tournaments. |
| Winners count configurable | ✅ | `winners_count` field persists. |
| Participants table below form with remove links | ⚠️ | Table renders; confirm removal flow. |
| Results default to latest closed hunt with selectors and filters | ⚠️ | Logic in `bonus-hunts-results.php`; confirm UI. |
| Empty state text "There are no winners yet" | ✅ | Present in template. |
| Time filter (This Month default / Year / All) | ⚠️ | Options wired; test data required. |
| Winners highlighted green + bold; alternating row colors; Prize column present | ⚠️ | Styling partial; now maps premium vs regular prizes per winner. |
| DB columns (`guessing_enabled`, `affiliate_id`) enforced | ✅ | Schema migration ensures fields. |
| Dual prize sets (regular + premium) selectable in admin | ✅ | Add/edit views now provide separate selectors and persistence. |
| Premium prizes shown to affiliates ahead of regular prizes | ⚠️ | Frontend and admin results updated; verify with affiliate test users. |

## 3. Tournaments (List/Edit)

| Requirement | Status | Notes |
| --- | --- | --- |
| Title & description fields available | ✅ | Present in edit form. |
| Type field includes quarterly, alltime; legacy period removed | ⚠️ | Options added; confirm data migration. |
| Participants mode toggle (winners/all guessers) | ✅ | Field persisted. |
| Actions: Edit, Results, Close, Delete | ⚠️ | Buttons present; verify flows. |
| DB column `participants_mode` | ✅ | Added via migration. |

## 4. Users Admin

| Requirement | Status | Notes |
| --- | --- | --- |
| Search by user/email | ✅ | Query builder supports `s` parameter. |
| Sortable columns | ⚠️ | Sorting UI exists; confirm on staging. |
| Pagination (30/page) | ✅ | Table uses `WP_List_Table` page size 30. |
| Profile shows affiliate toggles per site | ⚠️ | UI renders; test updates. |

## 5. Affiliates Sync

| Requirement | Status | Notes |
| --- | --- | --- |
| Adding/removing affiliate sites syncs user meta fields | ⚠️ | Helper functions present; run integration test. |
| Frontend affiliate lights + optional site display | ✅ | `bhg_render_affiliate_dot()` and shortcodes display lights. |

## 6. Prizes Module (Admin + Frontend + Shortcode)

| Requirement | Status | Notes |
| --- | --- | --- |
| CRUD fields including CSS panel and active toggle | ✅ | `BHG_Prizes` form supports fields. |
| Three image sizes (small/medium/big) | ⚠️ | Size selectors stored; confirm media handling for large uploads. |
| Hunt edit selects 1+ prizes (now separate regular/premium) | ✅ | Dual selectors implemented. |
| Frontend renders grid/carousel with accessible controls | ⚠️ | `render_prize_section()` handles layout; smoke test recommended. |
| Shortcode `[bhg_prizes]` supports category, design, size, active filters | ⚠️ | Implementation present; add regression test. |
| Premium prize set displayed above regular set for affiliate users | ⚠️ | Active hunt shortcode updated; confirm login-dependent behavior. |

## 7. Shortcodes Catalog & Pages

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin “Info & Help” lists all shortcodes w/ examples | ⚠️ | Partial coverage; review content. |
| Existing shortcodes `[bhg_user_profile]`, etc. maintained | ✅ | No regression detected. |
| `[bhg_user_guesses]` difference column when final balance known | ⚠️ | Implementation exists; verify dataset. |
| `[bhg_hunts]` includes Winners count + Details column | ⚠️ | Column exists; verify links show Guess Now / Show Results. |
| `[bhg_tournaments]` updated naming, no legacy type | ⚠️ | UI updated; confirm data. |
| `[bhg_leaderboards]` metrics (Times Won, Avg positions) | ⚠️ | Logic present; add tests. |
| `[bhg_advertising]` placement="none" supported | ✅ | Admin + shortcode allow `none`. |
| Core pages created with override metabox | ⚠️ | Page registration scaffolding exists; confirm pages on install. |

## 8. Notifications

| Requirement | Status | Notes |
| --- | --- | --- |
| Winners/Tournament/Bonushunt email blocks with Title, HTML, BCC, toggle | ⚠️ | Options stored; verify UI. |
| Uses `wp_mail` with BCC | ✅ | Implementation in notifications helper. |

## 9. Ranking & Points

| Requirement | Status | Notes |
| --- | --- | --- |
| Default mapping editable (25/15/10/5/4/3/2/1) | ⚠️ | Points map stored; confirm UI. |
| Scope toggle active/closed/all hunts | ⚠️ | Field present; ensure calculations. |
| Winners only accrue points (where relevant) | ⚠️ | Logic in `BHG_Models::close_hunt`; add coverage. |
| Backend + frontend rankings highlight winners/top 3 | ⚠️ | Styling present; QA needed. |
| Centralized service + unit tests | ⚠️ | Partial coverage; extend tests. |

## 10. Global CSS/Color Panel

| Requirement | Status | Notes |
| --- | --- | --- |
| Global typography & color controls applied across components | ⚠️ | Settings exist; verify propagation. |

## 11. Currency System

| Requirement | Status | Notes |
| --- | --- | --- |
| Setting `bhg_currency` (EUR/USD) stored | ✅ | Option managed in settings. |
| Helpers `bhg_currency_symbol()`, `bhg_format_money()` implemented | ✅ | Helpers defined in bootstrap. |
| All money outputs call helper | ⚠️ | Majority updated; audit legacy echoes. |

## 12. Database & Migrations

| Requirement | Status | Notes |
| --- | --- | --- |
| Columns `guessing_enabled`, `participants_mode`, `affiliate_id` ensured | ✅ | Schema migrator checks and adds columns. |
| Junction table hunt ↔ tournament | ✅ | `bhg_tournaments_hunts` maintained. |
| Idempotent `dbDelta` with keys/indexes | ⚠️ | Most tables covered; monitor new additions. |
| Hunt prize map supports `prize_type` with indexes | ✅ | Migration adds column and adjusts unique key. |

## 13. Security & i18n

| Requirement | Status | Notes |
| --- | --- | --- |
| Capability checks, nonces, sanitization/escaping | ⚠️ | Majority present; audit new inputs. |
| BCC email validation | ⚠️ | Basic sanitization; consider stricter validation. |
| All strings in `bonus-hunt-guesser` text domain | ⚠️ | New strings registered; audit older code. |

## 14. Backward Compatibility

| Requirement | Status | Notes |
| --- | --- | --- |
| Legacy data loads with safe defaults | ⚠️ | Migration fallback implemented; test on legacy DB. |
| New prize-type column defaults to regular | ✅ | Upgrade routine normalizes existing entries. |

## 15. Global UX Guarantees

| Requirement | Status | Notes |
| --- | --- | --- |
| Sorting, search, pagination (30/page) across lists | ⚠️ | Implemented; verify per screen. |
| Timeline filters (This Week/Month/Year/Last Year/All) | ⚠️ | Controls present; confirm query coverage. |
| Affiliate lights + website display | ✅ | Shortcodes and admin tables include indicators. |
| Profile blocks show real name, email, affiliate | ⚠️ | UI exists; smoke test profile edit. |

## 16. Release & Docs

| Requirement | Status | Notes |
| --- | --- | --- |
| Version bumped to 8.0.14 everywhere | ✅ | Header and metadata updated. |
| Changelog with migrations | ⚠️ | Needs refresh to document prize-type migration. |
| Readme/Admin “Info & Help” updated | ⚠️ | Requires pass to capture new dual-prize UX. |
| Screenshots/GIFs optional | ⚠️ | Not yet provided. |

## 17. QA Acceptance

| Requirement | Status | Notes |
| --- | --- | --- |
| E2E create/close hunts and verify highlights/points | ❌ | Full QA pending. |
| Currency switch reflects across admin/frontend | ⚠️ | Manual test required. |
| Guessing toggle blocks form | ⚠️ | Feature present; verify. |
| Participants mode respected in tournaments | ⚠️ | Needs regression test. |
| Prizes CRUD + FE grid/carousel + CSS panel | ⚠️ | CRUD works; FE dual-set requires verification. |
| Notifications BCC + toggle | ⚠️ | Basic integration; QA outstanding. |
| Translations loaded; front-end text editable | ⚠️ | Translation system present; review coverage. |

## Add-On: Winner Limits per User

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin settings (Bonus Hunt / Tournament limits) | ✅ | Settings UI implemented. |
| Enforcement skips ineligible winners and assigns next eligible | ⚠️ | Logic in `BHG_Models::close_hunt`; run scenario tests. |
| Win logging for rolling windows | ⚠️ | Data recorded; confirm cleanup. |
| Skipped winner messaging (admin/frontend) | ⚠️ | Notices added; verify copy. |

## Add-On: Frontend Adjustments

| Requirement | Status | Notes |
| --- | --- | --- |
| Frontend table header links white (#fff) | ✅ | CSS enforces white headers. |
| `[bhg_hunts]` Details column with contextual links | ⚠️ | Column implemented; confirm Show Results/Guess Now routes. |

## Add-On: Prizes Enhancements

| Requirement | Status | Notes |
| --- | --- | --- |
| Large image upload support (1200x800 PNG) | ⚠️ | Media handling assumed; validate upload constraints. |
| Image size labels in admin | ⚠️ | Field hints exist; double-check UI. |
| Prize link field w/ click behavior options | ⚠️ | Need verification. |
| Category management with optional link toggle | ⚠️ | Partially implemented. |
| Carousel controls (visible images, total load, auto-scroll) | ⚠️ | Settings exist; ensure front-end integration. |
| Toggle visibility of title/category/description | ⚠️ | Options defined; confirm output. |
| Responsive display adjusts image size | ⚠️ | Logic present; add responsive QA. |
| Remove automatic "Prizes" heading above grid | ✅ | Heading suppressed when using dual-set wrapper. |
| Dual prize selectors on hunt admin | ✅ | Implemented with regular/premium sets. |
| Affiliate winners see premium + regular prizes | ⚠️ | Frontend updated; confirm affiliate detection. |

## Add-On: Jackpot Feature (New Module)

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin menu "Jackpots" with CRUD | ❌ | Feature not yet implemented. |
| Fields (title, start amount, linked hunts options, increase per miss) | ❌ | Pending. |
| List view of latest jackpots | ❌ | Pending. |
| Logic to hit/increase jackpots on hunt close | ❌ | Pending. |
| Shortcodes `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` | ❌ | Pending implementation. |

