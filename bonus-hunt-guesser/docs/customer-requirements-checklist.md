# Customer Requirements Checklist — Bonus Hunt Guesser v8.0.16

Status legend: ✅ Complete · ⚠️ Not verified · ❌ Missing / incomplete · ➖ Not applicable

> Reference: For the detailed compliance matrix, consult `docs/final-checklist-20240917.md`.

## 0) Plugin Header & Bootstrapping
- ✅ Plugin header exposes the requested metadata (name, version 8.0.16, PHP 7.4, WP 6.3.5+, MySQL 5.5.5+, text domain, license). 【F:bonus-hunt-guesser.php†L1-L16】
- ✅ Text domain loads during `plugins_loaded`. 【F:bonus-hunt-guesser.php†L386-L404】
- ❌ PHPCS compliance not confirmed (sniffs not executed in this review).

## 1) Admin Dashboard (Latest Hunts)
- ✅ “Latest Hunts” card renders the latest three hunts with Bonushunt, All Winners (bold usernames with guess/difference), Start Balance, Final Balance (– if open), and Closed At columns. 【F:admin/views/dashboard.php†L44-L197】

## 2) Bonus Hunts (List · Edit · Results)
- ✅ List view includes Final Balance (em dash when open), Affiliate, configurable Winners count, and actions for Edit/Close/Results plus Delete and Guessing toggle. 【F:admin/views/bonus-hunts.php†L226-L320】
- ✅ Edit view restricts tournaments to active entries, exposes winners count, participant removal table with profile links, and affiliate selection. 【F:admin/views/bonus-hunts-edit.php†L92-L229】
- ⚠️ Results page implements selectors and timeframe filters, but requires verification to confirm default selection and empty-state copy. 【F:admin/views/bonus-hunts-results.php†L86-L320】
- ⚠️ Empty state string should be confirmed as “There are no winners yet.” 【F:admin/views/bonus-hunts-results.php†L249-L269】
- ✅ Winners highlighted (row class `bhg-results-row--winner`) and include Price column. 【F:admin/views/bonus-hunts-results.php†L198-L238】
- ✅ Database migrations add `guessing_enabled` and `affiliate_id`. 【F:includes/class-bhg-db.php†L96-L114】【F:includes/class-bhg-db.php†L269-L296】

## 3) Tournaments (List · Edit)
- ✅ List screen implements search, sorting, pagination, and actions (Edit, Close, Results, Delete). 【F:admin/views/tournaments.php†L144-L296】
- ✅ Edit form exposes Title, Description, and Participants Mode options. 【F:admin/views/tournaments.php†L300-L380】
- ⚠️ Type selector now offers requested options but still surfaces legacy values in some contexts; requires UX confirmation. 【F:admin/views/tournaments.php†L312-L375】
- ✅ Database migration adds `participants_mode`. 【F:includes/class-bhg-db.php†L130-L139】【F:includes/class-bhg-db.php†L317-L324】

## 4) Users (Admin)
- ✅ Custom `WP_List_Table` supports search, sortable columns, affiliate toggles, and 30-per-page pagination with navigation rendered above/below the table. 【F:admin/views/users.php†L21-L41】【F:admin/class-bhg-users-table.php†L27-L256】

## 5) Affiliates (Sync)
- ⚠️ Requires end-to-end testing to confirm affiliate CRUD updates propagate to user profiles; static review not conclusive.

## 6) Prizes (Admin · Frontend · Shortcode)
- ⚠️ Admin CRUD and shortcode rendering present in codebase, but carousel/grid behavior and image sizing need runtime verification beyond static review.

## 7) Shortcodes (Catalog & Pages)
- ⚠️ Shortcode catalogue and required pages exist in code, yet comprehensive option coverage and page creation were not validated in this pass.

## 8) Notifications
- ⚠️ Email notification settings (including BCC) appear in code but were not exercised in this review.

## 9) Ranking & Points
- ⚠️ Ranking service logic and automated tests not executed during this verification cycle.

## 10) Global CSS / Color Panel
- ⚠️ Global style builder is referenced but not validated across components in this pass.

## 11) Currency System
- ✅ Currency helpers provide EUR/USD toggle and formatting; dashboard uses them for money values. 【F:bonus-hunt-guesser.php†L1029-L1046】【F:admin/views/dashboard.php†L120-L184】

## 12) Database & Migrations
- ✅ Migrations ensure `guessing_enabled`, `participants_mode`, `affiliate_id`, and hunt↔tournament mapping support via `BHG_DB`. 【F:includes/class-bhg-db.php†L80-L205】【F:includes/class-bhg-db.php†L269-L332】
- ⚠️ Idempotence and live upgrade behavior still require database testing.

## 13) Security & i18n
- ⚠️ Spot checks show sanitization and escaping, but full audit outstanding.

## 14) Backward Compatibility
- ⚠️ Legacy data handling and safe defaults were not regression-tested in this review.

## 15) Global UX Guarantees
- ⚠️ Sorting/search/pagination confirmed for major admin tables, yet shortcode timeline filters and affiliate indicators need frontend verification.

## 16) Release & Docs
- ⚠️ Changelog, readme, and “Info & Help” updates still need manual confirmation.

## 17) QA (Acceptance)
- ⚠️ Winner-limit add-on partially implemented (settings, enforcement hooks, notices) but needs integration testing to confirm rolling windows and messaging. 【F:includes/class-bhg-models.php†L24-L249】【F:includes/helpers.php†L240-L347】
- ⚠️ Other acceptance tests (currency switch, guessing toggle behavior, prizes FE grid/carousel, notifications) not executed.

## Add-On: Winner Limits per User
- ⚠️ Settings page and logging utilities exist for win limits, yet rolling-window accuracy and admin feedback still need end-to-end validation. 【F:includes/helpers.php†L240-L347】【F:includes/class-bhg-models.php†L24-L249】

## Add-On: Jackpot Feature (New Module)
- ❌ No jackpot schema, admin UI, hunt-close logic, or front-end shortcodes exist. Repository searches for “jackpot” only surface QA notes, confirming the module is outstanding. 【7a8486†L1-L10】

---

### Follow-up Actions
1. Add hunt/tournament selectors, timeframe filters, and the specified empty-state copy to the results screen to satisfy section 2.
2. Remove or repurpose the legacy tournament `type` selector per section 3.
3. Deliver the full jackpot module (schema, admin CRUD, hunt-close integration, shortcodes) per the add-on contract.
4. Run PHPCS and execute functional/end-to-end tests for sections still flagged ⚠️.
