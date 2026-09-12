# Changes to Bike Rental

All notable changes to this project are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial plugin scaffold: `Plugin`, `Post_Types`, `Meta`, `Bookings`, `Shortcodes`,
  `Settings`, and `Assets` classes.
- `bike` and `booking` custom post types, plus the `bike_type` taxonomy.
- Bike meta box (fleet code, frame size, hourly/daily rate, status).
- Availability checks with double-booking protection.
- `[bike_listing]` and `[bike_booking_form]` shortcodes.
- Admin settings page (currency, business name, booking page, terms).
- Front-end stylesheet and booking-form JavaScript.
- Cline `.clinerules` and `memory-bank/` project scaffolding.

### Changed
- Re-skinned the front-end stylesheet to match the Happy Bike (happybike.no)
  design language: deep green-charcoal (`#23332d`), crimson accent (`#cf0050`),
  warm peach/cream neutrals, uppercase tight-tracked headings and pill-shaped
  CTAs. Brand values are exposed as `--bike-rental-*` CSS custom properties on
  `:root`.
- Localized every user-facing string to **Norwegian Bokmål** to match the
  Norwegian (happybike.no) market: front-end listing/empty state, booking form
  labels and button, success/error notices, bike + booking status labels,
  availability validation messages, and the admin meta box / post-type /
  settings labels. Strings remain wrapped in `__()` / `_x()` with the
  `bike-rental` text domain. Added `Plugin::load_textdomain()` (hooked on `init`)
  so `/languages` translation files are loaded.

[Unreleased]: https://example.com/bike-rental
