# Bonus Hunt Guesser — v8.0.14 Delivery Report

**Legend:** ✅ Pass · ❌ Fail · ⚠️ Needs follow-up · ⬜ Not tested

Each checklist item below includes the observed status, supporting evidence, and the files that must be updated (when applicable). Paths are relative to the project root.

## 0) Plugin Header & Standards
| Requirement | Status | Findings |
| --- | --- | --- |
| Header values match spec | ✅ | `Requires at least` now reports `6.3.0` alongside the agreed PHP and MySQL requirements. |【F:bonus-hunt-guesser.php†L3-L13】|
| Header values match spec | ✅ | `Requires at least` now reports `6.3.5` alongside the agreed PHP and MySQL requirements. |【F:bonus-hunt-guesser.php†L3-L13】|
| Text domain loads correctly | ✅ | `load_plugin_textdomain( 'bonus-hunt-guesser', … )` executes during `plugins_loaded`. |【F:bonus-hunt-guesser.php†L385-L404】|
| PHPCS (WordPress standard) passes | ❌ | `vendor/bin/phpcs --standard=WordPress bonus-hunt-guesser.php` reports 800+ violations (indentation, unsanitized input). |【aef0a5†L1-L120】|

## 1) BHG Dashboard (winners list)
| Requirement | Status | Findings |
| --- | --- | --- |
| Each winner appears on a separate row | ✅ | The Latest Hunts table renders every winner inside its own `<li>` chip within the winners cell. |【F:admin/views/dashboard.php†L117-L197】|
| Winner username is bold | ✅ | Winner chips apply `.bhg-winner-chip__name { font-weight: 600; }` via admin CSS. |【F:assets/css/admin.css†L465-L485】【F:assets/css/admin.css†L477-L479】|

## 2) `bhg-bonus-hunts` (Add/Edit)
| Requirement | Status | Findings |
| --- | --- | --- |
| Tournament multiselect shows only active tournaments | ✅ | Add/Edit forms pull tournaments with `WHERE status = 'active'`, keeping previously linked IDs visible. |【F:admin/views/bonus-hunts.php†L395-L415】【F:admin/views/bonus-hunts.php†L520-L567】|
| “Results” button lists ranked guesses for closed hunts | ✅ | Closed hunts expose a Results button that links to the ranked results page. |【F:admin/views/bonus-hunts.php†L236-L264】|
| Winner count configurable (1–25) | ✅ | Hunt form provides `<input type="number" min="1" max="25" name="winners_count">`. |【F:admin/views/bonus-hunts.php†L432-L435】【F:admin/views/bonus-hunts.php†L552-L559】|
| Edit view lists participants with removal + profile links | ✅ | Participant table links usernames to `user-edit.php` and includes nonce-protected delete forms. |【F:admin/views/bonus-hunts.php†L608-L648】|
| Final Balance column shows “—” while open | ✅ | Listing outputs an em dash when `final_balance` is null. |【F:admin/views/bonus-hunts.php†L238-L245】|

## 3) `bhg-bonus-hunts-results`
| Requirement | Status | Findings |
| --- | --- | --- |
| Row colours consistent (grey/white) | ✅ | Results tables use the core `widefat striped` styling for alternating grey/white rows. |【F:admin/views/bonus-hunts-results.php†L164-L205】|
| Winners highlighted (green + bold) | ✅ | Winner rows receive the `.bhg-results-row--winner` class, which applies green accent styling. |【F:admin/views/bonus-hunts-results.php†L208-L234】【F:assets/css/admin.css†L128-L152】|
| “Price” column present | ✅ | Results table includes a “Price” header and prints prize titles (or em dash) per ranked entry. |【F:admin/views/bonus-hunts-results.php†L200-L241】|

## 4) `bhg-tournaments` (Connected Bonus Hunts)
| Requirement | Status | Findings |
| --- | --- | --- |
| Selector shows this year’s hunts + already linked ones | ✅ | Hunt picker filters by current year and appends previously connected IDs so legacy links stay visible. |【F:admin/views/tournaments.php†L60-L119】【F:admin/views/tournaments.php†L479-L489】|

## 5) `bhg-users` (User Profile Affiliates)
| Requirement | Status | Findings |
| --- | --- | --- |
| Affiliate yes/no field created per affiliate website | ✅ | User profile screen renders a global affiliate checkbox plus one checkbox per affiliate site pulled from `bhg_affiliate_websites`. |【F:bonus-hunt-guesser.php†L1530-L1596】|

## 6) `bhg-affiliates` (Sync to User Profile)
| Requirement | Status | Findings |
| --- | --- | --- |
| Adding an affiliate website adds corresponding user field | ✅ | The affiliate admin list creates new rows, and the profile editor loops through all sites to display matching checkboxes. |【F:admin/views/affiliate-websites.php†L24-L118】【F:bonus-hunt-guesser.php†L1530-L1596】|
| Removing an affiliate website removes the field from profiles | ⚠️ | Deleting a site removes it from the admin list, but stored user meta persists; consider clearing `bhg_affiliate_websites` entries tied to the deleted site. |【F:admin/class-bhg-admin.php†L1230-L1263】|

## 7) `bhg-prizes` (Admin)
| Requirement | Status | Findings |
| --- | --- | --- |
| “Prizes” menu exists with CRUD | ✅ | Submenu `bhg-prizes` renders the prize table with Add/Edit/Delete actions. |【F:admin/class-bhg-admin.php†L63-L64】【F:admin/views/prizes.php†L23-L119】|
| Required fields (title, description, category, image sizes) present | ✅ | Modal form captures text fields, category select, and three media pickers for small/medium/big images. |【F:admin/views/prizes.php†L120-L214】|
| Frontend CSS panel options (border, colour, padding, margin, background) | ✅ | Prize modal exposes CSS settings inputs for border, border color, padding, margin, and background. |【F:admin/views/prizes.php†L215-L256】|
| Active yes/no toggle works | ✅ | Checkbox `name="active"` controls availability state stored with each prize. |【F:admin/views/prizes.php†L205-L206】|

## 8) Prizes in Bonus Hunt (Admin)
| Requirement | Status | Findings |
| --- | --- | --- |
| Can select 1+ prizes when creating/editing a bonus hunt | ✅ | Bonus Hunt forms offer multi-selects for prize IDs populated from the prize catalog. |【F:admin/views/bonus-hunts.php†L418-L429】【F:admin/views/bonus-hunts.php†L563-L574】|

## 9) Prizes — Frontend (Active Hunt)
| Requirement | Status | Findings |
| --- | --- | --- |
| Prizes render on active hunts | ✅ | Active hunt shortcode fetches associated prizes and renders them via `render_prize_section()`. |【F:includes/class-bhg-shortcodes.php†L930-L964】|
| Grid list or horizontal carousel view selectable | ✅ | `render_prize_section()` supports `grid` and `carousel` layouts with navigation UI. |【F:includes/class-bhg-shortcodes.php†L333-L376】|
| Carousel has dots and/or navigation arrows | ✅ | Carousel mode prints previous/next buttons and pagination dots when more than one prize is present. |【F:includes/class-bhg-shortcodes.php†L349-L373】|

## 10) Prizes Shortcode
| Requirement | Status | Findings |
| --- | --- | --- |
| Shortcode supports `category`, `design`, `size`, `active` attributes | ✅ | `[bhg_prizes]` sanitizes the four attributes and filters the prize query accordingly. |【F:includes/class-bhg-shortcodes.php†L2984-L3037】|

## 11) User Shortcodes (Frontend, My Profile)
| Requirement | Status | Findings |
| --- | --- | --- |
| `[my_bonushunts]`, `[my_tournaments]`, `[my_prizes]`, `[my_rankings]` exist | ✅ | Shortcode class registers all four handlers and renders the respective profile tables. |【F:includes/class-bhg-shortcodes.php†L53-L60】【F:includes/class-bhg-shortcodes.php†L3121-L3237】|
| Admin option to hide/show each block | ✅ | Settings screen exposes checkboxes under “My Profile Blocks”, and shortcode rendering checks the enabled flags. |【F:admin/views/settings.php†L121-L141】【F:includes/class-bhg-shortcodes.php†L3180-L3189】|

## 12) Global CSS/Colour Panel
| Requirement | Status | Findings |
| --- | --- | --- |
| Controls for title/H2/H3/description/p/span typography & spacing | ✅ | Settings UI includes grouped inputs for title block, H2, H3, description, and body text typography settings. |【F:admin/views/settings.php†L121-L210】|

## 13) `bhg-shortcodes` (Admin)
| Requirement | Status | Findings |
| --- | --- | --- |
| Shortcodes menu lists all available shortcodes + options | ✅ | Dedicated Shortcodes admin page enumerates each shortcode, attributes, and descriptions. |【F:admin/class-bhg-admin.php†L69-L70】【F:admin/views/shortcodes.php†L1-L160】|

## 14) Notifications (`bhg-notifications`)
| Requirement | Status | Findings |
| --- | --- | --- |
| Winners/Tournament/Bonushunt tabs with enable, BCC, HTML | ✅ | Notifications view renders each section with enable checkbox, subject/body inputs, and BCC textarea. |【F:admin/views/notifications.php†L16-L120】|
| Delivery uses `wp_mail()` with headers (BCC honoured) | ✅ | Notification dispatcher builds headers via `bhg_prepare_notification_headers()` and passes them to `wp_mail()`. |【F:includes/notifications.php†L100-L137】【F:includes/notifications.php†L240-L334】|

## 15) Tournaments — Prizes & Affiliate Fields
| Requirement | Status | Findings |
| --- | --- | --- |
| Admin tournament form exposes prizes & affiliate settings | ✅ | Tournaments form includes multi-select for prizes plus affiliate site dropdown and show/hide checkbox. |【F:admin/views/tournaments.php†L360-L421】|
| Frontend tournament detail shows linked prizes | ❌ | Tournament detail view renders only metadata and standings; no prize list is displayed. |【F:includes/class-bhg-shortcodes.php†L2667-L2769】|
| Show/Hide affiliate URL checkbox affects frontend output | ❌ | `affiliate_url_visible` is stored in the database but never referenced in frontend rendering code. |【0a2e5e†L1-L6】|

## 16) Tournament Ranking (Points System)
| Requirement | Status | Findings |
| --- | --- | --- |
| Points mapping exists and editable with scope toggle | ✅ | Tournament editor exposes per-placement `points_map` inputs and a ranking scope selector (`all/closed/active`). |【F:admin/views/tournaments.php†L360-L388】|
| Rankings aggregate winners from selected hunts | ✅ | `BHG_Models::recalculate_tournament_results()` recalculates wins/points across linked hunts respecting scope & mode. |【F:includes/class-bhg-models.php†L240-L436】|
| Winners highlighted; Top 3 extra highlighted | ❌ | Tournament detail table renders plain rows without any CSS classes to emphasise winners or top-three placements. |【F:includes/class-bhg-shortcodes.php†L2726-L2769】|

## 17) Data Integrity & Performance
| Requirement | Status | Findings |
| --- | --- | --- |
| DB schema migrations up to date (new columns/indexes) | ✅ | Database helper adds the affiliate, prize, and ranking columns with supporting indexes when upgrading. |【F:includes/class-bhg-db.php†L79-L152】【F:includes/class-bhg-db.php†L269-L346】|
| Caching used for heavy admin reads / no redundant queries | ⚠️ | Admin dashboard and list screens fetch counts/results directly on every load without transient/object caching. Consider memoizing repeated summaries. |【F:admin/views/dashboard.php†L18-L66】|
| Uninstall cleanup respects user choice | ✅ | `uninstall.php` only drops tables/options when the “Remove plugin data on uninstall” setting is enabled. |【F:uninstall.php†L11-L36】【F:admin/views/settings.php†L245-L249】|

## 18) Security & i18n
| Requirement | Status | Findings |
| --- | --- | --- |
| Capability checks & nonces on write actions | ⚠️ | Most admin forms include capability checks/nonces, but PHPCS still flags unsanitized `$_POST` usage in the settings handler. |【aef0a5†L60-L110】|
| Escaping/sanitization on output/input | ⚠️ | Many templates escape output, yet PHPCS highlights additional unsanitized inputs (e.g., profile settings arrays) that must be normalised. |【aef0a5†L60-L110】|
| All strings translatable with `bonus-hunt-guesser` domain | ⚠️ | Core screens localise strings, but PHPCS reports residual hard-coded text requiring translation wrappers. |【aef0a5†L1-L40】|

## 19) Backward Compatibility
| Requirement | Status | Findings |
| --- | --- | --- |
| Legacy data/settings migrate cleanly | ⚠️ | Upgrade helpers backfill new columns (winners limit, affiliate fields), but manual verification with legacy datasets is recommended. |【F:includes/class-bhg-db.php†L269-L346】|
| Safe defaults for new options | ✅ | Settings initialisation defines defaults for profile sections and global styles, preventing undefined index notices. |【F:admin/views/settings.php†L112-L120】|

## 20) Release & Documentation
| Requirement | Status | Findings |
| --- | --- | --- |
| Version bumped to 8.0.14 (header/constants) | ✅ | Plugin header and changelog both reflect version 8.0.14. |【F:bonus-hunt-guesser.php†L3-L13】【F:CHANGELOG.md†L9-L20】|
| CHANGELOG updated with 8.0.14 notes | ✅ | Changelog lists the 8.0.14 features (profile shortcodes, points system, affiliate sync, CSS controls, uninstall safety). |【F:CHANGELOG.md†L9-L20】|
| Readme/Admin help updated for new features | ✅ | README documents the `[my_*]` shortcodes and settings, matching the 8.0.14 deliverables. |【F:README.md†L1-L48】|

---

**Outstanding work before delivery**
1. Update the plugin header to report `Requires at least: 6.3.5` per the contract.
2. Resolve PHPCS violations and harden remaining input handling flagged by the coding-standard report.
3. Surface tournament prizes and affiliate URL visibility on the frontend detail view, honouring the “Show affiliate URL” toggle.
4. Add winner/top-three highlighting to tournament standings so rankings meet the visual specification.
5. Review caching opportunities for heavy dashboard/leaderboard queries to satisfy the performance checklist.
6. Optionally purge orphaned user affiliate assignments when an affiliate website is deleted.
