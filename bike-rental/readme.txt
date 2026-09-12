=== Bike Rental ===
Contributors: bikerental
Tags: rental, booking, bikes, bicycles, reservation
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a complete bicycle rental system to WordPress: manage your fleet, track availability, and take bookings.

== Description ==

Bike Rental turns your WordPress site into a bicycle rental business. Manage your
fleet, show availability, and accept booking requests directly on your site.

**Features**

* A **Bikes** post type with type, frame size, hourly/daily rates, fleet code, and status.
* A **Bike Types** taxonomy for road, mountain, electric, city, and more.
* A **Bookings** post type to review and manage rental requests.
* Automatic double-booking protection for overlapping date ranges.
* The `[bike_listing]` and `[bike_booking_form]` shortcodes.
* Settings for currency symbol, business name, booking page, and rental terms.

== Installation ==

1. Upload the `bike-rental` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Add bikes under **Bikes → Add New**.
4. Place `[bike_listing]` and `[bike_booking_form]` on your pages.
5. Configure defaults under **Bikes → Settings**.

== Frequently Asked Questions ==

= Does it handle payments? =

Not in v1. Bookings are requests that the site owner confirms. Payment integration
is planned for a future release.

= Can I prevent double bookings? =

Yes. Overlapping requests for the same bike are automatically rejected.

== Changelog ==

= 0.1.0 =
* Initial release: bikes, bike types, bookings, availability, shortcodes, settings.
