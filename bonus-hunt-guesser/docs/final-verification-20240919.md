# Bonus Hunt Guesser – Final Verification (2024-09-19)

## Runtime Targets
- PHP 7.4
- WordPress 6.3.5
- MySQL 5.5.5+

## Checklist Summary

| Area | Status | Notes |
| ---- | ------ | ----- |
| Plugin header metadata | ✅ | Header shows version 8.0.16, PHP 7.4, WP 6.3.5, MySQL 5.5.5. |
| Text domain loading | ✅ | `bonus-hunt-guesser` loaded during `plugins_loaded`. |
| PHPCS compliance | ❌ | Running `vendor/bin/phpcs --report=summary` raises 11,571 errors and 1,478 warnings across 46 files (formatting, documentation, direct SQL, missing doc blocks). |
| Admin dashboard latest hunts | ✅ | Displays three most recent hunts with winner rows, balances, and closure times. |
| Bonus hunt admin list/actions | ✅ | Columns/actions match spec; participant list present on edit screen. |
| Bonus hunt results view | ⚠️ | Shows latest closed hunt and filter controls, but pricing column lacks jackpot integration. |
| Tournament admin | ⚠️ | Title/description/participants mode present, but quarterly/all-time filters need QA. |
| Users admin tooling | ✅ | Search, sorting, and 30-per-page pagination implemented. |
| Affiliate sync | ⚠️ | UI available; automated propagation not regression tested this round. |
| Prizes module | ⚠️ | CRUD/UI operational; dual regular/premium prize sets exist but require more QA. |
| Shortcodes | ⚠️ | Core set available; jackpot shortcodes missing. |
| Notifications | ⚠️ | Tab available; enable/disable + BCC path needs E2E validation. |
| Ranking & points | ⚠️ | Service implemented; requires confirmation of scope toggle + unit coverage. |
| Global CSS controls | ✅ | Stylesheet injection wired. |
| Currency helpers | ✅ | Uses `bhg_currency` option and `bhg_format_money()`. |
| Database schema | ⚠️ | Columns present; junction table migrations assumed but not re-run. |
| Security/i18n | ⚠️ | Escaping patterns mostly compliant; PHPCS still flags direct SQL usage. |
| Backward compatibility | ⚠️ | Legacy helpers remain; full regression not executed. |
| Global UX (tables) | ⚠️ | Sorting/search/pagination available, but timeline filters need cross-page QA. |
| Release/docs | ❌ | Changelog, readme, and help tabs not updated for 8.0.16 with jackpot/premium notes. |
| QA acceptance | ❌ | E2E scenarios (jackpot, notifications, affiliate propagation) unverified. |
| Winner limits | ⚠️ | Backend enforcement exists; admin/frontend notices require validation. |
| Frontend adjustments | ⚠️ | Hunts shortcode shows Details column; header link colour not tested. |
| Prizes enhancements | ⚠️ | Premium display logic present; admin image guidance requires review. |
| Jackpot module | ❌ | No admin menu, schema, or shortcodes for jackpots are present. |

## Outstanding Work
1. Resolve PHPCS errors across the project (formatting, doc blocks, sanitization, DB access).
2. Implement the jackpot feature suite (admin CRUD, schema, closing logic, shortcodes, front-end).
3. Complete QA regression for winner limits, notifications, affiliate sync, tournaments, and prizes enhancements.
4. Update changelog, README, and Info & Help tab content for v8.0.16 deliverables.

## Test Log
- `vendor/bin/phpunit`
- `vendor/bin/phpcs --report=summary`

The PHPCS command currently fails with the error totals noted above.
