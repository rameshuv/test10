# Bonus Hunt Guesser v8.0.14 Delivery Checklist

> **Environment targets:** PHP 7.4 · WordPress 6.3.0 · MySQL 5.5.5+
> **Standards:** PHPCS (`WordPress-Extra`, `WordPress-Docs`, `WordPress-Core`)
> **Text Domain:** `bonus-hunt-guesser`

This checklist consolidates all customer-approved functionality and QA expectations for the Bonus Hunt Guesser plugin. Use it when preparing a release, performing QA, or validating that custom requirements have been met. Mark each item when the condition has been verified in the stated environment.

---

## 0. Plugin Bootstrap & Tooling

- [ ] **Plugin header** in `bonus-hunt-guesser.php` reflects version 8.0.14 and required metadata (URI, description, author, text-domain, domain path, min WP/PHP/MySQL, license).
- [ ] **Text domain** is loaded with `load_plugin_textdomain( 'bonus-hunt-guesser', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );` and all strings use the matching domain.
- [ ] **Activation/Deactivation/Uninstall hooks** register and clean up options/tables safely (no fatal errors on activation).
- [ ] **Composer autoloader** (if used) is dumped and included; third-party libraries are licensed appropriately.
- [ ] **PHPCS** passes with the configured WordPress standard (warnings acceptable if documented rationale).
- [ ] **Unit/integration tests** (PHPUnit) pass, or failing tests are documented with remediation steps.

---

## 1. Admin Dashboard (`bhg`)

- [ ] WP Admin menu label is **“Dashboard”** (not “Bonushunt”).
- [ ] Dashboard shows **Latest Hunts** table (3 most recent hunts).
- [ ] Table columns: Hunt Title, Winners (all ranked winners with guess + difference), Start Balance, Final Balance, Closed At.
- [ ] Supports up to **25 winners** per hunt in the dashboard view.
- [ ] Balances align left and are formatted with the configured currency symbol.

---

## 2. Bonus Hunts Admin (`bhg-bonus-hunts`)

- [ ] List table includes sortable columns, search box, pagination (30/page), and affiliate column between Final Balance and Winners.
- [ ] **Actions column:** Edit, Close/Results, Toggle Guessing, and other relevant actions.
- [ ] **Admin Action column:** Contains Delete button (with nonce & capability checks) isolated from other actions.
- [ ] Results button opens ranked guesses view with winners highlighted.
- [ ] Final Balance column shows numeric value or `-` for open hunts.
- [ ] Add/Edit screen captures: title, start balance, final balance, bonuses, winners count (configurable), guess toggle, affiliate dropdown, prize multiselect, connected tournaments, and CSS panel settings.
- [ ] Guessing toggle available both in list actions and edit screen; disabled hunts block frontend submissions.
- [ ] Edit screen displays participant list with ability to remove guesses; usernames link to user profiles.
- [ ] Prize associations persist and sync with frontend displays.

---

## 3. Bonus Hunt Results (`bhg-bonus-hunts-results`)

- [ ] Defaults to latest hunt results; dropdown allows selecting any hunt or tournament.
- [ ] Includes timeline filter (This Month, This Year, All Time).
- [ ] If no winners exist, displays “There are no winners yet” notice and hides table.
- [ ] Results table shows ranked list (best to worst), highlights winners, and respects configured winner count.
- [ ] Supports CSV export or print (if promised) and honors search/pagination (30/page).

---

## 4. Tournaments Admin (`bhg-tournaments`)

- [ ] List table has sortable columns, search, pagination (30/page), affiliate column, Actions (Edit, Close, Results), and Admin Action (Delete).
- [ ] Add/Edit form includes title, description, start/end dates, affiliate selection, participants mode (winners only/all guessers), connected hunts (auto/manual options), prizes, CSS settings, and affiliate URL visibility toggle.
- [ ] “Type” field removed; timeline logic driven by date ranges.
- [ ] Editing tournaments works (form pre-populated; save updates rows).
- [ ] Many-to-many relationships between hunts and tournaments persist via junction table.

---

## 5. Users Admin (`bhg-users`)

- [ ] Table supports search (name/email), sortable columns, pagination (30/page).
- [ ] Displays affiliate fields per affiliate site (yes/no toggles) with ability to edit.
- [ ] Links to standard WP user edit screen and custom profile enhancements.
- [ ] Data integrity: removing affiliate sites cleans user meta fields.

---

## 6. Affiliates (`bhg-affiliates`)

- [ ] CRUD works for affiliate websites; deletion cleans user/profile references.
- [ ] Adding affiliate auto-creates user profile toggles.
- [ ] Placement in hunts/tournaments respected; show/hide toggles work on frontend outputs.

---

## 7. Prizes Module (`bhg-prizes`)

- [ ] Admin menu exposes Prizes list with Add/Edit/Delete actions.
- [ ] Fields: title, description, category (cash money, casino money, coupons, merchandise, various), image upload.
- [ ] Image sizes generated: small, medium, big.
- [ ] CSS panel options available (border, color, padding, margin, background).
- [ ] Active toggle (yes/no) controls availability and surfaces in hunts/tournaments selection.
- [ ] Frontend active hunts can display prizes in grid or carousel layout.
- [ ] Carousel includes dots and/or left/right navigation; responsive and accessible.
- [ ] Shortcode `[bhg_prizes]` filters by category, design, size, active state.

---

## 8. Shortcodes Overview (`bhg-shortcodes` admin + frontend)

- [ ] Admin Shortcodes page lists each shortcode, attributes, and sample usage under “Info & Help”.
- [ ] Existing shortcodes functional: `[bhg_user_profile]`, `[bhg_active_hunt]`, `[bhg_guess_form]`, `[bhg_guess_form hunt_id="..."]`, `[bhg_tournaments]`, `[bhg_winner_notifications]`, `[bhg_leaderboard]`.
- [ ] New shortcodes implemented:
  - `[bhg_user_guesses id="" aff="" website=""]`
  - `[bhg_hunts status="" bonushunt="" website="" timeline=""]`
  - `[bhg_tournaments status="" tournament="" website="" timeline=""]`
  - `[bhg_leaderboards tournament="" bonushunt="" aff="" website="" ranking="" timeline=""]`
  - `[bhg_advertising status="" ad=""]`
  - `[bhg_profile]` (extended profile block with real name, email, affiliate info).
- [ ] Shortcode tables support sorting, search, pagination (30/page), timeline filters (This Week/Month/Year/Last Year/All-Time).
- [ ] Affiliate indicators (green/red lights) present where specified, with affiliate website titles shown when available.
- [ ] Active hunt shortcode provides dropdown when multiple hunts are open, includes hunt details, guesses, pagination, and respects currency symbol.
- [ ] Guess form shortcode respects multiple active hunts selector, redirect URL setting, and dynamic button text (Submit vs. Edit Guess).
- [ ] Leaderboards shortcode outputs Times Won, Average Hunt Position, Average Tournament Position metrics with filter controls.

---

## 9. Notifications (`bhg-notifications`)

- [ ] Admin tab exists with blocks for Winners, Tournament, and Bonushunt notifications.
- [ ] Each block includes editable title, HTML description, BCC field, enable/disable checkbox (default off).
- [ ] Emails send via `wp_mail()` with proper headers/escaping; BCC honored.
- [ ] Hooks/filters expose overrides for subject/body/headers.

---

## 10. Ads Module (`bhg-ads`)

- [ ] List table has Actions (Edit, Remove) plus Admin Action (Delete if separate) and supports pagination/search/sort.
- [ ] Placement dropdown includes “none” for shortcode-only placements.
- [ ] Ads respect login/affiliate visibility rules and appear in configured locations/shortcodes.

---

## 11. Translations & Tools (`bhg-translations`, `bhg-tools`)

- [ ] Translations page pre-populates front-end display strings for editing.
- [ ] Tools section lists available data/export/import utilities or indicates intentionally empty state with helper text.

---

## 12. Notifications & Points Logic

- [ ] Winner determination based on guess proximity to final balance (ties handled as specified).
- [ ] Points system editable: defaults 25/15/10/5/4/3/2/1, scope toggles for active/closed/all hunts.
- [ ] Tournament and Bonus Hunt rankings use points logic, highlighting winners and top 3.
- [ ] Data recalculates when winners/guesses updated; caches invalidated.

---

## 13. Currency & Formatting

- [ ] Settings page offers currency selection (€ / $) stored as option.
- [ ] All monetary outputs use helper (e.g., `bhg_format_money()`) respecting currency and localization.
- [ ] Admin and frontend tables align currency values consistently (left alignment per request).

---

## 14. Security, Performance, Compatibility

- [ ] Capability checks guard all admin pages/actions (`manage_options` or custom caps).
- [ ] Nonces verify all state-changing requests; actions validate current user role.
- [ ] Inputs sanitized/validated (`sanitize_text_field`, `absint`, etc.) and outputs escaped (`esc_html`, `esc_attr`, `wp_kses_post`).
- [ ] Database schema migrations up to date; indexes added for frequently queried columns.
- [ ] Avoid N+1 queries; use caching/transients where appropriate.
- [ ] Compatible with PHP 7.4, WordPress 6.3.0, MySQL 5.5.5+ (no deprecated API usage).

---

## 15. Release Management

- [ ] Version bumped to 8.0.14 in plugin header, constants, and documentation.
- [ ] CHANGELOG/readme updated with new features, fixes, and upgrade notes.
- [ ] Screenshots/GIFs refreshed for new UI where applicable.
- [ ] Final sanity checks: activation, upgrade path, uninstall cleanup, no fatal PHP errors.

---

### Sign-off

Use the space below to capture final reviewer notes, outstanding issues, or follow-up tasks before shipping the release.

- **Reviewer:** ____________________
- **Date:** ____________________
- **Notes:**
  - ___________________________________________________________
  - ___________________________________________________________
  - ___________________________________________________________

