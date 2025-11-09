# Bonus Hunt Guesser — Verification Report (2025-01-22)

Commit audited: `0b37298262f3b1b9477e08b2d3693ae649a00bf0`

## Executive Summary

- The dashboard, hunt management, tournament tooling, user administration, affiliate sync, shortcode catalog, login redirector, and role-aware front-end menus continue to operate per the v8.0.16 contract, covering multi-winner handling, participant pruning, timeline filters, and navigation requirements.【F:bonus-hunt-guesser.php†L387-L418】【F:admin/views/dashboard.php†L33-L214】【F:admin/views/bonus-hunts.php†L33-L253】【F:admin/views/bonus-hunts-edit.php†L120-L252】【F:admin/views/bonus-hunts-results.php†L26-L214】【F:admin/views/tournaments.php†L132-L332】【F:admin/views/users.php†L12-L66】【F:admin/class-bhg-users-table.php†L27-L220】【F:admin/views/affiliate-websites.php†L39-L179】【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:includes/class-bhg-login-redirect.php†L21-L140】【F:includes/class-bhg-front-menus.php†L22-L199】
- WordPress Coding Standards remain unmet. `phpcs` reports 10,581 errors and 1,428 warnings across 46 files, including bootstrap, admin templates, models, and tests, blocking delivery sign-off.【c606f4†L1-L41】
- The jackpot module is still absent—repository-wide searches find the keyword only in documentation, not in PHP classes, migrations, or shortcode registrations.【f781dd†L1-L17】
- Prizes add-ons (prize link field, category link toggles, click-behaviour controls, carousel sizing settings, responsive image switching, heading suppression) are missing from both admin modal and shortcode renderer, leaving that contract scope incomplete.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】
- Release notes have not been updated past v8.0.14, so the documentation deliverables for 8.0.16 remain outstanding.【F:CHANGELOG.md†L2-L34】

Automated tests: `vendor/bin/phpunit` passes (11 tests, 80 assertions). `phpcs` fails as outlined above.【d38397†L1-L9】【c606f4†L1-L41】

## Detailed Requirement Audit

| Section | Status | Notes |
| --- | --- | --- |
| 0. Header & PHPCS | ⚠️ | Metadata matches except `Requires at least` is set to 6.3.5 instead of the requested 6.3.0, and PHPCS reports 10k+ violations that must be cleaned before release.【F:bonus-hunt-guesser.php†L3-L14】【c606f4†L1-L41】 |
| 1. Admin Dashboard | ✅ | “Latest Hunts” lists the three most recent hunts, expanding each winner into its own row with bold usernames and aligned balances.【F:admin/views/dashboard.php†L33-L205】 |
| 2. Bonus Hunts | ✅ | List view exposes Final Balance/Affiliate columns with edit/results/admin-action controls; edit view provides configurable winner counts and participant pruning with profile links; results view defaults to the latest closed hunt, offers timeframe/toggle controls, highlights winners, and maps regular vs premium prizes.【F:admin/views/bonus-hunts.php†L33-L253】【F:admin/views/bonus-hunts-edit.php†L120-L252】【F:admin/views/bonus-hunts-results.php†L26-L214】 |
| 3. Tournaments | ✅ | Admin table shows title/description/type (including quarterly/alltime), supports search/sort/pagination, and the edit form updates tournaments successfully.【F:admin/views/tournaments.php†L132-L332】 |
| 4. Users Admin | ✅ | Custom `WP_List_Table` delivers search, sortable columns, 30-per-page pagination, affiliate toggles, and links into the user profile editor.【F:admin/views/users.php†L12-L66】【F:admin/class-bhg-users-table.php†L27-L220】 |
| 5. Affiliates | ✅ | Affiliate website CRUD stays wired to per-user toggles with labels and profile links.【F:admin/views/affiliate-websites.php†L39-L179】 |
| 6. Prizes Module | ⚠️ | Core CRUD, CSS options, and dual prize sets exist, but the add-on requirements (prize link field, category link/show toggle, click behaviour selection, carousel sizing/limit controls, responsive image switching, heading removal) are missing in the modal and shortcode renderer.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】 |
| 7. Shortcodes & Pages | ✅ | The “Info & Help” screen documents the full shortcode catalog, and `[bhg_hunts]` now includes the Details column with Guess Now / Show Results calls to action alongside other required filters.【F:admin/views/shortcodes.php†L12-L167】【F:includes/class-bhg-shortcodes.php†L2000-L2068】 |
| 8. Notifications | ✅ | Winners/Tournament/Bonushunt email templates expose enable toggles, HTML bodies, and BCC fields, dispatching via `wp_mail()` with filterable headers.【F:admin/views/notifications.php†L16-L114】【F:includes/notifications.php†L18-L214】 |
| 9. Ranking & Points | ✅ | The ranking service logs winners, enforces win limits, and recalculates tournament standings while returning official and ineligible winners for highlighting.【F:includes/class-bhg-models.php†L243-L350】 |
| 10. Global CSS Panel | ✅ | Settings provide typography/color controls (title, headings, body) that feed the global styling system.【F:admin/views/settings.php†L167-L220】【F:bonus-hunt-guesser.php†L360-L383】 |
| 11. Currency System | ✅ | Settings expose the EUR/USD toggle and helpers format all currency output accordingly.【F:admin/views/settings.php†L75-L99】【F:includes/helpers.php†L960-L987】 |
| 12. Database & Migrations | ⚠️ | Core schemas/migrations cover `guessing_enabled`, `participants_mode`, `affiliate_id`, dual prize sets, and hunt↔tournament mapping, but no jackpot tables or upgrades are present.【F:includes/class-bhg-db.php†L93-L262】【f781dd†L1-L17】 |
| 13. Security & i18n | ⚠️ | Templates and controllers generally sanitize/escape, yet PHPCS highlights numerous missing nonces, docblocks, and prepared statements (same violations blocking Section 0).【c606f4†L1-L41】 |
| 14. Backward Compatibility | ✅ | Legacy migrations remain idempotent, ensuring older installs pick up new columns and indices automatically.【F:includes/class-bhg-db.php†L272-L359】 |
| 15. Global UX Guarantees | ✅ | Admin and shortcode tables implement search, sorting, pagination, timeline filters, and affiliate signals, including white table-header links per add-on request.【F:admin/views/bonus-hunts.php†L38-L218】【F:includes/class-bhg-shortcodes.php†L2000-L2068】【F:assets/css/bhg-shortcodes.css†L509-L546】 |
| 16. Release & Docs | ❌ | Changelog still stops at 8.0.14—8.0.16 release notes, migrations, and screenshots remain undone.【F:CHANGELOG.md†L2-L34】 |
| 17. QA (Acceptance) | ⚠️ | PHPUnit passes, but PHPCS failures plus missing jackpot and prize add-ons block acceptance testing from passing the agreed criteria.【c606f4†L1-L41】【d38397†L1-L9】 |
| Add-on: Winner Limits | ✅ | Settings capture per-type win caps and rolling windows, and awarding logic records eligible/ineligible winners for transparency.【F:admin/views/settings.php†L100-L147】【F:includes/class-bhg-models.php†L243-L350】 |
| Add-on: Frontend Adjustments | ✅ | Table header links render in white, and `[bhg_hunts]` exposes the Details column with Guess Now/Show Results behavior based on hunt status.【F:assets/css/bhg-shortcodes.css†L509-L546】【F:includes/class-bhg-shortcodes.php†L2000-L2062】 |
| Add-on: Prizes Enhancements | ❌ | Missing prize link field, category link toggle, click-action selector, carousel sizing/auto-scroll controls, responsive image rules, and heading suppression keep this add-on incomplete.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】 |
| Add-on: Jackpot Module | ❌ | No jackpot schema, admin UI, hunt-close integration, or shortcodes exist in the codebase; only documentation references remain.【f781dd†L1-L17】 |

## Remaining Work Items

1. **Restore PHPCS compliance**  
   Apply WordPress Coding Standards fixes across bootstrap, uninstall script, admin classes/views, helpers, and tests to clear the 10k+ violations reported by `phpcs`.【c606f4†L1-L41】

2. **Complete the prizes enhancement add-on**  
   Extend prize CRUD and shortcode rendering to introduce the required link fields, category controls, click-action behaviour, carousel sizing limits, responsive sizing logic, and optional heading removal, then update assets accordingly.【F:admin/views/prizes.php†L130-L214】【F:includes/class-bhg-prizes.php†L289-L353】【F:includes/class-bhg-shortcodes.php†L333-L392】

3. **Implement the jackpot module**  
   Add database tables/migrations, admin screens, hunt-close logic, and the four shortcodes (`bhg_jackpot_current`, `bhg_jackpot_latest`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`) mandated by the contract.【f781dd†L1-L17】

4. **Refresh release documentation**  
   Update `CHANGELOG.md`, `README.md`, and the Info & Help screen to capture the 8.0.16 feature set and migration guidance once the missing functionality lands.【F:CHANGELOG.md†L2-L34】【F:admin/views/shortcodes.php†L12-L167】

## Test Log

- `vendor/bin/phpunit` *(passes: 11 tests, 80 assertions).*【d38397†L1-L9】
- `vendor/bin/phpcs --standard=phpcs.xml --report=summary` *(fails: 10,581 errors, 1,428 warnings across 46 files).*【c606f4†L1-L41】
