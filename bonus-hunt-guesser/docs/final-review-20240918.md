# Bonus Hunt Guesser – Final Verification (2024-09-18)

This audit re-confirms the plugin against every contracted requirement.  Each row lists the requirement, its status, and evidence.  ✅ means satisfied, ⚠️ means partially met (follow-up needed), and ❌ means missing.

| # | Area | Requirement | Status | Evidence / Notes |
|---|------|-------------|--------|------------------|
| 0 | Plugin bootstrap | Header declares version 8.0.16, WP ≥6.3.0, PHP ≥7.4, MySQL ≥5.5.5 | ✅ | Header fields match the contract in `bonus-hunt-guesser.php`.【F:bonus-hunt-guesser.php†L1-L20】 |
| 0 | Plugin bootstrap | Text domain loads on `plugins_loaded` | ✅ | `load_plugin_textdomain()` runs during `bhg_init_plugin()` on the `plugins_loaded` hook.【F:bonus-hunt-guesser.php†L388-L407】 |
| 0 | Plugin bootstrap | Full-project PHPCS passes with zero errors | ❌ | `phpcs` still reports 11,571 errors and 1,478 warnings across 46 files, so the acceptance criterion is unmet.【727053†L1-L24】 |
| 1 | Admin dashboard | “Latest Hunts” card lists the latest three hunts with winners, balances, and closed date | ✅ | `admin/views/dashboard.php` renders the requested table with winner rows and balance columns.【F:admin/views/dashboard.php†L83-L214】 |
| 2 | Bonus Hunts admin | List table includes Final Balance (showing “–” for open hunts) and Affiliate column, plus Edit/Results/Delete/Guessing toggle actions | ✅ | `BHG_Bonus_Hunts_List_Table` wires the new columns and row actions, and formats the Final Balance column per spec.【F:admin/class-bhg-bonus-hunts-list-table.php†L110-L205】 |
| 2 | Bonus Hunts admin | Edit screen shows winners count control and participant list with removable guesses linking to user profiles | ✅ | The edit view appends the participant roster with linked usernames and remove buttons (see bottom section of `admin/views/bonus-hunts-edit.php`).【F:admin/views/bonus-hunts-edit.php†L560-L720】 |
| 2 | Bonus Hunts admin | Results page defaults to latest closed hunt, exposes tournament filter, time filter, empty state, winner highlighting, and Price column | ✅ | The results template implements all requested UI behaviors, including the “There are no winners yet” empty message.【F:admin/views/bonus-hunts-results.php†L60-L290】 |
| 2 | Bonus Hunts admin | Database columns `guessing_enabled` and `affiliate_id` available | ✅ | Schema helper seeds both columns when installing/upgrading the hunts table.【F:includes/class-bhg-db.php†L180-L310】 |
| 3 | Tournaments admin | Title, description, participants mode (`winners` vs `all`), and expanded type options present; edit flow working | ✅ | `admin/views/tournaments.php` renders the required form fields and list tooling with sort/search/pagination.【F:admin/views/tournaments.php†L132-L340】 |
| 3 | Tournaments admin | `participants_mode` column provisioned with default `winners` | ✅ | Table creation defaults `participants_mode` to `winners` in the DB helper.【F:includes/class-bhg-db.php†L312-L380】 |
| 4 | Users admin | Search, sortable columns, pagination (30/page), affiliate toggles on profile | ✅ | `BHG_Users_Table` enforces 30-per-page paging and search; profile view renders affiliate switches.【F:admin/class-bhg-users-table.php†L70-L220】【F:admin/views/users.php†L120-L260】 |
| 5 | Affiliate sync | Admin can manage affiliate websites and selections flow through hunts/tournaments and user profiles | ⚠️ | CRUD UI exists, but automated propagation when sites are added/removed is not re-verified in tests; manual QA still pending.【F:admin/views/affiliate-websites.php†L20-L220】 |
| 6 | Prizes module | Admin CRUD fields, CSS panel, regular/premium prize selectors, and front-end rendering | ✅ | Prize admin view covers all fields and selector panels; helpers expose premium vs regular logic.【F:admin/views/prizes.php†L40-L420】【F:includes/class-bhg-prizes.php†L80-L420】 |
| 6 | Prizes module | Front-end grid/carousel shortcode accepts `size/design/category/active` options | ✅ | `[bhg_prizes]` implementation handles the documented attributes in the shortcode class.【F:includes/class-bhg-shortcodes.php†L40-L360】 |
| 7 | Shortcode catalog | Info & Help screen lists every shortcode with options/examples | ✅ | The documentation view enumerates all registered shortcodes for admins.【F:admin/views/tools.php†L40-L220】 |
| 7 | Shortcodes | Required shortcodes (`bhg_user_profile`, `bhg_active_hunt`, `bhg_guess_form`, `bhg_tournaments`, `bhg_winner_notifications`, `bhg_leaderboards`, extended hunts/tournaments/user guesses/etc.) implemented | ✅ | `BHG_Shortcodes` registers the full shortcode suite with the expected filters/options.【F:includes/class-bhg-shortcodes.php†L360-L1340】 |
| 7 | Pages | Core pages auto-provisioned with override metabox and mapped shortcodes | ✅ | `includes/core-pages.php` seeds all mandated pages with their shortcode payloads.【F:includes/core-pages.php†L40-L220】 |
| 8 | Notifications | Admin tab exposes Bonushunt/Tournament/Winner blocks with enable/BCC controls; mails honour BCC | ⚠️ | UI and hooks exist, but end-to-end QA (sending/receiving with BCC) still outstanding for 8.0.16 sign-off.【F:includes/notifications.php†L40-L260】 |
| 9 | Ranking & points | Editable default point mapping, scope toggle, centralized service, and tests | ✅ | Tournament controller exposes mapping UI and service logic; PHPUnit coverage exists in `CloseHuntTest` / related files.【F:admin/views/tournaments.php†L200-L360】【F:tests/CloseHuntTest.php†L40-L220】 |
| 10 | Global CSS panel | Site-wide typography/background controls applied across front-end components | ✅ | Helper builds global stylesheet from stored settings before enqueuing assets.【F:includes/helpers.php†L420-L620】 |
| 11 | Currency system | Option `bhg_currency`, helpers `bhg_currency_symbol()` & `bhg_format_money()` used for all money output | ✅ | Helpers defined in `includes/helpers.php` and consumed across admin/front-end templates.【F:includes/helpers.php†L120-L210】【F:admin/views/dashboard.php†L120-L205】 |
| 12 | Database & migrations | Core schema adds `guessing_enabled`, `participants_mode`, `affiliate_id`, and hunt↔tournament junction table with idempotent `dbDelta()` | ⚠️ | Existing migrations cover these columns, but new jackpot tables (see §18) remain missing, so DB work is incomplete.【F:includes/class-bhg-db.php†L180-L420】 |
| 13 | Security & i18n | Capability checks, nonces, sanitization, escaping, and `bonus-hunt-guesser` text domain usage | ⚠️ | Majority of surfaces respect caps/nonces, yet numerous PHPCS security warnings remain (e.g., direct SQL without prepare); requires remediation alongside coding-standard cleanup.【727053†L1-L24】 |
| 14 | Backward compatibility | Legacy helpers invoked for old data paths (e.g., dashboard fallback) | ✅ | Dashboard falls back to legacy `bhg_get_latest_closed_hunts()` ensuring older installs keep working.【F:admin/views/dashboard.php†L30-L50】 |
| 15 | Global UX guarantees | Lists and tables honour sorting/search/pagination (30/page) and timeline filters | ✅ | Admin list tables and front-end shortcodes include the required sorting, search, and pagination controls.【F:admin/class-bhg-bonus-hunts-list-table.php†L110-L205】【F:includes/class-bhg-shortcodes.php†L700-L1180】 |
| 16 | Release & docs | Version bump, changelog, Info & Help updated with new modules | ⚠️ | Documentation predates the jackpot module and other outstanding items; changelog lacks migration notes for missing work.【F:docs/customer-requirements-checklist.md†L1-L118】 |
| 17 | QA acceptance | Full scenario QA (hunts closing, currency switch, guessing toggle, participants mode, prizes, notifications, translations) complete | ❌ | No end-to-end acceptance evidence exists for 8.0.16; tests cover some pieces but manual QA backlog remains. |
| 18 | Add-on: Winner limits | Admin settings, enforcement, logging, and skip notices delivered | ✅ | Limit settings stored via settings screen; results view shows skip notices when limits trigger.【F:admin/views/bonus-hunts-results.php†L200-L320】 |
| 19 | Add-on: Front-end adjustments | Table header links styled white; hunts list adds Details column with Guess/Results links | ✅ | Shortcode output injects Details column and CTA logic for open vs closed hunts.【F:includes/class-bhg-shortcodes.php†L960-L1110】 |
| 20 | Add-on: Prizes enhancements | Large image uploads, size labels, prize links/categories, click behaviours, carousel controls, dual regular/premium prize sets | ⚠️ | Admin UI and helpers exist, yet premium prize assignment on the public winners view still lacks automated QA; additional verification needed before marking complete.【F:includes/class-bhg-prizes.php†L420-L720】 |
| 21 | Jackpot module | Admin menu, CRUD, hunt-close integration, jackpots table, and shortcodes (`bhg_jackpot_current`, `bhg_jackpot_latest`, `bhg_jackpot_ticker`, `bhg_jackpot_winners`) | ❌ | No jackpot code paths exist—the only “jackpot” references are in documentation, confirming the feature remains unimplemented.【9d900f†L1-L30】 |

## Outstanding remediation

1. **Resolve WordPress Coding Standards violations** across the codebase so that `phpcs` completes with zero errors or warnings.  This includes fixing tab/space alignment, adding missing docblocks, and converting direct SQL to prepared statements where flagged.【727053†L1-L24】  
2. **Implement the complete jackpot module** (schema, admin CRUD, hunt-close logic, front-end shortcodes, and documentation) as specified in the add-on contract.【9d900f†L1-L30】  
3. **Finish end-to-end QA & documentation** once the above are in place—update changelog, Info & Help, and capture acceptance evidence for hunts, currency switching, guessing toggles, prizes, notifications, translations, and prize premium logic.【F:docs/customer-requirements-checklist.md†L1-L118】

## Test log

| Command | Result |
|---------|--------|
| `./vendor/bin/phpunit` | ✅ All 11 unit tests pass (80 assertions).【130a92†L1-L8】 |
| `./vendor/bin/phpcs --report=summary` | ❌ Fails with 11,571 errors and 1,478 warnings; coding-standard cleanup pending.【727053†L1-L24】 |

