# Bonus Hunt Guesser — Verification Report (2025-01-21)

Commit audited: `c8d0c4cd9f7e3f42d0ffec884bd48b40c32370f1`

## Executive Summary

- Core dashboard, bonus-hunt, tournament, user-management, affiliate, shortcode, login-redirect, and menu features from the v8.0.16 contract continue to function in the current build; the code paths remain in place for multi-winner handling, participant pruning, timelines, and role-aware navigation.【F:bonus-hunt-guesser.php†L387-L418】【F:admin/views/dashboard.php†L120-L214】【F:admin/views/bonus-hunts.php†L90-L218】【F:admin/views/bonus-hunts-edit.php†L130-L233】【F:admin/views/bonus-hunts-results.php†L26-L210】【F:admin/views/tournaments.php†L132-L360】【F:admin/views/users.php†L12-L43】【F:admin/class-bhg-users-table.php†L27-L220】【F:admin/views/affiliate-websites.php†L39-L179】【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:includes/class-bhg-login-redirect.php†L21-L130】【F:includes/class-bhg-front-menus.php†L22-L200】
- PHPCS compliance is still outstanding: `composer phpcs` reports hundreds of indentation, sanitization, and direct-query sniffs across the bootstrap, admin templates, and uninstall script.【1f33eb†L1-L140】
- The jackpot feature set (schema, admin UI, hunt-close integration, shortcodes) remains unimplemented; repository searches surface only documentation references, confirming the add-on is still missing.【994b9c†L1-L34】
- Prizes enhancements (prize link field, category link toggle, click-behaviour options, carousel sizing controls, responsive display logic, heading removal) are absent from the current admin modal and shortcode renderer.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L390】
- Release collateral has not been updated for 8.0.16—the published changelog stops at 8.0.14, leaving the delivery documentation requirement unmet.【F:CHANGELOG.md†L1-L34】

Automated tests: `vendor/bin/phpunit` passes (11 tests, 80 assertions).【f79611†L1-L9】

## Detailed Requirement Audit

| Section | Status | Notes |
| --- | --- | --- |
| 0. Header & PHPCS | ⚠️ | Plugin header matches the requested metadata except that `Requires at least` is stricter (6.3.5 vs 6.3.0). Coding standards still fail due to indentation and sanitization errors flagged by PHPCS.【F:bonus-hunt-guesser.php†L3-L14】【1f33eb†L1-L140】 |
| 1. Admin Dashboard | ✅ | "Latest Hunts" widget lists the newest hunts, showing every winner row with bold usernames and formatted balances.【F:admin/views/dashboard.php†L120-L205】 |
| 2. Bonus Hunts | ✅ | List view exposes Final Balance/Affiliate columns plus actions; edit view includes winner-count control and participant list with profile links; results view defaults to the latest closed hunt, offers timeframe selectors, highlights winners, and surfaces prize sets.【F:admin/views/bonus-hunts.php†L90-L218】【F:admin/views/bonus-hunts-edit.php†L130-L233】【F:admin/views/bonus-hunts-results.php†L26-L210】 |
| 3. Tournaments | ✅ | Admin table includes title/description/type columns, expanded type options (quarterly/alltime), participants mode, and close/results/delete actions; the edit form works end-to-end.【F:admin/views/tournaments.php†L132-L360】 |
| 4. Users Admin | ✅ | Custom `WP_List_Table` delivers search, sortable columns, 30-per-page pagination, and inline affiliate toggles tied to profile edit links.【F:admin/views/users.php†L12-L43】【F:admin/class-bhg-users-table.php†L27-L220】 |
| 5. Affiliates | ✅ | Affiliate website CRUD and per-user toggles are operational, with links back to profile editing and status labels for each site.【F:admin/views/affiliate-websites.php†L39-L179】 |
| 6. Prizes Module | ⚠️ | Base CRUD, CSS panel, image selectors, and dual prize-set assignments exist, but required add-ons (prize link field, category link controls, click action options, carousel sizing/auto-scroll settings, responsive size logic, removal of automatic heading) are absent from both admin modal and shortcode renderer.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L390】 |
| 7. Shortcodes & Pages | ✅ | Info & Help documents all shortcodes; `[bhg_hunts]` now provides the Details column with context-aware CTAs; other required tags and auto-created pages remain available.【F:admin/views/shortcodes.php†L12-L167】【F:includes/class-bhg-shortcodes.php†L2000-L2068】 |
| 8. Notifications | ✅ | Admin tab exposes enable/disable toggles, subject/body fields, and BCC inputs; helpers normalize addresses and dispatch via `wp_mail()` with proper headers.【F:admin/views/notifications.php†L16-L114】【F:includes/notifications.php†L18-L214】 |
| 9. Ranking & Points | ✅ | Ranking service calculates points with win-limit awareness and triggers tournament recalculations while logging eligibility decisions.【F:includes/class-bhg-models.php†L243-L350】 |
| 10. Global CSS Panel | ✅ | Settings expose typography and color controls that feed into global inline CSS for shared components.【F:admin/views/settings.php†L171-L220】【F:bonus-hunt-guesser.php†L360-L383】 |
| 11. Currency System | ✅ | Settings offer EUR/USD selection and helpers format all monetary outputs accordingly.【F:admin/views/settings.php†L80-L108】【F:includes/helpers.php†L960-L987】 |
| 12. Database & Migrations | ⚠️ | Core tables include `guessing_enabled`, `participants_mode`, `affiliate_id`, and the hunt↔tournament junction; jackpot tables/migrations are still missing.【F:includes/class-bhg-db.php†L94-L216】【994b9c†L1-L34】 |
| 13. Security & i18n | ⚠️ | Most views sanitize/escape data, but PHPCS still flags missing nonces/sanitization in several templates and uninstall routines that must be rectified before acceptance.【1f33eb†L70-L140】 |
| 14. Backward Compatibility | ✅ | Legacy migrations and helper shims remain in place, including automatic renaming of the legacy hunt↔tournament table.【F:includes/class-bhg-db.php†L80-L115】 |
| 15. Global UX Guarantees | ✅ | Admin/shortcode tables provide search, sorting, pagination, timeline filters, and affiliate indicators per the specification.【F:admin/views/bonus-hunts.php†L90-L218】【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:assets/css/bhg-shortcodes.css†L509-L546】 |
| 16. Release & Docs | ❌ | Project changelog and release docs have not been updated to cover 8.0.16 features, migrations, or outstanding add-ons.【F:CHANGELOG.md†L1-L34】 |
| 17. QA (Acceptance) | ⚠️ | PHPUnit suite passes, but PHPCS failures plus missing jackpot/prize enhancements block sign-off for v8.0.16 delivery.【1f33eb†L1-L140】【f79611†L1-L9】 |
| Add-on: Winner Limits | ✅ | Settings define per-type maximums and rolling windows; awarding logic records eligibility/skip reasons and surfaces notices in results/shortcodes.【F:admin/views/settings.php†L110-L148】【F:includes/class-bhg-models.php†L243-L350】【F:includes/class-bhg-shortcodes.php†L888-L989】 |
| Add-on: Frontend Adjustments | ✅ | Table header links render in white, and `[bhg_hunts]` exposes the Details column with Guess/Results CTAs depending on status.【F:assets/css/bhg-shortcodes.css†L509-L546】【F:includes/class-bhg-shortcodes.php†L2000-L2062】 |
| Add-on: Prizes Enhancements | ❌ | Missing link field, category link toggle, click-action behaviour, carousel sizing/auto-scroll controls, responsive sizing logic, and heading removal in the shortcode output.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L390】 |
| Add-on: Jackpot Module | ❌ | No jackpot schema, admin menu, or shortcode registrations exist; only documentation references remain.【994b9c†L1-L34】 |

## Remaining Work Items

1. **Restore PHPCS compliance**  
   Update the bootstrap, uninstall script, and admin templates to use tab indentation, sanitize/scrub input values, and replace direct SQL interpolation with prepared statements where required. Focus files: `bonus-hunt-guesser.php`, `admin/views/*.php`, `uninstall.php` (per sniffer output).【1f33eb†L1-L140】

2. **Complete the prizes enhancement add-on**  
   Extend `admin/views/prizes.php`, `includes/class-bhg-prizes.php`, `includes/class-bhg-shortcodes.php`, and related assets to add the missing link/category controls, click-behaviour options, carousel sizing settings, responsive size rules, and to suppress the forced heading in shortcode output.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L390】

3. **Implement the jackpot module**  
   Add migrations to `includes/class-bhg-db.php`, an admin CRUD screen (new `admin/views/jackpots.php` or equivalent), service/controller classes, hunt-close hooks, and the four front-end shortcodes demanded by the contract (`bhg_jackpot_current`, `bhg_jackpot_latest`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`).【994b9c†L1-L34】

4. **Refresh release documentation**  
   Update `CHANGELOG.md`, `README.md`, and the Info & Help screen once the missing work lands so the 8.0.16 delivery has proper release notes and migration guidance.【F:CHANGELOG.md†L1-L34】【F:admin/views/shortcodes.php†L12-L167】

## Test Log

- `composer phpcs` *(fails: outstanding WordPress coding standards violations; see sniffer output above).*【1f33eb†L1-L140】
- `vendor/bin/phpunit` *(passes).*【f79611†L1-L9】
