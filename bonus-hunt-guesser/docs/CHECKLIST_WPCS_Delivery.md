# WPCS Delivery Checklist (Internal)

**Target:** WordPress Coding Standards (Core/Extra/Docs) — PHP 7.4, WP 6.3.5+

## A. Input Handling
- [ ] Replace ALL `$_GET` / `$_POST` / `$_REQUEST` / `$_COOKIE` reads with:
  - `wp_unslash( ... )` then `sanitize_text_field` / `absint` / `floatval` / custom validators.
- [ ] Verify nonces on **every** mutating action (`admin_post_*`, AJAX, forms).

## B. Output Escaping
- [ ] `esc_html()` for text nodes, `esc_attr()` for attributes, `wp_kses_post()` for intended HTML.
- [ ] Never echo raw variables; audit `echo`/`printf` occurrences.

## C. Database
- [ ] `$wpdb->prepare()` for any user-influenced VALUES.
- [ ] For dynamic table names (from `$wpdb->prefix` only) with no user input:
  - Add `// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic table name; no user input.`
- [ ] Prefer `dbDelta()` for migrations; version-gate changes.

## D. i18n & Docs
- [ ] All strings wrapped in i18n functions with domain `bonus-hunt-guesser`.
- [ ] Complete DocBlocks for public methods/functions (params, returns).

## E. Coding Style
- [ ] PHPCS: zero **errors**; warnings resolved or annotated with justification.
- [ ] No trailing whitespace; 4-space indents.

## F. Security
- [ ] Cap checks on admin screens (`current_user_can()`).
- [ ] Validate emails for BCC; use `wp_mail()` filters.

## G. QA Smoke
- [ ] Create/close hunt; toggle guessing; award winners; verify UI states.
- [ ] Switch currency; verify across UI.
- [ ] Prizes CRUD and shortcodes render; images responsive.
