# Bike Rental

A WordPress plugin that adds a complete bicycle-rental system to any WordPress site:
manage your fleet, track availability, and take bookings — no third-party service
required.

> Built as an installable WordPress **add-on (plugin)** living in the `bike-rental/`
> directory of this repository.

## Features (v1)

- **Fleet management** — a `Bikes` post type with type, frame size, hourly/daily
  rates, fleet code, and availability status.
- **Bike types** — a `Bike Types` taxonomy (road, mountain, electric, city, …).
- **Bookings** — customers request a rental for a date range; requests are stored as
  a `Bookings` post type and reviewed in the admin.
- **Double-booking protection** — overlapping requests for the same bike are rejected.
- **Shortcodes** — drop `[bike_listing]` and `[bike_booking_form]` into any page.
- **Admin settings** — currency symbol, business name, booking page, and rental terms.

## Installation

1. Copy the `bike-rental/` folder into your WordPress site's
   `wp-content/plugins/` directory (or upload a zip of it via
   **Plugins → Add New → Upload Plugin**).
2. Activate **Bike Rental** from the Plugins screen.
3. Add a few bikes under **Bikes → Add New**.
4. Create a page containing:
   - `[bike_listing]` to show available bikes, and/or
   - `[bike_booking_form]` to accept bookings.
5. Set that page as the **Booking page** under **Bikes → Settings**.

## Shortcodes

### `[bike_listing]`

| Attribute | Default | Description |
|---|---|---|
| `type`    | `''`    | Filter by bike type slug (e.g. `mountain`). |
| `limit`   | `12`    | Maximum number of bikes to show. |
| `columns` | `3`     | Grid columns (1–4). |

### `[bike_booking_form]`

| Attribute | Default | Description |
|---|---|---|
| `bike_id` | `0`     | Bike to book. Falls back to the `?bike_id=` query var. |

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Development

See [DEVELOPMENT.md](DEVELOPMENT.md) for the local environment, tooling, and testing
instructions. Project context for Cline live in `.clinerules/` and `memory-bank/`.

## License

GPL-2.0-or-later — see the plugin header in `bike-rental/bike-rental.php`.
