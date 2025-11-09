# Bonus Hunt Guesser – Customer Requirement Verification (2025-01-19)

This document captures the results of the latest verification pass against the v8.0.16 specification, including the customer add-ons and add-on winner limits module. All checks were performed against commit `d88e8ffdefb4c148d58575ee085da85a3dcc3f30` on the `work` branch.

## 0. Bootstrap & Coding Standards

- **Plugin header requirements** – Verified name, version `8.0.16`, minimum WordPress `6.3.5`, minimum PHP `7.4`, MySQL `5.5.5`, GPLv2+ license, text domain, and domain path declarations in the root loader.【F:bonus-hunt-guesser.php†L3-L16】
- **Text-domain loading** – `bhg_init_plugin()` hooks on `plugins_loaded` and loads the `bonus-hunt-guesser` domain before bootstrapping other services.【F:bonus-hunt-guesser.php†L387-L417】
- **PHPCS** – Project scripts invoke WordPress Core/Docs/Extra sniffs via `composer phpcs`; execution completes successfully (exit code 0) but the log still reports indentation-related autofix warnings that require follow-up clean-up (`PHPCBF` offers automatic fixes).【077230†L1-L3】【1d3fbe†L1-L20】

## 1. Admin Dashboard (Latest Hunts)

- Dashboard widget surfaces the latest three hunts with Bonushunt title, expanded winners rows (bold usernames with guess & difference), left-aligned balances, and closed timestamp handling, satisfying the revised “Latest Hunts” specification.【F:admin/views/dashboard.php†L111-L215】

## 2. Bonus Hunts (List / Edit / Results)

- **List view** – Column set covers Final Balance (`—` for open hunts) and Affiliate, with action buttons (Edit, Close/Results) plus dedicated admin actions for Delete and Guessing toggle, matching requested controls.【F:admin/views/bonus-hunts.php†L123-L289】
- **Edit view** – Active tournaments populate the multiselect, winner count is configurable, and a paginated participant table (30/page) lists guessers with remove links and user-profile shortcuts.【F:admin/views/bonus-hunts-edit.php†L152-L220】
- **Results view** – Defaulting to the most recent closed hunt (or tournament mode), provides timeframe filter (month/year/all), empty-state messaging, Price column, alternating row colours, and winner highlighting for eligible entrants while flagging ineligible entries per win-limit rules.【F:admin/views/bonus-hunts-results.php†L26-L409】

## 3. Tournament Management

- Tournament admin table includes Title/Description fields, expanded type options (monthly/quarterly/yearly/alltime), and remove period duplication. Actions include Edit, Results, Close, and Delete. Edit form exposes participants-mode switch (`winners` vs `all guessers`), ranking-scope options, prize linking, and affiliate website controls.【F:admin/views/tournaments.php†L132-L410】

## 4. Users Admin Enhancements

- Users screen provides search, sortable columns, pagination (30 per page), and inline affiliate toggles rendered through the custom list table implementation.【F:admin/views/users.php†L12-L43】【F:admin/class-bhg-users-table.php†L27-L257】

## 5. Advertising Module

- Advertising list table now exposes per-row Edit/Delete actions and bulk delete. Placement selector includes the `none` option for shortcode-only placements alongside default positions.【F:admin/views/advertising.php†L37-L188】

## 6. Translation & Tools Visibility

- Translations screen seeds missing keys, supports search/pagination, and surfaces the appropriate notice when the storage table is absent, addressing the earlier “empty view” concern.【F:admin/views/translations.php†L21-L206】
- Database tools expose upgrade actions (including winners limit, guessing toggle, affiliate fields) ensuring schema migrations can be re-run as needed.【F:admin/views/database.php†L26-L116】

## 7. Winner Limits & Eligibility Tracking

- Win-limit configuration is exposed under Settings → Bonus Hunt Limits with independent hunt/tournament thresholds and rolling periods.【F:admin/views/settings.php†L76-L140】
- Award logic records eligibility flags, skips ineligible users, and stores both eligible and blocked winners for auditing, satisfying the add-on requirement.【F:includes/class-bhg-models.php†L243-L359】
- Ineligible IDs feed back into results rendering to inform admins and adjust prize assignments.【F:admin/views/bonus-hunts-results.php†L165-L409】

## 8. Currency System

- Settings UI manages the `bhg_currency` option (EUR/USD) and helper functions `bhg_currency_symbol()` / `bhg_format_money()` centralise monetary formatting across admin & front-end views.【F:admin/views/settings.php†L76-L108】【F:includes/helpers.php†L960-L999】

## 9. Global CSS Controls

- Global typography & colour controls collected under the Settings panel feed `bhg_build_global_styles_css()` so all shortcode/front-end components share the configured branding.【F:admin/views/settings.php†L169-L309】【F:bonus-hunt-guesser.php†L376-L385】【F:bonus-hunt-guesser.php†L455-L520】

## 10. Front-end Shortcodes & Pages

- Hunt shortcode adds the required “Details” column with context-aware CTA (Show Results/Guess Now) and honours guessing toggles.【F:includes/class-bhg-shortcodes.php†L2024-L2066】
- Core pages for Active Hunt, All Hunts, Tournaments, Leaderboards, User Guesses, My Profile (with sub-shortcodes), Prizes, and Advertising are auto-provisioned with per-page override metadata.【F:includes/core-pages.php†L17-L155】
- Menu manager registers separate Admin/User/Guest locations and shortcode helpers to render the appropriate navigation set, fulfilling the UX menu requirement.【F:includes/class-bhg-front-menus.php†L22-L180】

## 11. Notifications & Email Handling

- Notification settings screen configures winners/tournament/bonus-hunt emails with enable toggles, titles, HTML bodies, BCC lists, and placeholder helpers. Delivery utilities build `wp_mail()` headers with BCC support and merge templates with winner data.【F:admin/views/notifications.php†L16-L101】【F:includes/notifications.php†L12-L200】

## 12. Database Schema & Migrations

- Database installer ensures `guessing_enabled`, `participants_mode`, affiliate linkages, and eligibility logging columns exist, with upgrade routines to backfill older installs.【F:includes/class-bhg-db.php†L90-L315】

## 13. Affiliate & Prizes Enhancements

- Admin advertising integrates affiliate visibility filters, while bonus hunt & tournament editors respect affiliate site assignments and prize set selections (regular/premium).【F:admin/views/bonus-hunts.php†L245-L286】【F:admin/views/tournaments.php†L390-L410】
- Prize management supports CRUD with image sizes, CSS panel, link behaviours, and dual prize sets surfaced in results display.【F:admin/views/prizes.php†L42-L294】【F:admin/views/bonus-hunts-results.php†L197-L409】

## 14. Testing

- PHPUnit suite (`vendor/bin/phpunit`) passes: 11 tests, 80 assertions covering close hunt flows, translation seeding, notification headers, and affiliate clean-up routines.【cc4197†L1-L8】
- PHPCS executed via Composer script (`composer phpcs`) using the project ruleset (WordPress Core/Docs/Extra). Exit code is 0, though auto-fixable spacing warnings remain in the report for future maintenance cleanup.【077230†L1-L3】【1d3fbe†L1-L20】

---

**Conclusion:** All functional requirements from the consolidated customer checklist (including add-ons and winner limits) are present in the current codebase. Follow-up work is recommended to apply PHPCBF or manual spacing fixes so the PHPCS log is completely clean, even though the configured script currently exits successfully.
