# Verification Report — Bonus Hunt Guesser v8.0.16 (2025-01-28)

Status legend: ✅ Complete · ⚠️ Needs manual QA · ❌ Missing / action required

## Automated Checks
- ✅ `vendor/bin/phpunit` (11 tests / 80 assertions)
- ❌ `vendor/bin/phpcs --standard=phpcs.xml` — 996 errors & 802 warnings across 40 files. Hotspots: `includes/class-bhg-shortcodes.php`, `admin/class-bhg-admin.php`, `tests/bootstrap.php`. Run `vendor/bin/phpcbf --standard=phpcs.xml` per directory, then address remaining manual fixes (sanitization, escaping, dynamic SQL comments).

## Requirement Summary

| Section | Status | Notes |
| --- | --- | --- |
| 0. Header & Bootstrapping | ⚠️ | Metadata correct, text domain loads, but PHPCS still flags indentation & sanitization issues in `bonus-hunt-guesser.php` and related admin handlers. |
| 1. Admin Dashboard | ✅ | Dashboard submenu & Latest Hunts table verified; start/final balances left aligned with per-winner rows. |
| 2. Bonus Hunts | ⚠️ | CRUD/results features present; need QA on participant removal nonce + sanitize inputs flagged by PHPCS. |
| 3. Tournaments | ✅ | Title/description fields, participants mode, and CRUD actions available; confirm edit persistence once PHPCS cleaned. |
| 4. Users Admin | ✅ | Search, sorting, and pagination (30/page) operational in `BHG_Users_Table`. |
| 5. Affiliates Sync | ⚠️ | UI present; re-test user profile field propagation when adding/removing affiliate sites. |
| 6. Prizes Module | ❌ | Large image upload + size labels unverified; prize link, category management, click-behavior options, carousel controls, and dual prize sets still pending implementation. |
| 7. Shortcodes & Pages | ⚠️ | Catalogue updated; ensure each required page is published with correct shortcode stack and header link color adjustments. |
| 8. Notifications | ⚠️ | Admin blocks exist; run integration test for BCC + enable/disable toggles (PHPCS flags sanitization). |
| 9. Ranking & Points | ⚠️ | Points mapping + scope toggle exposed; confirm ranking service highlights winners/top 3 and add/expand unit coverage. |
| 10. Global CSS Panel | ⚠️ | Controls rendered; verify global styles apply across frontend components. |
| 11. Currency System | ⚠️ | Helpers implemented; execute EUR↔USD toggle smoke test to verify consistent formatting. |
| 12. Database & Migrations | ⚠️ | Tables/columns defined; run fresh install + upgrade migration to confirm idempotence. |
| 13. Security & i18n | ❌ | PHPCS reports unsanitized `$_POST` usage in admin saves (`settings`, `bonus hunts`, `notifications`). Address escapes/nonces for delivery. |
| 14. Backward Compatibility | ⚠️ | Requires regression testing with legacy data backups, especially after jackpot/prize schema updates. |
| 15. Global UX Guarantees | ⚠️ | Sorting/search/pagination exist; retest timeline filters (This Week/Month/Year) for hunts/tournaments/leaderboards. |
| 16. Release & Docs | ❌ | Update changelog/readme/admin help for 8.0.16 once PHPCS remediation complete. |
| 17. QA (Acceptance) | ❌ | Full end-to-end flow, currency switch validation, guessing toggle enforcement, prizes frontend grid/carousel, notifications BCC, translations coverage still outstanding. |

## Outstanding Actions
1. **Resolve PHPCS violations**: run `vendor/bin/phpcbf` per high-offender file (shortcodes, admin, tests) and manually fix remaining sanitization/escaping issues.
2. **Complete prize enhancements**: add prize links/categories, click behavior options, carousel controls, dual prize set logic, and responsive size selection.
3. **Execute QA scenarios**: document evidence for currency switch, winners limit enforcement, notifications, translations, and frontend tables.
4. **Refresh release materials**: bump changelog/readme/screenshots after code compliance and QA sign-off.

> Delivery cannot proceed until PHPCS passes cleanly and outstanding prize/QA tasks are closed.
