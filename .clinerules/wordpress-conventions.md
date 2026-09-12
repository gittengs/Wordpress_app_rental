---
description: WordPress plugin coding standards and project conventions for the Bike Rental plugin.
alwaysApply: true
---

# WordPress Plugin Conventions — Bike Rental

These rules apply to all code in this repository. Follow them without exception.

## Project Identity

- **Plugin name:** Bike Rental
- **Plugin slug / folder:** `bike-rental/`
- **Text domain:** `bike-rental`
- **PHP namespace:** `BikeRental\`
- **Constant prefix:** `BIKE_RENTAL_`
- **Meta key prefix:** `_bike_` / `_booking_`
- **Function/class prefix (global scope):** `bike_rental_`

## Technology

- PHP **7.4+** compatible (no PHP 8-only syntax such as enums, readonly, constructor
  promotion, or `match` unless guarded).
- WordPress **6.0+**.
- Vanilla PHP + WordPress APIs only. **No Composer or JS build tooling** unless the
  Memory Bank is updated to adopt it first.
- The plugin must remain portable: it can be dropped into `wp-content/plugins/`.

## Codestyle

- Follow the [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- Tabs for indentation, Yoda conditions, snake_case for functions/variables,
  `Class_Names_With_Underscores`.
- File names: `class-<kebab-case>.php` for classes (e.g. `class-post-types.php`).
- Every PHP file starts with a `defined( 'ABSPATH' ) || exit;` guard.
- Full DocBlocks on every class, method, and property.

## Architecture

- OOP with a PSR-4-style autoloader defined in the main plugin file. Classes map as
  `BikeRental\Post_Types` → `includes/class-post-types.php`.
- Each feature class exposes a `register_hooks()` method. Nothing executes on file
  load; all work is attached to WordPress hooks.
- Bootstrap is `BikeRental\Plugin::instance()->init()`, called on `plugins_loaded`.
- Activation/deactivation/uninstall logic lives in the `Plugin` class and
  `uninstall.php` respectively.

## Security (non-negotiable)

- **Capabilities:** check `current_user_can()` before any privileged action.
- **Nonces:** use `wp_nonce_field()` / `check_admin_referer()` for forms, and
  `wp_verify_nonce()` + `check_ajax_referer()` for AJAX/REST.
- **Sanitize all input:** `sanitize_text_field()`, `absint()`, `sanitize_email()`,
  `sanitize_key()`, etc.
- **Escape all output:** `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` —
  escape late, as close to rendering as possible.
- Never trust `$_POST`, `$_GET`, `$_REQUEST`, or `$_SERVER` directly.

## Internationalization

- Every user-facing string MUST be wrapped: `__( 'Text', 'bike-rental' )`,
  `esc_html__()`, `esc_attr__()`, `_n()`, etc.
- Domain path: `/languages`.

## Data

- Store custom fields as post meta with a leading underscore (`_bike_daily_rate`) so
  they are hidden from the default custom-fields UI.
- Register meta with the REST API where it needs to be readable/writable over REST.
- Never modify WordPress core or bundled themes.
