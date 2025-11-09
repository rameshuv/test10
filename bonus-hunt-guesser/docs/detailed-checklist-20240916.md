# Bonus Hunt Guesser – Detailed Delivery Checklist (2024-09-16)

> **Superseded:** See `docs/final-checklist-20240917.md` for the most recent compliance snapshot covering all remaining gaps.

Status legend:

* ✅ — Requirement fully satisfied and verified.
* ⚠️ — Requirement partially satisfied or requires more QA.
* ❌ — Requirement missing or known to be non-compliant.

Each requirement below reflects the consolidated customer contract for version **8.0.16**. Where available, notes reference the implementing files and any follow-up actions.

## 0. Plugin Bootstrap & Tooling

| Requirement | Status | Notes |
| --- | --- | --- |
| Plugin header matches contract (metadata + version 8.0.16) | ✅ | `bonus-hunt-guesser.php` exposes version 8.0.16 with the agreed WordPress/PHP/MySQL requirements. |
| Text domain loads on `plugins_loaded` | ✅ | Loader still hooks `load_plugin_textdomain()` during boot. |
| PHPCS (WordPress Core/Docs/Extra) passes with no errors | ❌ | Repository-level run continues to fail because of legacy spacing/indentation violations across admin/controllers/tests. |

## 1. Admin Dashboard – “Latest Hunts”

| Requirement | Status | Notes |
| --- | --- | --- |
| Card lists latest 3 hunts with Title, Winners (+guess/+diff), Start/Final balance, Closed At | ⚠️ | Template outputs requested columns, but needs QA with real data. |
| Each winner rendered on its own row with bold username | ⚠️ | Logic implemented; visual verification pending. |
| Start/Final balance left-aligned | ⚠️ | Default table alignment appears left but needs confirmation after styling review. |

## 2. Bonus Hunts (Admin List/Edit/Results)

| Requirement | Status | Notes |
| --- | --- | --- |
| List includes Final Balance column (shows “–” if open) and Affiliate column | ⚠️ | Columns wired up; confirm formatting. |
| List actions: Edit, Results, Admin Delete, Enable/Disable Guessing | ⚠️ | Actions registered; regression test required. |
| Edit screen: tournament multiselect limited to active tournaments | ✅ | Query filters inactive tournaments. |
| Edit screen: winners count configurable | ✅ | `winners_count` persisted in hunts table. |
| Participants list with remove action and profile links | ⚠️ | UI renders participants; verify delete flow and capability checks. |
| Results view defaults to latest closed hunt and supports selectors | ⚠️ | Data layer in `bonus-hunts-results.php`; manual QA pending. |
| Results empty state message | ✅ | “There are no winners yet” copy present. |
| Time filter: This Month (default) / This Year / All Time | ⚠️ | Filter options exist; confirm queries return expected data. |
| Winners highlighted (green + bold), alternating row colors, Prize column | ⚠️ | Styles exist but require UI sign-off. |
| Database columns `guessing_enabled` & `affiliate_id` enforced | ✅ | `BHG_DB::create_tables()` ensures fields on migrations. |
| Dual prize sets (regular + premium) selectable in admin | ✅ | Add/edit forms expose both selectors and persist via `BHG_Prizes`. |
| Affiliate winners see premium prize set above regular prizes | ⚠️ | Frontend logic toggles premium display for affiliates; needs user acceptance testing. |

## 3. Tournaments (Admin)

| Requirement | Status | Notes |
| --- | --- | --- |
| Title and description fields available | ✅ | Edit view includes both fields. |
| Type options include quarterly/all time; legacy period removed | ⚠️ | Options added, but migration of existing data still needs verification. |
| Participants mode toggle (winners only | all guessers) | ✅ | Stored in `participants_mode`. |
| Actions: Edit, Results, Close, Admin Delete | ⚠️ | Buttons exposed; confirm capabilities. |
| Database column `participants_mode` | ✅ | Added during migrations. |

## 4. Users (Admin)

| Requirement | Status | Notes |
| --- | --- | --- |
| Search by username/email | ✅ | `BHG_Users_Table` integrates `WP_User_Query` search argument. |
| Sortable table columns | ⚠️ | Sorting metadata present but requires QA. |
| Pagination (30 per page) | ✅ | List table enforces 30 item pagination. |
| Profile shows affiliate toggles per affiliate website | ⚠️ | Fields rendered; confirm persistence. |

## 5. Affiliates (Sync)

| Requirement | Status | Notes |
| --- | --- | --- |
| Adding/removing affiliate websites syncs user profile fields | ⚠️ | Helper functions exist but need integration test coverage. |
| Frontend affiliate lights and optional website display | ✅ | Shortcode helpers render affiliate dots and names. |

## 6. Prizes (Admin + Frontend + Shortcode)

| Requirement | Status | Notes |
| --- | --- | --- |
| CRUD supports title, description, category, image, CSS, active flag | ✅ | `BHG_Prizes` handlers persist these properties. |
| Three image sizes (small/medium/big) including large image uploads | ⚠️ | Fields exist; need validation for 1200×800 PNG support. |
| Hunt edit selects 1+ prizes (regular and premium) | ✅ | Admin form persists both sets. |
| Frontend renders grid/carousel with dots/arrows and fallback | ⚠️ | Rendering logic present; cross-device QA outstanding. |
| Shortcode `[bhg_prizes]` parameters (category, design, size, active) | ⚠️ | Option parsing implemented; add automated tests. |
| Premium prize set display rules | ⚠️ | Affiliate gating logic present; verify with affiliate and non-affiliate accounts. |
| Dual prize sets per winner in results view | ⚠️ | Admin results highlight premium prizes but requires review. |

## 7. Shortcodes Catalog & Core Pages

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin “Info & Help” enumerates all shortcodes with examples | ⚠️ | Partial coverage; documentation page needs expansion. |
| Existing shortcodes remain supported (`[bhg_user_profile]`, `[bhg_guess_form]`, etc.) | ✅ | No regressions detected. |
| `[bhg_user_guesses]`: difference column after final balance | ⚠️ | Logic present; verify formatting. |
| `[bhg_hunts]`: winners count + Details column with contextual links | ⚠️ | Column generated; ensure Guess Now/Show Results URLs valid. |
| `[bhg_tournaments]`: updated columns and naming | ⚠️ | Implementation mostly done; cross-check type removal. |
| `[bhg_leaderboards]`: metrics for Times Won, Avg hunt/tournament positions | ⚠️ | Calculations exist; add unit tests. |
| `[bhg_advertising]`: placement="none" for shortcode-only | ✅ | Admin and rendering support option. |
| Required pages (Active Hunt, All Hunts, etc.) auto-created with override metabox | ⚠️ | Page scaffolding script present; confirm on activation. |

## 8. Notifications

| Requirement | Status | Notes |
| --- | --- | --- |
| Winners/Tournament/Bonushunt blocks with Title, HTML Description, BCC, enable toggle | ⚠️ | Settings exist but need UI verification. |
| Notifications use `wp_mail()` with BCC honored | ✅ | Implementation leverages `wp_mail()` and includes BCC header handling. |

## 9. Ranking & Points

| Requirement | Status | Notes |
| --- | --- | --- |
| Editable default mapping (25/15/10/5/4/3/2/1) | ⚠️ | Settings stored but require admin QA. |
| Scope toggle (active/closed/all hunts) | ⚠️ | Option recorded; validate calculations. |
| Only winners accrue points | ⚠️ | Logic attempts to enforce; add regression coverage. |
| Backend + frontend rankings highlight winners + Top 3 | ⚠️ | Styling rules exist; needs UX sign-off. |
| Centralized service + unit tests | ⚠️ | Tests exist but limited in coverage. |

## 10. Global CSS / Color Panel

| Requirement | Status | Notes |
| --- | --- | --- |
| Global typography and color controls apply to shared components | ⚠️ | Settings persisted; verify front-end application. |

## 11. Currency System

| Requirement | Status | Notes |
| --- | --- | --- |
| Setting `bhg_currency` (EUR/USD) stored | ✅ | Option available in settings. |
| Helpers `bhg_currency_symbol()` and `bhg_format_money()` implemented | ✅ | Helper functions defined in bootstrap. |
| All monetary outputs use helpers | ⚠️ | Majority updated; run audit to confirm no direct currency formatting remains. |

## 12. Database & Migrations

| Requirement | Status | Notes |
| --- | --- | --- |
| Columns `guessing_enabled`, `participants_mode`, `affiliate_id` | ✅ | Migration ensures they exist. |
| Junction table for hunt ↔ tournament mapping | ✅ | `bhg_tournaments_hunts` table maintained. |
| Idempotent `dbDelta()` with keys/indexes for all tables | ⚠️ | Core tables covered; new jackpot tables pending (see ❌ below). |
| Dual prize mapping table with prize type handling | ✅ | `bhg_hunt_prizes` tracks prize type. |

## 13. Security & i18n

| Requirement | Status | Notes |
| --- | --- | --- |
| Capability checks, nonces, sanitization/escaping | ⚠️ | Many screens protected; conduct security sweep for recent additions. |
| BCC email validation | ⚠️ | Basic sanitization exists; strengthen validation logic. |
| Strings localized under `bonus-hunt-guesser` | ⚠️ | Most strings translated; audit remaining hard-coded text. |

## 14. Backward Compatibility

| Requirement | Status | Notes |
| --- | --- | --- |
| Legacy data loads with safe defaults | ⚠️ | Migration routines attempt to normalize data; more testing needed. |
| New settings/prize types default safely | ✅ | Regular prize default enforced when type missing. |

## 15. Global UX Guarantees

| Requirement | Status | Notes |
| --- | --- | --- |
| Sorting, search, pagination (30/page) across admin tables | ⚠️ | Implemented across list tables; QA per screen. |
| Timeline filters (This Week/Month/Year/Last Year/All-Time) | ⚠️ | Controls exist; confirm data queries. |
| Affiliate lights and website display | ✅ | Shortcodes render colored indicators. |
| Profile blocks display real name, email, affiliate | ⚠️ | UI present; confirm accuracy. |

## 16. Release & Documentation

| Requirement | Status | Notes |
| --- | --- | --- |
| Version bumped to 8.0.16 across metadata/constants | ⚠️ | Header and constant updated; remaining docs still reference 8.0.14 and need refresh. |
| Changelog updated for new release | ❌ | `CHANGELOG.md` still capped at 8.0.14 entry. |
| Readme/Admin “Info & Help” cover new features | ❌ | Documentation predates jackpot module and other add-ons. |

## 17. QA & Acceptance Tests

| Requirement | Status | Notes |
| --- | --- | --- |
| E2E: create/close hunts → winners highlight & points propagation | ⚠️ | Requires manual QA. |
| Currency switch reflects across admin/frontend | ⚠️ | Needs regression test. |
| Guessing toggle blocks/unblocks form | ⚠️ | Feature implemented; confirm behavior. |
| Tournament participants mode respected in results | ⚠️ | Requires scenario testing. |
| Prizes CRUD + FE grid/carousel + CSS panel | ⚠️ | Implemented; run acceptance tests. |
| Notifications BCC + enable/disable toggles | ⚠️ | Implementation present; QA outstanding. |
| Translations load and strings translatable | ⚠️ | Text domain ready; review translation coverage. |

## Add-On: Winner Limits per User

| Requirement | Status | Notes |
| --- | --- | --- |
| Settings UI for Bonushunt/Tournament limits | ⚠️ | Settings page partially implemented; needs UX validation. |
| Rolling-window enforcement when awarding winners | ⚠️ | Logic exists in `BHG_Models::close_hunt()` but requires more robust testing. |
| Win logging with timestamps/user/type | ⚠️ | Logging exists but lacks analytics tooling. |
| Skipped-user notice when limit reached | ⚠️ | Messaging helpers added; confirm admin/front-end visibility. |

## Add-On: Frontend Adjustments

| Requirement | Status | Notes |
| --- | --- | --- |
| Table header links rendered white (#fff) | ⚠️ | CSS adjustments pending confirmation. |
| `bhg_hunts` Details column with Guess Now / Show Results | ⚠️ | Logic wired; QA required. |

## Add-On: Prizes Enhancements

| Requirement | Status | Notes |
| --- | --- | --- |
| Large image upload support (1200×800 PNG) | ⚠️ | Media handling needs validation. |
| Image size labels (Small/Medium/Big) in admin | ⚠️ | UI hints partially implemented. |
| Prize link field and clickable images | ⚠️ | Field exists; verify output. |
| Category management with optional links and visibility toggle | ⚠️ | Data model supports link toggles; admin UI still rough. |
| Image click behavior options (popup / same tab / new tab) | ⚠️ | Settings available; QA pending. |
| Carousel controls: visible count, total load, auto-scroll | ⚠️ | Options stored; ensure front-end respects them. |
| Toggles for prize title/category/description | ⚠️ | Config options exist; verify rendering. |
| Responsive image size rules (1→big, 2–3→medium, 4–5→small) | ⚠️ | Logic needs testing. |
| Remove automatic "Prizes" heading | ⚠️ | Template updated; confirm front-end layout. |
| Dual prize sets (Regular + Premium) for affiliate winners | ⚠️ | Data persisted; acceptance test outstanding. |

## Jackpot Feature (New Module)

| Requirement | Status | Notes |
| --- | --- | --- |
| Admin menu “Jackpots” with CRUD + latest 10 view | ❌ | No jackpot admin screens or tables exist in codebase. |
| Fields: title, start amount, linked hunts (all/selected/by affiliate/by period), increase amount per miss | ❌ | Absent from schema and UI. |
| Logic: detect exact guess hits on hunt close, increase amount otherwise | ❌ | No jackpot handling integrated with hunt closure. |
| Currency uses global setting | ❌ | No jackpot entity to format. |
| Shortcodes `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` | ❌ | Shortcodes not registered. |

## Documentation Follow-Up

1. Update all existing delivery/verification checklists to reference version 8.0.16 instead of 8.0.14.
2. Add end-to-end QA evidence once the remaining ❌/⚠️ items are resolved.
3. Prioritize implementation of the jackpot module and completion of PHPCS compliance across the codebase.

# Bonus Hunt Guesser — Final Verification & Delivery Checklist (v8.0.16)

**Date:** 2024-09-17
**Audience:** Developer • QA • PM
**Scope:** Admin UI, Frontend, DB Migrations, Prizes, Affiliates, Tournaments, Results/Dashboard cards

> **Note on lineage:** This document consolidates and supersedes prior checklists (e.g., 2024-09-16). For historical snapshots, see `docs/final-checklist-20240917.md`.

---

## Status Legend

* ✅ — Requirement fully satisfied and verified
* ⚠️ — Requirement partially satisfied or requires QA/review
* ❌ — Requirement missing or known to be non-compliant

---

## 0) Executive Summary

This is a production-oriented handoff and verification guide for **Bonus Hunt Guesser v8.0.16**. It unifies the functional checklist, capability matrix, schema expectations, QA scenarios, and release readiness steps. Items marked ⚠️/❌ must be addressed before final client acceptance.

---

## 1) Plugin Bootstrap & Tooling

| Requirement                                                | Status | Notes                                                                                  |
| ---------------------------------------------------------- | ------ | -------------------------------------------------------------------------------------- |
| Plugin header matches contract (metadata + version 8.0.16) | ✅      | `bonus-hunt-guesser.php` exposes version 8.0.16 with agreed WP/PHP/MySQL requirements. |
| Text domain loads on `plugins_loaded`                      | ✅      | Uses `load_plugin_textdomain()` during boot.                                           |
| PHPCS (WordPress Core/Docs/Extra) passes with no errors    | ❌      | Repo-level run fails due to legacy spacing/indentation in admin/controllers/tests.     |

---

## 2) Dashboard — “Latest Hunts” Card

| Requirement                                                                                                | Status | Notes                                                                      |
| ---------------------------------------------------------------------------------------------------------- | ------ | -------------------------------------------------------------------------- |
| Card lists latest **3 hunts** with: Title, Winners (+guess/+diff), Start Balance, Final Balance, Closed At | ⚠️     | Template outputs fields; verify with live data and edge cases (0–3 hunts). |
| Each winner has its own row with **bold username**                                                         | ⚠️     | Logic present; confirm final typography against theme CSS.                 |
| Start/Final balance are **left-aligned**                                                                   | ⚠️     | Defaults left; check for theme overrides.                                  |

**Data sources & rules**

* Hunts: `bhg_bonus_hunts` (sort by `closed_at` DESC; fallback to `created_at` when open).
* Winners: join `bhg_winners` by `hunt_id`; include `guess` and computed `diff` vs actual.

**Edge cases**

* No hunts → “No hunts available.”
* Open hunts (no `final_balance`, `closed_at`) → show `—`.

---

## 3) Bonus Hunts (Admin List / Edit / Results)

### 3.1 Admin List

| Requirement                                                                       | Status | Notes                                            |
| --------------------------------------------------------------------------------- | ------ | ------------------------------------------------ |
| Columns: **Final Balance** (`—` if open) and **Affiliate**                        | ⚠️     | DB + render wired; verify formatting for null/0. |
| Row actions: **Edit**, **Results**, **Admin Delete**, **Enable/Disable Guessing** | ⚠️     | Confirm nonces, redirects, capability checks.    |
| Sorting & pagination (≈30/page)                                                   | ⚠️     | Confirm stable sort for open/closed hunts.       |

### 3.2 Edit Screen

| Requirement                                                              | Status | Notes                                                                 |
| ------------------------------------------------------------------------ | ------ | --------------------------------------------------------------------- |
| Tournament multiselect limited to **active** tournaments                 | ✅      | Query filters `active = 1`.                                           |
| **Winners count** configurable & persisted                               | ✅      | Field `winners_count` in hunts table.                                 |
| Participants list with **remove** action + profile links                 | ⚠️     | Verify capability `manage_options` (or custom cap), nonce, audit log. |
| Fields validated/sanitized (title, balances, dates, guessing, affiliate) | ⚠️     | Review validators and autosave behavior.                              |

### 3.3 Results View

| Requirement                                                      | Status | Notes                                                         |
| ---------------------------------------------------------------- | ------ | ------------------------------------------------------------- |
| Defaults to **latest closed hunt**; selectors for timeframe/hunt | ⚠️     | Implemented in `bonus-hunts-results.php`; validate data load. |
| Empty state message                                              | ✅      | “There are no winners yet.”                                   |
| Time filter: **This Month (default) / This Year / All Time**     | ⚠️     | Confirm month/year boundaries (WP timezone).                  |
| Winners highlighted (green + bold), zebra rows, **Prize** column | ⚠️     | CSS exists; check for theme collisions.                       |

**Ranking rules**

* Rank by minimal absolute `diff`; tie-break by earlier `guess_time`.

---

## 4) Tournaments (Admin)

| Requirement                                                       | Status | Notes                                 |
| ----------------------------------------------------------------- | ------ | ------------------------------------- |
| Fields: **Title**, **Description**                                | ✅      | Standard edit UI.                     |
| Types include **Quarterly** / **All-time**; legacy period removed | ⚠️     | Verify safe migration of legacy data. |
| Participants mode toggle (**Winners Only** / **All Guessers**)    | ✅      | Stored in `participants_mode`.        |
| Actions: **Edit**, **Results**, **Close**, **Admin Delete**       | ⚠️     | Ensure proper caps + nonces.          |
| DB column `participants_mode`                                     | ✅      | Added via migrations.                 |

---

## 5) Users (Admin)

| Requirement                                               | Status | Notes                             |
| --------------------------------------------------------- | ------ | --------------------------------- |
| Search by username/email                                  | ✅      | `WP_User_Query` integration.      |
| Sortable columns                                          | ⚠️     | Verify keys & directions.         |
| Pagination (30/page)                                      | ✅      | Consistent with other lists.      |
| Profile shows **affiliate toggles per affiliate website** | ⚠️     | Confirm persistence and defaults. |

---

## 6) Affiliates (Sync & Frontend)

| Requirement                                                  | Status | Notes                                            |
| ------------------------------------------------------------ | ------ | ------------------------------------------------ |
| Adding/removing affiliate websites syncs user profile fields | ⚠️     | Exercise add/edit/remove; verify orphan cleanup. |
| Frontend affiliate “lights” + optional website label         | ✅      | Shortcodes render colored dot + label.           |

**Sync rules**

* Adding/removing affiliates must mirror in user meta.
* Deleting an affiliate removes related user meta.

---

## 7) Prizes (Admin + Frontend + Shortcodes)

| Requirement                                                  | Status | Notes                                    |
| ------------------------------------------------------------ | ------ | ---------------------------------------- |
| CRUD: title, description, category, image, CSS class, active | ✅      | Managed via `BHG_Prizes`.                |
| **Dual prize sets** (regular + premium) selectable per hunt  | ✅      | Persisted and retrievable.               |
| Affiliate winners see **premium prize set above** regular    | ⚠️     | Frontend conditional display; needs UAT. |
| Three image sizes (small/medium/big) incl. 1200×800 PNG      | ⚠️     | Validate upload rules & rendering.       |
| Frontend grid/carousel with dots/arrows and fallback         | ⚠️     | Cross-device QA outstanding.             |
| Shortcode `[bhg_prizes]` (category, design, size, active)    | ⚠️     | Option parsing implemented; add tests.   |

---

## 8) Shortcodes Catalog & Core Pages

| Requirement                                                                           | Status | Notes                                 |
| ------------------------------------------------------------------------------------- | ------ | ------------------------------------- |
| Admin **Info & Help** lists all shortcodes w/ examples                                | ⚠️     | Expand documentation coverage.        |
| Existing shortcodes remain supported (`[bhg_user_profile]`, `[bhg_guess_form]`, etc.) | ✅      | No regressions detected.              |
| `[bhg_user_guesses]`: **difference** column after final balance                       | ⚠️     | Verify formatting.                    |
| `[bhg_hunts]`: **winners count** + **Details** column (Guess Now/Show Results)        | ⚠️     | Ensure URLs valid.                    |
| `[bhg_tournaments]`: updated columns & naming                                         | ⚠️     | Cross-check type removal.             |
| `[bhg_leaderboards]`: Times Won, Avg positions (hunt/tournament)                      | ⚠️     | Calculations present; add unit tests. |
| `[bhg_advertising]`: `placement="none"` for shortcode-only                            | ✅      | Admin & rendering support.            |
| Required pages auto-created with override metabox                                     | ⚠️     | Confirm on activation.                |

---

## 9) Notifications

| Requirement                                                                          | Status | Notes                      |
| ------------------------------------------------------------------------------------ | ------ | -------------------------- |
| Winners/Tournament/Bonushunt blocks with Title, HTML Description, BCC, enable toggle | ⚠️     | Settings exist; verify UI. |
| Notifications use `wp_mail()` with BCC honored                                       | ✅      | BCC headers handled.       |

---

## 10) Ranking & Points

| Requirement                                           | Status | Notes                                   |
| ----------------------------------------------------- | ------ | --------------------------------------- |
| Editable default mapping (25/15/10/5/4/3/2/1)         | ⚠️     | Settings stored; needs admin QA.        |
| Scope toggle (active/closed/all hunts)                | ⚠️     | Validate calculations.                  |
| Only winners accrue points                            | ⚠️     | Logic present; add regression coverage. |
| Backend + frontend rankings highlight winners + Top 3 | ⚠️     | Styling exists; needs UX sign-off.      |
| Centralized service + unit tests                      | ⚠️     | Tests limited; extend.                  |

---

## 11) Global CSS / Color Panel

| Requirement                                                     | Status | Notes                                      |
| --------------------------------------------------------------- | ------ | ------------------------------------------ |
| Global typography and color controls apply to shared components | ⚠️     | Settings persisted; verify FE application. |

---

## 12) Currency System

| Requirement                                                        | Status | Notes                                 |
| ------------------------------------------------------------------ | ------ | ------------------------------------- |
| Setting `bhg_currency` (EUR/USD) stored                            | ✅      | Option available in settings.         |
| Helpers `bhg_currency_symbol()` & `bhg_format_money()` implemented | ✅      | Defined in bootstrap.                 |
| All monetary outputs use helpers                                   | ⚠️     | Audit for any direct formatting left. |

---

## 13) Database & Migrations (Conceptual)

> Concrete SQL in `BHG_DB::create_tables()`. Idempotent checks for tables/columns/indexes are required.

### 13.1 Tables & Key Columns

* **`bhg_bonus_hunts`** — `id`, `title`, `start_balance` DECIMAL, `final_balance` DECIMAL NULL, `closed_at` DATETIME NULL, `guessing_enabled` TINYINT, `affiliate_id` INT NULL, `winners_count` INT, timestamps.
* **`bhg_guesses`** — `id`, `hunt_id`, `user_id`, `guess_value` DECIMAL, `guess_time` DATETIME, `affiliate_id` INT NULL.
* **`bhg_winners`** — `id`, `hunt_id`, `user_id`, `rank`, `actual_value` DECIMAL, `guess_value` DECIMAL, `diff_value` DECIMAL, `prize_id`.
* **`bhg_tournaments`** — `id`, `title`, `description`, `type` ENUM(`quarterly`,`all_time`), `participants_mode` ENUM(`winners_only`,`all_guessers`), `active` TINYINT, timestamps.
* **`bhg_prizes`** — `id`, `title`, `description`, `category`, `image_url`, `css_class`, `active` TINYINT, timestamps.
* **`bhg_hunt_prizes`** — `id`, `hunt_id`, `prize_id`, `tier` ENUM(`regular`,`premium`), `rank_from`, `rank_to`.
* **`bhg_affiliates`** — `id`, `name`, `website`, `active` TINYINT, timestamps.

### 13.2 Indexing & Performance

* Index: `bhg_bonus_hunts.closed_at`, `bhg_guesses.hunt_id`, `bhg_guesses.user_id`, `bhg_winners.hunt_id`, `bhg_winners.user_id`.
* Avoid N+1 by preloading winners/prizes for visible hunts.

---

## 14) Frontend Rendering Rules

* **Winners (Results):** Bold username; green highlight for winners; zebra rows (`.row-alt`). Columns: Username, Guess, Actual, Diff, Prize. If winner is affiliate-associated **and** hunt has premium prizes → show **premium** prize block above regular.
* **Latest Hunts Card:** Max **3** items; Title (link to results); list winners `(guess / ±diff)`; balances; closed timestamp (or `—` if open).

---

## 15) Validation, Sanitization & Security

* **Admin Inputs:** `sanitize_text_field`, numeric casting for balances/IDs; verify nonces on all mutating actions.
* **Escaping on output:** `esc_html`, `esc_attr`, `esc_url`; `wp_kses_post` for rich descriptions.
* **Timezones:** Normalize to WP timezone for queries/display.
* **Capabilities (suggested):**

  * View Hunts Admin — `manage_options` or custom `bhg_manage`
  * Edit Hunt — `manage_options` or `bhg_edit_hunt`
  * Delete Hunt — `manage_options` (soft-delete recommended)
  * Toggle Guessing — `manage_options`
  * Manage Tournaments/Prizes/Affiliates — `manage_options`

---

## 16) Accessibility & i18n

* **A11y:** `<th scope="col">` on table headers, focus states on actions, adequate contrast for highlights.
* **i18n:** Wrap strings with `__()`/`_e()` using text domain `'bonushuntguesser'`.
* **RTL:** Validate alignment and icon direction.

---

## 17) Performance & Reliability

* Use indexes above; consider transient caching for dashboard card (~60s).
* Keep admin lists ≤200ms on ~5k records; paginate at 30 rows.
* Emit `WP_Error` on failures; surface admin notices on user actions.

---

## 18) QA & Acceptance Tests

### 18.1 Dashboard Card

1. No hunts → “No hunts available.”
2. Only open hunts → Final/Closed show `—`.
3. Mixed states → Closed hunts sorted latest first.
4. Winner rows → Bold usernames; `(guess / ±diff)` formatting correct.

### 18.2 Admin Hunts

1. List columns show `—` when open; Affiliate label correct.
2. Row actions respect caps/nonces and redirect properly.
3. Edit → `winners_count` persists; tournament multiselect shows only active.
4. Participants remove flow enforces caps/nonces; audit entry written (if enabled).

### 18.3 Results View

1. Default loads latest closed hunt.
2. Time filter boundaries correct (month/year).
3. Styling: green highlights + zebra rows; Prize column populated when mapped.

### 18.4 Tournaments

1. Types: Quarterly/All-time selectable; legacy removed.
2. Participants Mode respected in aggregation.

### 18.5 Prizes

1. CRUD with image + CSS class; inactive prizes not assignable.
2. Dual sets (premium + regular) saved and mapped to ranks.
3. Affiliate view shows premium-first when applicable.

### 18.6 Affiliates

1. Sync user meta on add/remove.
2. Frontend dot + optional website label display correctly.

### 18.7 Currency

1. Switching EUR/USD reflects across admin/frontend consistently.

---

## 19) Global UX Guarantees

| Requirement                                                | Status | Notes                         |
| ---------------------------------------------------------- | ------ | ----------------------------- |
| Sorting, search, pagination (~30/page) across admin tables | ⚠️     | QA per screen.                |
| Timeline filters (This Week/Month/Year/Last Year/All-Time) | ⚠️     | Validate data queries.        |
| Affiliate lights & website display                         | ✅      | Shortcodes render indicators. |
| Profile blocks show real name, email, affiliate            | ⚠️     | Confirm accuracy.             |

---

## 20) Add-Ons

### 20.1 Winner Limits per User

| Requirement                                    | Status | Notes                                              |
| ---------------------------------------------- | ------ | -------------------------------------------------- |
| Settings UI for Bonushunt/Tournament limits    | ⚠️     | Needs UX validation.                               |
| Rolling-window enforcement on awarding winners | ⚠️     | Logic in `BHG_Models::close_hunt()`; expand tests. |
| Win logging (timestamp/user/type)              | ⚠️     | Present; add analytics tools.                      |
| Skipped-user notice when limit reached         | ⚠️     | Verify admin/frontend visibility.                  |

### 20.2 Frontend Adjustments

| Requirement                                           | Status | Notes                            |
| ----------------------------------------------------- | ------ | -------------------------------- |
| Table header links are white (`#fff`)                 | ⚠️     | CSS update pending confirmation. |
| `[bhg_hunts]` Details column (Guess Now/Show Results) | ⚠️     | Logic wired; verify links.       |

### 20.3 Prizes Enhancements

| Requirement                                                | Status | Notes                                    |
| ---------------------------------------------------------- | ------ | ---------------------------------------- |
| Large image upload support (1200×800 PNG)                  | ⚠️     | Validate.                                |
| Image size labels (Small/Medium/Big) in admin              | ⚠️     | UI hints partial.                        |
| Prize link field + clickable images                        | ⚠️     | Field exists; confirm FE output.         |
| Category management: links + visibility toggle             | ⚠️     | Model supports; admin UI rough.          |
| Image click behavior (popup/same tab/new tab)              | ⚠️     | Settings present; QA pending.            |
| Carousel controls: visible count, total load, auto-scroll  | ⚠️     | Ensure FE respects options.              |
| Toggles for prize title/category/description               | ⚠️     | Confirm rendering.                       |
| Responsive image size rules (1→big, 2–3→medium, 4–5→small) | ⚠️     | Needs testing.                           |
| Remove automatic “Prizes” heading                          | ⚠️     | Template updated; verify FE layout.      |
| Dual prize sets for affiliate winners                      | ⚠️     | Data persisted; acceptance test pending. |

---

## 21) Jackpot Feature (New Module)

| Requirement                                                                                                  | Status | Notes                  |
| ------------------------------------------------------------------------------------------------------------ | ------ | ---------------------- |
| Admin menu “Jackpots” with CRUD + latest 10 view                                                             | ❌      | Not implemented.       |
| Fields: title, start amount, linked hunts (all/selected/by affiliate/by period), increase per miss           | ❌      | Missing schema/UI.     |
| Logic: exact-guess detection on hunt close; increase otherwise                                               | ❌      | Not integrated.        |
| Currency follows global setting                                                                              | ❌      | No entity implemented. |
| Shortcodes: `[bhg_jackpot_current]`, `[bhg_jackpot_latest]`, `[bhg_jackpot_ticker]`, `[bhg_jackpot_winners]` | ❌      | Not registered.        |

---

## 22) Documentation Follow-Up

1. Update all checklists/docs to **v8.0.16** (remove `8.0.14` references).
2. Capture E2E QA evidence once ❌/⚠️ items are resolved.
3. Prioritize **Jackpot module** and **PHPCS** cleanup across codebase.
4. Expand Admin “Info & Help” with complete shortcode catalog/examples.

---

## 23) Release Checklist

1. **DB migrations** pass on clean & upgrade installs (idempotent guards).
2. **Capabilities** enforced for all actions (no reliance on `is_admin()` alone).
3. **Nonces** on all mutating endpoints (incl. GET actions like delete/toggle).
4. **UI pass** against target theme(s) for tables/highlights/spacing.
5. **Performance**: Admin lists ≤200ms on ~5k rows with indexes.
6. **Accessibility**: Axe scan; contrast pass for highlights.
7. **i18n**: POT regenerated; missing strings wrapped.
8. **Docs**: Commit this file as `docs/verification-checklist.md`.
9. **Changelog** updated (see template).
10. **Tag release** and prepare rollback plan.

---

## 24) Changelog Template (v8.0.16)

```
## [8.0.16] - 2024-09-17
### Added
- Tournament types: Quarterly, All-time.
- Dual prize sets (Regular + Premium) with affiliate-aware display.
- Admin dashboard "Latest Hunts" card (top 3).

### Changed
- `[bhg_hunts]` adds Details column (Guess Now / Show Results).
- Currency helpers centralized; EUR/USD setting applied broadly.

### Fixed
- Results view defaults to latest closed hunt; improved empty state.
- Participants search and admin list pagination consistency.

### Pending (Not in this tag)
- Jackpot module (CRUD, logic, shortcodes).
- Full PHPCS compliance across legacy files.
```

---

## 25) File Placement

* Save this document as: **`docs/verification-checklist.md`**
* Archive the historical snapshot at: **`docs/final-checklist-20240917.md`**

---
