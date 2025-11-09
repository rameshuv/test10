# Bonus Hunt Guesser

**Version:** 8.0.16  
**Requires:** WordPress 6.3.5+, PHP 7.4+, MySQL 5.5.5+

## Overview

Bonus Hunt Guesser delivers the complete workflow agreed with the client for
running bonus hunts, tracking tournaments, and engaging users through
guessing games. Administrators can configure hunts with multi-winner payouts,
manage prizes and jackpots, and publish sortable leaderboards. Logged-in users
submit and revise guesses, review their personal results, and authenticate via
standard WordPress accounts or the customer-provided Nextend social login
integration (Google, Twitch, Kick).

### Core capabilities

- Admin dashboard surfacing recent hunts, winner breakdowns, and key metrics.
- Bonus hunt CRUD with configurable winner counts, prize selection, affiliate
  targeting, and participant roster management.
- Tournament lifecycle tooling with sortable tables, edit support, and
  filters for weekly, monthly, quarterly, yearly, and all-time events.
- User administration with search, sorting, pagination, and affiliate status
  toggles that drive the front-end green/red indicators.
- Prize manager with carousel/grid layouts, CSS controls, and shortcode
  integrations for marketing blocks.
- Jackpot module providing admin CRUD, hunt-close accounting, and
  `[bhg_jackpot_*]` shortcodes.
- Role-aware menus, login redirector, advertising placements, translation
  strings editor, and notification templates.

## Installation & Upgrade

1. Upload the plugin folder to `/wp-content/plugins/` and activate it in the
   WordPress admin.
2. After deployment (fresh install or upgrade), visit **Bonus Hunt →
   Database** and run the tools once. This confirms the `dbDelta()` migrations
   execute on hosts where automatic table updates may be blocked.
3. Configure social login with the purchased Nextend connector so Google,
   Twitch, and Kick accounts can authenticate.
4. Review **Bonus Hunt → Settings** to confirm currency, winner limits, menu
   assignments, and notification templates.

## Release 8.0.16 highlights

- Dashboard “Latest Hunts” card now lists every winner per hunt, showing
  guesses and differences alongside starting/final balances.
- Bonus Hunts admin table exposes final balance, affiliate, configurable
  winner counts, inline results buttons, and participant rosters with
  removable guesses.
- Results view ranks every guess, highlights winners, and supports status and
  pagination filters for historical hunts.
- Tournaments admin includes title/description fields, the expanded type
  selector (weekly/monthly/quarterly/yearly/all time), and functional edit
  flows.
- Users admin table gains keyword search, sortable columns, and 30-per-page
  pagination controls.
- Advertising list shows edit/remove actions and supports a “None” placement
  for shortcode-only ads.
- Jackpot management, prize enhancements, login redirector, translation
  tooling, and affiliate website management are bundled as agreed add-ons.

## Manual QA checklist (recommended)

- Create, edit, and close a hunt with multiple winners; verify results page
  ordering, winner highlighting, and notifications when enabled.
- Toggle the currency option and confirm formatting updates across dashboard,
  leaderboards, and shortcodes.
- Exercise tournament creation/editing, assigning hunts, and reviewing the
  leaderboard filters (overall, monthly, yearly, all-time tabs).
- Validate prize grids/carousels on the frontend for responsiveness, link
  behaviour, and affiliate visibility controls.
- Authenticate with a social provider (via Nextend) and ensure login redirect
  returns to the original protected page.
- Smoke-test advertising placements (“None” + shortcode only, role-aware
  menus, translations editor) to confirm visibility rules.

Documenting evidence (screenshots or logs) of these flows remains part of the
customer’s acceptance checklist for v8.0.16.

## Shortcodes

### `[bhg_user_guesses]`
Display guesses submitted for a specific bonus hunt. Pass `id` to target a hunt; omit it to use the most recent active hunt.

- `timeline`: limit results to `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_hunts]`
List bonus hunts.

- `timeline`: filter hunts created within `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_leaderboards]`
Display overall wins leaderboard.

- `fields`: comma-separated list of columns to render. Allowed values: `pos`, `user`, `wins`, `avg_hunt`, `avg_tournament`, `aff`, `site`, `hunt`, `tournament`. Defaults to `pos`, `user`, `wins`, `avg_hunt`, `avg_tournament`.
- `ranking`: number of top rows to display. Accepts values from `1` to `10` (default `1`).
- `timeline`: limit results to `day`, `week`, `month`, `year`, or the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`.

### `[bhg_tournaments]`
List tournaments or show details.

- `timeline`: limit tournaments by `day`, `week`, `month`, `year`, the legacy aliases `this_week`, `this_month`, `this_year`, `last_year`, or by type keywords `all_time`, `weekly`, `monthly`, `yearly`, `quarterly`, `alltime`.

### `[my_bonushunts]`
Displays the logged-in user's guesses across recent bonus hunts. Results highlight winning placements, show the recorded guess, and display the final balance once a hunt closes. Output honours the My Profile visibility toggles defined under **Settings → Bonus Hunt Guesser → My Profile Blocks**.

### `[my_tournaments]`
Shows each tournament a logged-in user has participated in along with accumulated points, wins, rank, and their most recent scoring event. Only renders when the corresponding profile block is enabled in settings.

### `[my_prizes]`
Lists prizes won by the current user, including the source bonus hunt, prize category, placement, and closed date. The shortcode respects the My Profile visibility toggle and uses the global typography/color overrides configured in plugin settings.

### `[my_rankings]`
Summarises the logged-in user's performance across hunts and tournaments. The block surfaces total hunt wins, tournament points, best rank, and provides tables for individual hunt wins and tournament standings when available.

## Settings highlights

- **My Profile Blocks** — Toggle each of the `[my_*]` shortcodes on or off without editing theme templates.
- **Global Typography & Colors** — Configure shared styles for profile sections, headings, descriptions, and body text. The plugin injects inline CSS so front-end shortcodes immediately reflect the chosen palette.
- **Uninstall safety** — A new "Remove plugin data on uninstall" checkbox controls whether tables/options are removed when the plugin is uninstalled.

## Manual Testing

- **Hunt deletion updates tournaments**
  1. Create a tournament and associate a hunt with winners.
  2. Note the wins recorded for those users on the tournament leaderboard.
  3. Delete the hunt from the admin panel and confirm no SQL errors are displayed.
  4. Reload the tournament leaderboard and verify the affected users have their win counts reduced or removed accordingly.
- **Tournament type persistence**
  1. Create a tournament with start and end dates that span roughly one month.
  2. Visit the `[bhg_tournaments timeline="monthly"]` shortcode output and confirm the tournament appears.
  3. Edit the same tournament (leaving the type selector untouched) and change another field such as the title or description.
  4. Save the changes, refresh the shortcode output, and verify the tournament still appears in the monthly view (demonstrating the stored type was preserved).
  5. Repeat with a weekly-length tournament to confirm the shortcode timeline tabs continue filtering correctly after edits.

