# Bonus Hunt Guesser — Final Delivery (v8.0.16)

This build is aligned to the customer checklist and our agreed decisions:
- Prizes: **switchable `design="grid|carousel"`**
- UI: **Modern Minimal Clean**, inherits existing site accent automatically
- Profile: **Stats + badges + ranking highlights**

## Shortcodes (ready to use)

- **Prizes**
  ```
  [bhg_prizes category="" design="grid|carousel" size="small|medium|big"
    active="yes|no" visible="3" total="12" auto="off" dots="on" arrows="on"
    click="popup|same|new" show_title="on|off" show_category="on|off" show_description="on|off"]
  ```
  **Responsive rule:** visible=1→big, 2–3→medium, 4–5→small.

- **Core pages**
  - Active Bonus Hunt: `[bhg_active_hunt]` + `[bhg_guess_form]`
  - All Bonus Hunts: `[bhg_hunts]`
  - Tournaments: `[bhg_tournaments]`
  - Leaderboards: `[bhg_leaderboards]`
  - User Guesses: `[bhg_user_guesses]`
  - My Profile: `[bhg_user_profile]`
  - Prizes: `[bhg_prizes]`
  - Advertising: `[bhg_advertising]`

## Admin > Where to find things
- **Bonus Hunts:** Enable/Disable guessing toggle on list & edit pages; participants list under the edit form.
- **Results:** Time filters and empty-state appear on the Results view; winners highlighted and Price column visible when set.
- **Tournaments:** Participants Mode (`winners`/`all guessers`) is available on edit and enforced in standings.
- **Prizes:** Full CRUD, Category manager, Prize Link + click behaviour, CSS panel, dual Prize Sets (Regular + Premium).
- **Notifications:** Winners/Tournament/Bonushunt templates with HTML body and BCC validation; Enable/Disable flags per type.
- **Ranking:** Points mapping (defaults 25/15/10/5/4/3/2/1), timeline filters, Top-3 highlight.

## Database
Plugin will update/create tables and columns on activation (idempotent), including:
- `guessing_enabled`, `participants_mode`, `affiliate_id`
- Hunt ↔ Tournament junction table

## QA Acceptance
- Prizes Grid & Carousel render; image sizes respect `visible`.
- Guessing toggle blocks/unblocks forms.
- Results time filters change scope; empty states shown when no winners.
- Tournaments Participants Mode changes result & ranking inclusion.
- Ranking points and Top-3 highlights render.
- Notifications send with BCC (validated).

## Notes
- PHPCS (Core/Extra/Docs) targeted clean; code follows WPCS patterns (sanitization, escaping, `$wpdb->prepare()`).
- UI inherits the site's active accent color (Customizer/CSS variables) automatically.
