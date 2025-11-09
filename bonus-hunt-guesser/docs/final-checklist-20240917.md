# Bonus Hunt Guesser — Final Compliance Checklist (2024-09-17)

Status legend:

* ✅ — Requirement fully satisfied and verified during this review.
* ⚠️ — Requirement implemented but needs additional QA or polish.
* ❌ — Requirement missing or non-compliant.

The checklist below consolidates every contractual deliverable that remains in scope for release **8.0.16**. Each row links back to the corresponding implementation area so follow-up work can be assigned quickly.

## 0. Bootstrap & Tooling

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Plugin header advertises version 8.0.16, PHP 7.4, WP 6.3.0, MySQL 5.5.5 | ✅ | `bonus-hunt-guesser.php` lines 3‑19. |
| Text domain loads on `plugins_loaded` | ✅ | `bonus-hunt-guesser.php` line 140 loads translations. |
| PHPCS (WordPress-Core) passes with no errors | ❌ | `./vendor/bin/phpcs` reports >1500 spacing/indentation issues across admin views, controllers, and tests. |

## 1. Admin Dashboard

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| “Latest Hunts” lists latest 3 hunts with required columns | ⚠️ | `admin/views/dashboard.php` outputs hunts but needs data verification. |
| Each winner on separate row, usernames bold, balances left-aligned | ⚠️ | Styling present; confirm with real dataset. |

## 2. Bonus Hunts (Admin)

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| List columns & actions (Final Balance, Affiliate, Edit/Results/Delete/Toggle) | ⚠️ | `admin/views/bonus-hunts.php` implements layout; QA pending. |
| Edit screen (active tournaments only, winners count, participant list) | ✅ | `admin/views/bonus-hunts-edit.php`. |
| Results view (default latest closed, filters, highlights, prize column) | ⚠️ | `admin/views/bonus-hunts-results.php`; requires UX sign-off. |
| DB columns `guessing_enabled`, `affiliate_id`, premium/regular prize links | ✅ | `includes/class-bhg-db.php`, `includes/class-bhg-prizes.php`. |

## 3. Tournaments (Admin)

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Title & description fields, expanded type list, participants-mode toggle | ✅ | `admin/views/tournaments.php`. |
| Remove legacy period column / ensure migration | ⚠️ | `includes/class-bhg-db.php` drops column but migration not regression-tested. |
| Actions (Edit, Results, Close, Delete) | ⚠️ | Buttons wired; manual QA pending. |

## 4. Users (Admin)

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Search, sortable columns, pagination | ✅ | `admin/class-bhg-users-table.php`. |
| Profile affiliate toggles per site | ⚠️ | `admin/views/users.php`; persistence not re-tested. |

## 5. Affiliates

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Add/remove sites syncs user meta | ⚠️ | `includes/helpers.php` exposes sync helpers; integration test missing. |
| Frontend affiliate lights & optional website display | ✅ | `includes/class-bhg-shortcodes.php` leaderboard renderers. |

## 6. Prizes

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Admin CRUD (fields, CSS panel, active flag) | ✅ | `admin/views/prizes.php`. |
| Large image upload (1200×800), labels for sizes | ⚠️ | Need QA for WP media constraints. |
| Dual prize sets (regular/premium) selection & display | ⚠️ | Logic present in `BHG_Prizes` & results template; acceptance pending. |
| Shortcode `[bhg_prizes]` parameters | ⚠️ | Implementation exists; automated tests missing. |

## 7. Shortcodes & Core Pages

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Admin “Info & Help” lists all shortcodes | ⚠️ | `admin/views/tools.php`; ensure completeness. |
| Extended shortcodes (`bhg_user_guesses`, `bhg_hunts`, etc.) | ⚠️ | Core functionality implemented; thorough QA needed. |
| New advertising shortcode placement “none” | ✅ | Advertising admin view supports it. |
| Core pages (Active Hunt, Tournaments, Leaderboards, etc.) auto-created | ⚠️ | `includes/core-pages.php`; requires smoke test. |

## 8. Notifications

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Winners/Tournament/Bonushunt blocks with enable + BCC | ⚠️ | `includes/notifications.php`; deliverability not validated. |
| Uses `wp_mail()` with filters | ✅ | Same file handles filters. |

## 9. Ranking & Points

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Editable points map, scope toggle, winners-only points | ⚠️ | UI available; business logic partly covered but untested. |
| Backend + frontend rankings highlight winners/top 3 | ⚠️ | Implementation in `BHG_Tournaments` modules; requires QA. |
| Centralized service with unit tests | ⚠️ | Tests partially cover ranking but not exhaustive. |

## 10. Global CSS Panel

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Title/H2/H3/description/text controls applied globally | ⚠️ | CSS generation exists in helpers; cross-shortcode verification outstanding. |

## 11. Currency System

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| `bhg_currency` option + helpers (`bhg_currency_symbol`, `bhg_format_money`) | ✅ | `includes/helpers.php`. |
| All money outputs use helper | ⚠️ | Spot-checks positive, but full audit incomplete. |

## 12. Database & Migrations

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Ensure `guessing_enabled`, `participants_mode`, `affiliate_id` columns | ✅ | `BHG_DB::create_tables()`. |
| Junction table hunt ↔ tournament | ✅ | `bhg_tournaments_hunts`. |
| Jackpot tables & migrations | ❌ | No jackpot schema in current migrations. |

## 13. Security & i18n

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Caps, nonces, sanitization/escaping | ⚠️ | Present in many handlers but PHPCS flags several direct queries lacking prepare(). |
| BCC email validation | ⚠️ | Basic validation in notifications; add stricter checks. |
| Strings localized with `bonus-hunt-guesser` text domain | ⚠️ | Majority covered; audit recommended. |

## 14. Backward Compatibility

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Legacy data loads with safe defaults | ⚠️ | Some fallbacks exist, but migrations need regression testing. |

## 15. Global UX Guarantees

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Sorting/search/pagination across admin/shortcode tables | ⚠️ | List tables implement features; front-end tables need QA. |
| Timeline filters (week/month/year/last year/all time) | ⚠️ | Filters present in results templates; coverage incomplete. |
| Affiliate lights + website display | ✅ | Front-end templates render dots/names. |
| Profile blocks show real name/email/affiliate | ⚠️ | Profile template present; verify data sync. |

## 16. Release & Documentation

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Version bumped to 8.0.16 everywhere | ⚠️ | Header updated; README/CHANGELOG require update. |
| Changelog documents migrations | ❌ | `CHANGELOG.md` predates jackpot/currency additions. |
| Readme & Info/Help updated with new features | ❌ | Documentation still references older feature set. |

## 17. QA Acceptance

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| End-to-end hunt lifecycle (create → close → winners) | ❌ | No automated/recorded QA run. |
| Currency switch reflects across admin/front-end | ⚠️ | Helper ready; QA missing. |
| Guessing toggle enforcement | ⚠️ | Admin toggle implemented; acceptance pending. |
| Participants mode respected in tournament results | ⚠️ | Logic in results view; requires testing. |
| Prizes CRUD + FE grid/carousel + CSS panel | ⚠️ | Implementation present, QA pending. |
| Notifications BCC + enable/disable | ⚠️ | Implementation present; real mail flow unverified. |
| Translations load & strings editable | ⚠️ | Translation tables exist; no QA proof. |

## Add-On: Winner Limits per User

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Admin settings (limits + rolling period per type) | ⚠️ | Settings page contains fields; persistence confirmed but QA outstanding. |
| Backend enforcement skips ineligible winners and shows notices | ⚠️ | Logic flags ineligible entries; needs integration testing. |
| Win logging for rolling window | ⚠️ | Logs stored; verify report accuracy. |

## Add-On: Front-End Adjustments

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Table header links styled white (#fff) | ⚠️ | CSS adjustments exist; verify across templates. |
| `[bhg_hunts]` includes Details column with context-sensitive links | ✅ | `BHG_Shortcodes::render_hunts_table()`. |

## Add-On: Prizes Enhancements

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Prize link field & category management (with optional links) | ⚠️ | Admin UI added; front-end click behavior requires QA. |
| Image click behavior options | ⚠️ | Settings stored; front-end JS verification pending. |
| Carousel controls (visible count, total load, auto-scroll) | ⚠️ | Options stored; need UI test. |
| Responsive display (size by count) | ⚠️ | Helper present; device testing missing. |
| Remove automatic “Prizes” heading | ✅ | Template no longer injects static heading. |

## Jackpot Feature (New Module)

| Requirement | Status | Evidence / Notes |
| --- | --- | --- |
| Admin menu & CRUD for jackpots | ❌ | No jackpot admin screen or storage implemented. |
| Jackpot fields (title, start amount, linked hunts, increase amount) | ❌ | No schema or form. |
| List view of latest jackpots | ❌ | Missing. |
| Logic: hit on exact guess, otherwise increase by configured amount | ❌ | Hunt close flow lacks jackpot hooks. |
| Currency uses global helper | ❌ | No jackpot amounts displayed. |
| Shortcodes (`bhg_jackpot_current`, `bhg_jackpot_latest`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`) | ❌ | Not registered anywhere. |

## Final Actions Required Before Release

1. **Implement the jackpot module** — schema, admin CRUD, hunt-close integration, and all four shortcodes remain outstanding.
2. **Achieve full PHPCS compliance** — resolve the outstanding spacing/indentation errors and address direct SQL warnings by introducing `$wpdb->prepare()` or documented esc_sql usage.
3. **Complete QA + documentation** — execute end-to-end acceptance scenarios, update changelog/README/Info & Help, and capture evidence for translation, notification, and prizes enhancements.

## Verification Commands

| Command | Result |
| --- | --- |
| `composer install` | ✅ |
| `./vendor/bin/phpunit` | ✅ |
| `./vendor/bin/phpcs` | ❌ — extensive violations remain. |

