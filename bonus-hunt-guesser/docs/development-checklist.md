# Bonus Hunt Guesser – v8.0.14 Delivery Checklist

Use this checklist to track every customer-mandated requirement for version **8.0.14**.
Check off an item only after it is implemented, tested on PHP 7.4 / WordPress 6.3.0 / MySQL 5.5.5,
meets WordPress coding standards (`WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`), and the
referenced file(s) have been reviewed.

---

## 0) Plugin Header & Compatibility
- [ ] Update the main plugin header to match the provided template (metadata, requirements, etc.). *(File: `bonus-hunt-guesser.php`)*
- [ ] Verify the documented compatibility matrix in project docs (`Requires at least`, `Tested up to`, PHP/MySQL versions). *(Files: `README.md`, `readme.txt`, `composer.json`)*

## 1) Prizes (Admin: `bhg-prizes`)
- [ ] Add "Prizes" top-level (or submenu) item in the WordPress admin. *(Files: `admin/class-bhg-admin-menu.php`, `admin/views/prizes/list.php`)*
- [ ] Provide add/edit/remove screens for prizes with fields: title, description, category (`cash money`, `casino money`, `coupons`, `merchandise`, `various`), image picker (small/medium/big), CSS panel (border, border color, padding, margin, background color), active toggle. *(Files: `includes/class-bhg-prizes.php`, `admin/views/prizes/edit.php`, `assets/css/admin-prizes.css`)*
- [ ] Ensure images are generated/accessed in three sizes (small/medium/big). *(Files: `includes/class-bhg-prizes.php`, `includes/class-bhg-image.php`)*

## 2) Bonus Hunts ↔ Prizes (`bhg-bonus-hunts`)
- [ ] Allow admins to assign one or multiple prizes to each bonus hunt during creation/edit. *(Files: `admin/views/bonus-hunts/edit.php`, `includes/class-bhg-bonus-hunts.php`)*

## 3) Prizes Frontend Display
- [ ] Output prizes on active hunts with selectable layout: grid list or horizontal carousel (with dots and/or left/right navigation). *(Files: `includes/shortcodes/class-bhg-shortcode-prizes.php`, `templates/prizes/grid.php`, `templates/prizes/carousel.php`, `assets/css/frontend-prizes.css`, `assets/js/frontend-prizes.js`)*

## 4) Prizes Shortcode
- [ ] Deliver `[bhg_prizes]` (name optional) shortcode supporting filters `category=""`, `design="grid|carousel"`, `size="small|medium|big"`, `active="yes|no"`. *(Files: `includes/shortcodes/class-bhg-shortcode-prizes.php`, `includes/class-bhg-shortcodes.php`)*

## 5) User Shortcodes (Frontend)
- [ ] Provide shortcodes for user dashboards: `my_bonushunts`, `my_tournaments`, `my_prizes`, `my_rankings`. *(Files: `includes/shortcodes/`)*
- [ ] Add admin controls to hide/show each shortcode output section. *(Files: `admin/class-bhg-settings.php`, `admin/views/settings/shortcodes.php`)*

## 6) CSS / Color Panel
- [ ] Build styling controls for: title block (background, border radius, padding, margin), `h2`, `h3`, description text, and standard text (`p`, `span`) with font size/weight/color/padding/margin. *(Files: `admin/views/settings/design.php`, `assets/css/frontend-customizer.css`, `includes/class-bhg-settings.php`)*

## 7) Shortcodes Reference (`bhg-shortcodes`)
- [ ] Create admin Info & Help screen listing all available shortcodes with options (block title: "Info & Help"). *(Files: `admin/views/shortcodes/info.php`, `admin/class-bhg-shortcodes.php`)*

## 8) Notifications (`bhg-notifications`)
### Winner Notifications
- [ ] Add admin block with editable title, HTML description, BCC field, and enable/disable checkbox (default disabled). *(Files: `admin/views/notifications/winners.php`, `includes/class-bhg-notifications.php`)*
### Tournament Notifications
- [ ] Mirror the configuration for tournament notifications. *(Files: `admin/views/notifications/tournaments.php`, `includes/class-bhg-notifications.php`)*
### Bonus Hunt Notifications
- [ ] Mirror the configuration for new bonus hunt notifications. *(Files: `admin/views/notifications/bonus-hunts.php`, `includes/class-bhg-notifications.php`)*

## 9) Tournaments
- [ ] Attach selected prizes to tournaments in admin and show them on the frontend details page. *(Files: `admin/views/tournaments/edit.php`, `templates/tournament/single.php`, `includes/class-bhg-tournaments.php`)*
- [ ] Add affiliate website field plus show/hide checkbox (default: show) to the tournament form and output. *(Files: `admin/views/tournaments/edit.php`, `includes/class-bhg-tournaments.php`, `templates/tournament/single.php`)*

## 10) Tournament Ranking
- [ ] Implement editable points system (default: `25,15,10,5,4,3,2,1`) with scope options for active/closed/all hunts. *(Files: `admin/views/tournaments/settings-ranking.php`, `includes/class-bhg-ranking.php`)*
- [ ] Base tournament results on winners selected from associated bonus hunts; highlight all winners (especially top 3) on backend/frontend. *(Files: `includes/class-bhg-ranking.php`, `templates/tournament/results.php`, `admin/views/bonus-hunts/results.php`)*

---

## Core Bonus Hunt & Guessing System
- [ ] Admin bonus hunt form collects title, starting balance, number of bonuses, prizes text, final balance (if closed). *(Files: `admin/views/bonus-hunts/edit.php`)*
- [ ] Logged-in users can submit and edit guesses (0–100,000) while the hunt is open; validation enforced. *(Files: `includes/class-bhg-guesses.php`, `templates/bonus-hunt/guess-form.php`)*
- [ ] Display guesses in leaderboard with position, username, guess, difference from final balance. *(Files: `templates/bonus-hunt/leaderboard.php`, `assets/css/frontend-leaderboard.css`)*
- [ ] Support configurable winner count (1st to 25th); admin dashboard shows all winners per hunt. *(Files: `admin/views/dashboard/latest-hunts.php`, `includes/class-bhg-dashboard.php`)*
- [ ] Rename admin submenu "bonushunt" to "dashboard" and replace "Recent Winners" with "Latest Hunts" table (Title, All Winners with guess & diff, Start Balance, Final Balance, Closed At). *(Files: `admin/class-bhg-admin-menu.php`, `admin/views/dashboard/index.php`)*
- [ ] Add "Results" action for finished hunts showing ranked guesses with highlighted winners. *(Files: `admin/views/bonus-hunts/results.php`, `admin/class-bhg-bonus-hunts.php`)*
- [ ] When editing a hunt, list all participants with ability to remove guesses; usernames link to user profile editor. *(Files: `admin/views/bonus-hunts/edit.php`, `admin/class-bhg-users.php`)*
- [ ] Include final balance column in the admin hunts list (`-` if open). *(Files: `admin/views/bonus-hunts/list.php`)*

## User Profiles & Enhancements
- [ ] Admin can manage user fields: real name, username, email, affiliate status. *(Files: `admin/views/users/edit.php`, `includes/class-bhg-user-meta.php`)*
- [ ] Integrate Nextend Social Login (Google, Twitch, Kick) with hooks for compatibility. *(Files: `includes/integrations/class-bhg-nextend.php`)*
- [ ] Highlight affiliates with green indicator, non-affiliates with red on leaderboards. *(Files: `assets/css/frontend-leaderboard.css`, `templates/bonus-hunt/leaderboard.php`)*
- [ ] Leaderboard table supports sorting (position, username, guess) and pagination. *(Files: `assets/js/frontend-leaderboard.js`, `templates/bonus-hunt/leaderboard.php`)*
- [ ] `bhg-users` admin screen includes search, sort, and pagination (30 per page). *(Files: `admin/views/users/list.php`, `admin/class-bhg-users.php`)*

## Tournaments & Leaderboards
- [ ] Support tournament types weekly, monthly, quarterly, yearly, all-time; remove redundant period field. *(Files: `includes/class-bhg-tournaments.php`, `admin/views/tournaments/edit.php`)*
- [ ] Add missing tournament title & description fields; ensure edit/save flows work. *(Files: `admin/views/tournaments/edit.php`, `includes/class-bhg-tournaments.php`)*
- [ ] Provide sortable leaderboards (position, username, wins) with filters for week/month/year and history access. *(Files: `templates/tournament/leaderboard.php`, `assets/js/frontend-tournaments.js`)*

## Frontend Leaderboard Enhancements
- [ ] Add tabs for best guessers (Overall, Monthly, Yearly, All-Time). *(Files: `templates/bonus-hunt/leaderboard-tabs.php`, `assets/js/frontend-leaderboard.js`)*
- [ ] Add tabs for viewing leaderboard history across previous bonus hunts. *(Files: `templates/bonus-hunt/history-tabs.php`, `includes/class-bhg-leaderboard.php`)*

## User Experience Improvements
- [ ] Implement smart redirect after login to return users to the originally requested page. *(Files: `includes/class-bhg-auth.php`)*
- [ ] Configure three WordPress menus (Admin/Moderator, Logged-in Users, Guests) with styling aligning to site theme. *(Files: `admin/views/settings/menus.php`, `assets/css/frontend-navigation.css`)*
- [ ] Provide "Translations" admin tab for managing all plugin text strings. *(Files: `admin/views/translations/index.php`, `includes/class-bhg-translations.php`)*

## Affiliate Enhancements
- [ ] Allow admins to manage multiple affiliate websites (add/edit/delete). *(Files: `admin/views/affiliates/list.php`, `includes/class-bhg-affiliates.php`)*
- [ ] Select affiliate site(s) during bonus hunt creation and show per-user assignments in profiles (Affiliate Website 1, 2, 3…). *(Files: `admin/views/bonus-hunts/edit.php`, `admin/views/users/edit.php`, `includes/class-bhg-affiliates.php`)*
- [ ] Reflect affiliate data per hunt on frontend (indicator + ad targeting adjustments). *(Files: `templates/bonus-hunt/leaderboard.php`, `includes/class-bhg-ads.php`)*

## Notifications & Communication
- [ ] Calculate winners based on proximity to final balance (support multiple winners per hunt). *(Files: `includes/class-bhg-winners.php`)*
- [ ] Send result/winner emails honoring enable flags and BCC addresses. *(Files: `includes/class-bhg-notifications.php`)*

## Advertising Module (`bhg-ads`)
- [ ] Allow creation of ads with text, optional link, placement (including `none`), and visibility rules (by login + affiliate status). *(Files: `admin/views/ads/edit.php`, `includes/class-bhg-ads.php`)*
- [ ] Add Actions column to ads list with Edit and Remove buttons. *(Files: `admin/views/ads/list.php`)*

## Tools & Translations Screens
- [ ] Populate `bhg-translations` and `bhg-tools` admin tabs with meaningful management interfaces rather than blank views. *(Files: `admin/views/translations/index.php`, `admin/views/tools/index.php`, `includes/class-bhg-translations.php`, `includes/class-bhg-tools.php`)*

## Quality & Polish
- [ ] Apply border styling to Bonus Hunt admin input fields. *(Files: `assets/css/admin-bonus-hunt.css`)*
- [ ] Run PHPCS (`WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`) and address violations. *(Entire codebase)*
- [ ] Fix outstanding bugs and optimize performance as issues are discovered during QA. *(Entire codebase)*

---

## Quick Reference: Backend Improvements (Customer Feedback 04 Sep)
- [ ] Dashboard submenu rename + "Latest Hunts" widget showing latest 3 hunts with all winners, start/final balances, closed date. *(Files: `admin/class-bhg-admin-menu.php`, `admin/views/dashboard/index.php`, `admin/views/dashboard/latest-hunts.php`)*
- [ ] Bonus hunt "Results" action with ranked guesses; configurable winner count; participant list with removal/user links; admin list final balance column. *(Files: `admin/views/bonus-hunts/list.php`, `admin/views/bonus-hunts/edit.php`, `admin/views/bonus-hunts/results.php`, `includes/class-bhg-bonus-hunts.php`)*
- [ ] Tournaments admin fixes: add title, description, types (weekly/monthly/quarterly/yearly/alltime), remove period field, repair edit/save logic. *(Files: `admin/views/tournaments/edit.php`, `includes/class-bhg-tournaments.php`)*
- [ ] Users admin: search, sort, pagination (30 per page). *(Files: `admin/views/users/list.php`, `admin/class-bhg-users.php`)*
- [ ] Ads admin: add Actions (edit/remove) column and `none` placement option. *(Files: `admin/views/ads/list.php`, `includes/class-bhg-ads.php`)*
- [ ] Populate `bhg-translations` and `bhg-tools` screens with appropriate data instead of empty placeholders. *(Files: `admin/views/translations/index.php`, `admin/views/tools/index.php`)*

