# Bonus Hunt Guesser — v8.0.16 **Deduped** Verification & Implementation Checklist

**Runtime:** PHP 7.4 · WordPress 6.3.5 · MySQL 5.5.5+  
**Standards:** PHPCS (`WordPress-Extra`, `WordPress-Docs`, `WordPress-Core`)  
**Text Domain:** `bonus-hunt-guesser`

## 0) Plugin Header & Bootstrapping

* [ ] Header in `bonus-hunt-guesser.php` matches:
  * Name, URI, Description, **Version 8.0.16**, Author, Text Domain, Domain Path, **Requires PHP 7.4**, **Requires at least WP 6.3.0**, License GPLv2+.
* [ ] Load text domain correctly.
* [ ] PHPCS passes (no errors).

## 1) Admin Dashboard (Latest Hunts)

* [ ] “Latest Hunts” shows latest 3 hunts with columns: Title, All Winners (user + guess + difference), Start Balance, Final Balance, Closed At.
* [ ] Each winner in its own row; usernames bold.
* [ ] Start/Final balance left-aligned.

## 2) Bonus Hunts (List + Edit + Results)

**List:**
* [ ] Columns include Final Balance (– if open) and **Affiliate**.
* [ ] Actions: Edit, Results, **Admin Action (Delete)**, **Enable/Disable Guessing**.

**Edit:**
* [ ] Tournament multiselect shows only active tournaments.
* [ ] Winners count configurable.
* [ ] Below form: participants list with remove action; usernames link to profile edit.

**Results:**
* [ ] Latest closed hunt by default; selector for any hunt or tournament.
* [ ] Empty state: “There are no winners yet”.
* [ ] Time filter: This Month (default) / This Year / All Time.
* [ ] Winners highlighted (green + bold); row colors grey/white; **Price** column present.

**DB:**
* [ ] `guessing_enabled TINYINT(1) DEFAULT 1`, `affiliate_id` FK.

## 3) Tournaments (List + Edit)

* [ ] Fields: Title, Description, Participants Mode (**winners only | all guessers**).
* [ ] Remove legacy “type” field.
* [ ] Actions: Edit, Results, Close, **Admin Action (Delete)**.
* **DB**: [ ] `participants_mode VARCHAR(10) DEFAULT 'winners'`.

## 4) Users (Admin)

* [ ] Search by user/email, sortable table, pagination (30/page).
* [ ] Profile shows affiliate toggles per affiliate website.

## 5) Affiliates (Sync)

* [ ] Adding/removing affiliate websites adds/removes fields on all user profiles.
* [ ] Frontend supports affiliate “lights” and optional website display.

## 6) Prizes (Admin + Frontend + Shortcode)

**Admin:**
* [ ] CRUD with fields: title, description, category (cash money, casino money, coupons, merchandise, various), image.
* [ ] 3 image sizes (small, medium, big).
* [ ] CSS panel: border, border color, padding, margin, background color.
* [ ] Active yes/no.
* [ ] Hunt edit: select 1+ prizes.

**Frontend:**
* [ ] Grid or carousel (dots/arrows, accessible fallback).

**Shortcode:**
* [ ] `[bhg_prizes category="" design="grid|carousel" size="small|medium|big" active="yes|no"]`.

## 7) Shortcodes (Catalog & Pages)

* [ ] Admin “Info & Help” lists **all** shortcodes with options/examples.

**Keep/extend**:
* `[bhg_user_profile]`, `[bhg_active_hunt]`, `[bhg_guess_form]` (supports `hunt_id`), `[bhg_tournaments]`, `[bhg_winner_notifications]`, `[bhg_leaderboards]`.

**Add/extend**:
* `[bhg_user_guesses id="" aff="" website=""]` – If no final balance: rank by time; when final exists include Difference column.
* `[bhg_hunts status="" bonushunt="" website="" timeline=""]` – Includes “Winners (count)” column.
* `[bhg_tournaments status="" tournament="" website="" timeline=""]` – No legacy “Type”; ensure Name shown.
* `[bhg_leaderboards tournament="" bonushunt="" aff="" website="" ranking="1-10" timeline=""]` – Metrics: Times Won, Avg Hunt Pos, Avg Tournament Pos.
* `[bhg_advertising status="" ad=""]` with placement “none” for shortcode-only use.

### Pages to create (with per-page override metabox)
1. **Active Bonus Hunt** — `[bhg_active_hunt]` + `[bhg_guess_form]`
2. **All Bonus Hunts** — `[bhg_hunts …]`
3. **Tournaments** — `[bhg_tournaments …]`
4. **Leaderboards** — `[bhg_leaderboards …]`
5. **User Guesses** — `[bhg_user_guesses …]`
6. **My Profile** — `[bhg_user_profile]` (+ `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, `[my_rankings]`)
7. **Prizes** — `[bhg_prizes …]`
8. **Advertising** — `[bhg_advertising …]`

## 8) Notifications

* [ ] Admin tab with blocks: **Winners / Tournament / Bonushunt** each with Title, HTML Description, **BCC**, **Enable/Disable** (default disabled).
* [ ] Uses `wp_mail()`; BCC honored; filters for headers/subject/message.

## 9) Ranking & Points

* [ ] Editable default mapping: 1st 25, 2nd 15, 3rd 10, 4th 5, 5th 4, 6th 3, 7th 2, 8th 1.
* [ ] Scope toggle: active / closed / all hunts.
* [ ] Only winners get points where applicable.
* [ ] Backend + frontend rankings; winners highlighted; Top 3 extra highlight.
* [ ] Centralized service + unit tests.

## 10) Global CSS/Color Panel

* [ ] Title block controls; H2/H3 controls; Description; p/span controls applied across components.

## 11) Currency System

* [ ] Setting: `bhg_currency` = EUR|USD.
* [ ] Helpers:
```
function bhg_currency_symbol(){ return get_option('bhg_currency','EUR')==='USD' ? '$' : '€'; }
function bhg_format_money($a){ return bhg_currency_symbol().number_format_i18n((float)$a,2); }
```
* [ ] All money outputs use `bhg_format_money()`.

## 12) Database & Migrations

* [ ] Ensure: `guessing_enabled`, `participants_mode`, `affiliate_id`.
* [ ] Junction table for many-to-many **hunt ↔ tournament**.
* [ ] Idempotent `dbDelta()` with keys/indexes, version-gated.

## 13) Security & i18n

* [ ] Caps, nonces, sanitize/validate, escape output.
* [ ] BCC email validation.
* [ ] All strings in `bonus-hunt-guesser` domain.

## 14) Backward Compatibility

* [ ] Legacy data loads; safe defaults for new settings; deprecations mapped.

## 15) Global UX Guarantees (Admin lists & shortcode tables)

* [ ] Sorting, Search, Pagination (30/page).
* [ ] Timeline filters: This Week, This Month, This Year, Last Year, All-Time.
* [ ] Affiliate lights where relevant; show affiliate website if set.
* [ ] Profile blocks display real name, email, affiliate.

## 16) Release & Docs

* [ ] Bump to **8.0.16** everywhere.
* [ ] Changelog with migrations.
* [ ] Readme/Admin “Info & Help” updated; optional screenshots/GIFs.

## 17) QA (Acceptance)

* [ ] E2E: create/close hunts → winners (1–25) → admin/FE confirm highlights & points.
* [ ] Currency switch reflects across admin/FE.
* [ ] Guessing toggle blocks/unblocks form.
* [ ] Participants mode respected in tournament results.
* [ ] Prizes CRUD + FE grid/carousel + CSS panel.
* [ ] Notifications BCC + enable/disable correct.
* [ ] Translations loaded; front-end default strings editable via translations.

---

### Status Ledger

**Already present (v8.0.11):**
* Sorting/Search/Pagination, Timeline filters, Affiliate lights/website display, Basic profile outputs, Base shortcode structures.

**To deliver (v8.0.16):**
* Dashboard “Latest Hunts”, Hunts/Tournaments Admin Action columns + toggles, Results empty state + time filter, Participants mode, Currency system, Prizes module + shortcodes + FE, Notifications tab, Ranking service + tests, DB junction + new cols, Global CSS panel, Pages & overrides.

---

## Add-On: Winner Limits per User (Bonushunts & Tournaments)

**Goal**: Limit how many times a user can be a **winner** in a rolling window; participation and ranking remain unaffected.

**Admin Settings (Settings → Bonus Hunt Limits)**
* Bonushunt: Max wins/user; Rolling period (days); 0 = disabled.
* Tournament: Max wins/user; Rolling period (days); 0 = disabled.

**Behavior & Logic**
* On awarding, check wins in rolling window; skip ineligible users and award to next eligible.
* Rankings still count placements even if not awarded again within the limit.

**Data/Tracking**
* Log: user_id, type, context_id, timestamp.

**Edge Cases**
* 0 → unlimited. Bulk awards respect current log.
* Only “winner” state is blocked; participation unaffected.

**Deliverables**
* Settings UI, backend enforcement, win logs, customizable skip message.

## ✅ Add-On: Frontend Adjustments

**General Frontend**
* [ ] Table header links are white (`#fff`) across frontend tables.

**`bhg_hunts` list**
* [ ] Add **Details** column (next to **Status**):
  * Closed → **Show Results** (link to hunt results).
  * Open → **Guess Now** (link to guess form).

## ✅ Add-On: Prizes Enhancements

**Image Handling**
* [ ] Accept large images (1200×800 PNG).
* [ ] Show image size labels (Small 300×200, Medium 600×400, Big 1200×800).

**Prize Links & Categories**
* [ ] Add optional **Prize Link** (clickable image if present).
* [ ] Category management: name, custom link (optional), show/hide link toggle (controls clickability).

**Click Behavior**
* [ ] Options: popup large image / open link same window / open link new window.

**Carousel / Grid Controls**
* [ ] Visible images in carousel, total to load, auto-scroll on/off.
* [ ] Toggles: show/hide Title, Category, Description.

**Responsive Display**
* [ ] 1 image → Big; 2–3 → Medium; 4–5 → Small.

**Frontend Clean-Up**
* [ ] Remove automatic “Prizes” heading.

**NEW: Dual Prize Sets**
* [ ] Bonus Hunt admin: two selectors—Regular Prize Set & Premium Prize Set.
* [ ] Affiliate winners see Premium set (above Regular); non-affiliate winners see Regular only.

## 🎰 Jackpot Feature (Separate Order)

**Admin**
* [ ] Menu: **Jackpots**; fields include Title, Start amount, Linked hunts (all/selected/by affiliate/by time), Increase per miss.
* [ ] List: Title, Start Date, Start Amount, Current Amount, Status.

**Logic**
* [ ] On hunt close: exact guess → jackpot hit; else increase by configured amount.

**Shortcodes**
* [ ] `[bhg_jackpot_current id=""]`
* [ ] `[bhg_jackpot_latest]`
* [ ] `[bhg_jackpot_ticker mode="amount|winners"]`
* [ ] `[bhg_jackpot_winners layout="list|table"]` with options to show/hide date, name, title, amount, affiliate website.
