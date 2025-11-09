# Bonus Hunt Guesser — v8.0.14 Verification Checklist

- Runtime: PHP 7.4 · WordPress 6.3.0 · MySQL 5.5.5+
- Standards: PHPCS (`WordPress-Extra`, `WordPress-Docs`, `WordPress-Core`)
- Text Domain: `bonus-hunt-guesser`

---

## 0) Plugin Header (proposed, tidy)

- [ ] Header values match spec

  - `Plugin Name: Bonus Hunt Guesser`
  - `Plugin URI: https://yourdomain.com/`
  - `Description: Bonus hunt management…`
  - `Version: 8.0.14` (bump from 8.0.13)
  - `Author: Bonus Hunt Guesser Development Team`
  - `Text Domain: bonus-hunt-guesser`
  - `Domain Path: /languages`
  - `Requires PHP: 7.4`
  - `Requires at least: 6.3.5`
  - `Requires MySQL: 5.5.5`
  - `License: GPLv2 or later`
    - Where to check: main plugin file `bonus-hunt-guesser.php` top header.
    - How to test: Activate plugin → WP Admin > Plugins lists correct metadata.

- [ ] Text domain loaded correctly (`load_plugin_textdomain('bonus-hunt-guesser', false, dirname(plugin_basename(__FILE__)) . '/languages');`)
  - Where: plugin bootstrap/init.
  - How: Ensure no `_` vs `-` mismatch in text-domain usage.

- [ ] PHPCS passes (no errors; warnings acceptable if non-functional)
  - How: `phpcs --standard=WordPress --extensions=php .`

---

## 1) BHG Dashboard (winners list)

- [ ] Each winner appears on a separate row
  - Where: Admin > BHG Dashboard page.
  - How: Inspect table markup; no concatenated winners in a single cell.

- [ ] Winner username is bold
  - How: CSS/markup ensures `<strong>` or appropriate class applied.

---

## 2) `bhg-bonus-hunts` (Add/Edit)

- [ ] Tournament multiselect shows only active tournaments
  - Where: Add/Edit Bonus Hunt screen.
  - How: Confirm query filters `status=active`; verify inactive tournaments are excluded.

---

## 3) `bhg-bonus-hunts-results`

- [ ] Row colors consistent across hunts
  - How: Compare multiple hunts; no per-hunt color drift.

- [ ] Table rows are grey/white (not grey/green)
  - How: Confirm CSS variables/classes.

- [ ] All winners highlighted (green + bold)
  - How: Winners for each hunt receive highlight class; amount of winners varies per hunt.

- [ ] “Price” column present (Heading “Price”; price title in table output for winners)
  - How: Column exists; values render for winners.

---

## 4) `bhg-tournaments` (Connected Bonus Hunts)

- [ ] Connected Bonus Hunts selector shows only this year’s hunts + already connected ones
  - How: Filter by current year; ensure already-connected hunts remain visible even if not current year.

---

## 5) `bhg-users` (User Profile Affiliates)

- [ ] Affiliate yes/no field created per affiliate website (from `bhg-affiliates`)
  - Where: User profile screen.
  - How: Each affiliate site exposes a toggle/checkbox on the user profile.

---

## 6) `bhg-affiliates` (Sync to User Profile)

- [ ] Adding an affiliate website adds corresponding field to all user profiles
  - How: Create affiliate site → verify new field appears on user profiles.

- [ ] Removing an affiliate website removes the field from user profiles
  - How: Delete affiliate site → field disappears / values cleaned up.

---

## 7) `bhg-prizes` (Admin)

- [ ] “Prizes” menu exists (Add/Edit/Remove)
- [ ] Fields: title, description, category (`cash money`, `casino money`, `coupons`, `merchandise`, `various`), image
- [ ] Images generated/available in 3 sizes: small, medium, big
- [ ] Frontend CSS panel options present: border, border color, padding, margin, background color
- [ ] Active yes/no toggle works (controls availability)
  - How: CRUD flow works; image sizes created (check WP image sizes or custom).

---

## 8) Prizes in Bonus Hunt (Admin)

- [ ] Can select 1+ prizes when creating/editing a Bonus Hunt
  - How: Multiselect populated from `bhg-prizes`; saves & restores.

---

## 9) Prizes — Frontend (Active Hunt)

- [ ] Prizes render on active hunt(s)
- [ ] Grid list or horizontal carousel view selectable
- [ ] Carousel has dots and/or left/right arrows
  - How: Toggle view via setting; check both layouts on active hunts.

---

## 10) Prizes Shortcode

- [ ] Shortcode available (e.g., `[bhg_prizes category="" design="" size="" active=""]`)
- [ ] Attributes work: `category` (taxonomy/enum), `design` (`grid`|`carousel`), `size` (`small`|`medium`|`big`), `active` (`yes`|`no`)
  - How: Place on a page; confirm filtering & rendering.

---

## 11) User Shortcodes (Frontend, My Profile)

- [ ] `[my_bonushunts]` — lists all participated hunts + ranking
- [ ] `[my_tournaments]` — lists all participated tournaments + ranking
- [ ] `[my_prizes]` — lists all won prizes
- [ ] `[my_rankings]` — lists all rankings (hunts/tournaments)
- [ ] Admin option to hide/show each block (controls frontend visibility)
  - How: Toggle in admin; confirm appearance/disappearance in profile.

---

## 12) Global CSS/Color Panel

- [ ] Title block controls: background color, border radius, padding, margin
- [ ] H2 controls: font size, font-weight, color, padding, margin
- [ ] H3 controls: font size, font-weight, color, padding, margin
- [ ] Description controls: font size, font-weight, color, padding, margin
- [ ] p/span standard fields: font size, padding, margin
  - How: Change settings; confirm live in frontend components.

---

## 13) `bhg-shortcodes` (Admin)

- [ ] Shortcodes menu shows all available shortcodes + their options
  - How: “Info & Help” block present with examples/params.

---

## 14) Notifications (`bhg-notifications`)

- [ ] Notifications tab exists in admin

### 8.1 Winners notifications

- [ ] Editable title + description (HTML)
- [ ] BCC field present
- [ ] Enable/Disable checkbox (default: disabled)

### 8.2 Tournament notifications

- [ ] Editable title + description (HTML)
- [ ] BCC field present
- [ ] Enable/Disable checkbox (default: disabled)

### 8.3 Bonushunt notifications

- [ ] Editable title + description (HTML)
- [ ] BCC field present
- [ ] Enable/Disable checkbox (default: disabled)

**Delivery check:**

- [ ] Mail uses `wp_mail()` with headers (BCC honored), proper escaping, and filters/hooks for overrides.

---

## 15) Tournaments — Prizes & Affiliate Fields

- [ ] Admin + Frontend tournament detail shows prizes (when linked)
- [ ] Affiliate website field in tournament admin (create/edit)
- [ ] Show/Hide affiliate URL checkbox (default: show) controls frontend output
  - How: Toggle and verify.

---

## 16) Tournament Ranking (Points System)

- [ ] Points mapping exists and is editable:
  - Default: 1st=25, 2nd=15, 3rd=10, 4th=5, 5th=4, 6th=3, 7th=2, 8th=1
- [ ] Scope toggle: apply to active/closed/all hunts
- [ ] Ranking sources only winners from selected hunts (if a hunt has 3 winners, only those 3 get points)
- [ ] Backend + Frontend tournament results based on computed points
- [ ] Bonus Hunt ranking (backend + frontend) based on points
- [ ] Winners highlighted; Top 3 extra highlighted
  - How: Close several hunts, mark winners with ranks, verify totals & highlight logic.

---

## 17) Data Integrity & Performance

- [ ] DB schema migrations up to date (new tables/columns for prizes, notifications, affiliate fields)
- [ ] Foreign keys / indexes added where needed (read-heavy lists paginated)
- [ ] No N+1 queries on prize/tournament/leaderboard listings
- [ ] Caching (transients/object cache) used for heavy reads where safe
- [ ] Uninstall cleanup respects user data (settings removable via confirm or retained by default)

---

## 18) Security & i18n

- [ ] Capability checks on all admin pages (`manage_options` or custom caps)
- [ ] Nonces on all write actions
- [ ] Escaping (`esc_html`, `esc_attr`, `wp_kses_post`) on output
- [ ] Sanitization/Validation on input (incl. BCC emails)
- [ ] All strings translatable with `bonus-hunt-guesser` text-domain

---

## 19) Backward Compatibility

- [ ] Previous hunts/tournaments/users load without errors
- [ ] Missing new settings have safe defaults
- [ ] Deprecated options mapped/migrated

---

## 20) Release & Docs

- [ ] Version bumped to 8.0.14 (header + any constants)
- [ ] CHANGELOG updated (what changed, migration notes)
- [ ] Readme / Admin “Info & Help” updated (shortcodes, attributes, CSS panel, notifications)
- [ ] Screenshots/GIFs (optional) for new UI

---

### Quick Dev Hints (optional to keep in your Codex)

- Active tournaments filter: ensure query like `post_status=publish` + meta `active=1`.
- This-year hunts: filter by date (year equals current) or stored `year` meta.
- Carousel: prefer a no-jQuery, accessible slider; expose a fallback grid.
- Points engine: centralize in a service; unit test rank → points mapping & totals.
- PHPCS: whitelist specific lines only when necessary; prefer refactors.
- Mail: use filters `bhg_mail_headers`, `bhg_mail_subject`, `bhg_mail_message` for extensibility.
