# Bonus Hunt Guesser – Feature Verification

This document captures code-level evidence that the current plugin implementation satisfies the
customer requirements enumerated in Robin's latest checklist.

## Sorting, Search, and Pagination (30 per page)
- `[bhg_user_guesses]` enforces a `LIMIT 30` window, honors `bhg_search`, and flips ordering via
  `bhg_orderby` / `bhg_order` query arguments.
- `[bhg_hunts]` uses identical pagination, search, and sortable headings with 30-row pages.
- `[bhg_tournaments]` exposes the same controls, retaining search filters while paginating at 30
  items per page.
- `[bhg_leaderboards]` supports sortable headers, search boxes, and query-string pagination.

## Timeline Filters
- `BHG_Shortcodes::get_timeline_range()` resolves keywords such as _this week_, _this month_,
  _last year_, and _all time_ to concrete date ranges.
- Each shortcode pulls the chosen timeline from shortcode attributes or `$_GET` parameters and
  constrains the SQL WHERE clause accordingly.

## Affiliate Indicators & Websites
- `bhg_render_affiliate_dot()` renders a green (affiliate) or red (non-affiliate) status badge.
- Hunt, leaderboard, and tournament tables append the indicator beside usernames and can show the
  associated affiliate site name when requested.

## Profile Output
- `[bhg_user_profile]` renders a table with the logged-in user's real name, username, email,
  affiliate status badge, and any linked affiliate websites, plus an edit link when permitted.

## Shortcode Inventory
- `BHG_Shortcodes::__construct()` registers every shortcode mentioned in the requirements, including
  `bhg_user_guesses`, `bhg_hunts`, `bhg_leaderboards`, and legacy aliases for backwards
  compatibility.

