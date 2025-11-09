# Bonus Hunt Guesser — Verification Report (2025-01-20)

Commit audited: `7a7e08173aecd3e197131c600636c0fcfe637a0d`

## Executive Summary

- Core admin and front-end features from the base v8.0.16 specification (dashboard, bonus hunts, tournaments, users, affiliates, shortcodes, notifications, rankings, currency helpers, global styles, menus) are present and operating according to the code review.【F:bonus-hunt-guesser.php†L387-L453】【F:admin/views/dashboard.php†L111-L205】【F:admin/views/bonus-hunts.php†L40-L310】【F:admin/views/bonus-hunts-edit.php†L152-L251】【F:admin/views/bonus-hunts-results.php†L26-L210】【F:admin/views/tournaments.php†L132-L360】【F:admin/views/users.php†L12-L42】【F:admin/class-bhg-users-table.php†L27-L256】【F:admin/views/affiliate-websites.php†L39-L179】【F:includes/class-bhg-shortcodes.php†L39-L167】【F:includes/helpers.php†L960-L1505】
- Coding standards are not passing: running `composer phpcs` reports 200+ sniffs (tab indentation, unsanitized inputs, direct queries) across templates and the bootstrap file, so the “PHPCS passes (no errors)” acceptance criterion is still unmet.【a87910†L1-L96】
- The jackpot add-on remains entirely unimplemented (no schema, admin menu, logic, or shortcodes); repository searches only surface documentation TODOs, so requirement coverage is **missing**.【3782fb†L1-L32】
- Prizes enhancement add-on is only partially delivered. The current admin UI/data model lacks prize links, click-action toggles, category link controls, carousel display settings, and responsive size logic, and the shortcode still renders a fixed “Prizes” heading.【F:admin/views/prizes.php†L48-L214】【F:includes/class-bhg-prizes.php†L283-L336】【F:includes/class-bhg-shortcodes.php†L324-L380】
- Release collateral is outdated: `CHANGELOG.md` stops at 8.0.14, so the 8.0.16 release notes/migration guidance required by section 16 have not been published.【F:CHANGELOG.md†L1-L23】

Automated tests: `vendor/bin/phpunit` passes (11 tests, 80 assertions).【300187†L1-L8】

## Detailed Requirement Audit

| Section | Status | Notes |
| --- | --- | --- |
| 0. Header & PHPCS | ⚠️ | Header fields match the spec with the exception that `Requires at least` is set to 6.3.5 (stricter than the requested 6.3.0). Coding standards fail due to numerous PHPCS violations in `bonus-hunt-guesser.php` and admin templates.【F:bonus-hunt-guesser.php†L3-L16】【a87910†L1-L96】 |
| 1. Admin Dashboard | ✅ | “Latest Hunts” widget lists the newest three hunts with multi-winner rows, bold usernames, and balance columns.【F:admin/views/dashboard.php†L111-L205】 |
| 2. Bonus Hunts | ✅ | List view includes Final Balance/Affiliate columns plus toggle/delete actions; edit view exposes winner count & participant list with profile links; results view defaults to latest closed hunt with timeframe filter, winner highlighting, and prize mapping. Schema defines `guessing_enabled` and `affiliate_id` columns.【F:admin/views/bonus-hunts.php†L40-L310】【F:admin/views/bonus-hunts-edit.php†L152-L251】【F:admin/views/bonus-hunts-results.php†L26-L210】【F:includes/class-bhg-db.php†L94-L215】 |
| 3. Tournaments | ✅ | Admin table shows title/description/type, participants mode, expanded type options, and delete/close/results actions; edit form works.【F:admin/views/tournaments.php†L132-L360】 |
| 4. Users Admin | ✅ | Custom `WP_List_Table` implements search, sorting, pagination (30/page), and inline affiliate toggles with profile shortcuts.【F:admin/views/users.php†L12-L42】【F:admin/class-bhg-users-table.php†L27-L256】 |
| 5. Affiliates | ✅ | Affiliate website CRUD exists and user profiles render per-site checkboxes plus affiliate indicator helpers used on the front end.【F:admin/views/affiliate-websites.php†L39-L179】【F:bonus-hunt-guesser.php†L1560-L1663】【F:includes/helpers.php†L1450-L1474】 |
| 6. Prizes Module | ⚠️ | Base CRUD, CSS controls, image selectors, and dual regular/premium selections are present, but the add-on requests (prize links, category link toggles, click behaviour options, carousel settings, responsive sizing, removal of automatic “Prizes” heading) are still missing.【F:admin/views/prizes.php†L48-L214】【F:includes/class-bhg-prizes.php†L283-L336】【F:includes/class-bhg-shortcodes.php†L324-L380】 |
| 7. Shortcodes & Pages | ✅ | All required shortcodes are registered and the Info & Help screen documents their attributes; core pages auto-create with the correct compositions.【F:includes/class-bhg-shortcodes.php†L39-L167】【F:admin/views/shortcodes.php†L12-L177】【F:includes/core-pages.php†L17-L155】 |
| 8. Notifications | ✅ | Settings tab provides enable/disable, subject/body, and BCC fields; helper functions normalise BCC and dispatch via `wp_mail()` respecting placeholders.【F:admin/views/notifications.php†L16-L102】【F:includes/notifications.php†L12-L220】 |
| 9. Ranking & Points | ✅ | Tournament recalculation honours participants mode and ranking scope, with default point map fallbacks and shortcodes exposing ranking summaries.【F:includes/class-bhg-models.php†L243-L520】【F:includes/class-bhg-shortcodes.php†L1980-L2090】 |
| 10. Global CSS Panel | ✅ | Settings store global typography/colour controls feeding `bhg_build_global_styles_css()` for shared styling.【F:admin/views/settings.php†L76-L214】【F:bonus-hunt-guesser.php†L376-L585】 |
| 11. Currency System | ✅ | `bhg_currency_symbol()` / `bhg_format_money()` helpers and settings integration satisfy the currency requirement.【F:includes/helpers.php†L960-L987】【F:admin/views/settings.php†L76-L108】 |
| 12. Database & Migrations | ⚠️ | Core columns/tables exist, but jackpot tables/junctions are absent, leaving the add-on migration incomplete.【F:includes/class-bhg-db.php†L94-L215】【3782fb†L1-L32】 |
| 13. Security & i18n | ⚠️ | Escaping/nonces largely present, yet PHPCS flags multiple unsanitized `$_POST` usages in `bonus-hunt-guesser.php`, requiring remediation before sign-off.【a87910†L60-L96】 |
| 14. Backward Compatibility | ✅ | Legacy helpers and sanitizers remain, and upgrade routines check for missing columns before altering tables.【F:includes/class-bhg-db.php†L318-L350】【F:includes/helpers.php†L1329-L1394】 |
| 15. Global UX Guarantees | ✅ | List tables implement search/sort/pagination and timeline filters; affiliate indicators appear on shortcode tables.【F:admin/views/bonus-hunts.php†L40-L310】【F:includes/class-bhg-shortcodes.php†L1505-L1764】【F:includes/helpers.php†L1460-L1474】 |
| 16. Release & Docs | ❌ | Changelog and accompanying docs have not been updated for 8.0.16 / jackpot additions.【F:CHANGELOG.md†L1-L23】 |
| 17. QA Acceptance | ⚠️ | PHPUnit suite passes, but PHPCS failures and missing jackpot/prize enhancements block acceptance.【a87910†L1-L96】【300187†L1-L8】 |
| Add-on: Winner Limits | ✅ | Settings expose per-type limits and award logic records eligibility/skip state with notices in results views.【F:admin/views/settings.php†L109-L148】【F:includes/class-bhg-models.php†L243-L356】【F:admin/views/bonus-hunts-results.php†L172-L210】 |
| Add-on: Frontend Adjustments | ✅ | Table headers render white links and `bhg_hunts` adds the Details column with context-aware CTA.【F:assets/css/bhg-shortcodes.css†L521-L543】【F:includes/class-bhg-shortcodes.php†L2024-L2066】 |
| Add-on: Prizes Enhancements | ❌ | Missing prize link field, category link toggles, click behaviour controls, carousel sizing/auto-scroll configuration, responsive sizing, and removal of automatic heading.【F:admin/views/prizes.php†L48-L214】【F:includes/class-bhg-prizes.php†L283-L336】【F:includes/class-bhg-shortcodes.php†L324-L380】 |
| Add-on: Jackpot Module | ❌ | No code files, schema, or shortcodes implement the jackpot feature set.【3782fb†L1-L32】 |

## Remaining Work Items

1. **Resolve PHPCS violations and security warnings**  
   Update the flagged templates and bootstrap handlers to use tab indentation, sanitize incoming `$_POST`/`$_GET` values, and replace direct SQL interpolation with prepared statements or documented ignores. Primary files: `admin/views/bonus-hunts.php`, `bonus-hunt-guesser.php`, and other admin views highlighted by the sniffer output.【a87910†L1-L96】

2. **Complete the prizes enhancement add-on**  
   Extend `admin/views/prizes.php`, `includes/class-bhg-prizes.php`, `includes/class-bhg-shortcodes.php`, and associated JS/CSS to add prize links, category link/show toggles, click-action options (image popup / same window / new window), carousel display controls (visible count, total load, auto-scroll), responsive sizing logic, and remove the forced “Prizes” heading per the contract.【F:admin/views/prizes.php†L48-L214】【F:includes/class-bhg-prizes.php†L283-L336】【F:includes/class-bhg-shortcodes.php†L324-L380】

3. **Implement the jackpot module**  
   Add schema upgrades in `includes/class-bhg-db.php`, admin CRUD screens (e.g., new `admin/views/jackpots.php`), model/services to manage jackpot amounts (likely in a new `includes/class-bhg-jackpots.php`), integrate jackpot logic into hunt-closing flows (`includes/class-bhg-models.php`), and register the required shortcodes in `includes/class-bhg-shortcodes.php` with corresponding front-end templates.【3782fb†L1-L32】

4. **Refresh release documentation**  
   Update `CHANGELOG.md`, `README.md`, and the Admin “Info & Help”/shortcode references to cover the 8.0.16 features (winner limits, prizes enhancements, jackpot module) once implemented, including migration notes for new tables/settings.【F:CHANGELOG.md†L1-L23】【F:admin/views/shortcodes.php†L12-L177】

