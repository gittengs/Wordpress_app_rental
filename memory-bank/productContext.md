# Product Context — Bike Rental

## Why this project exists

Bicycle shops, rental kiosks, hotels, and tour operators often need a way to rent
out bikes on their existing WordPress website. Rather than sending customers to a
third-party booking site, this plugin lets them browse the fleet and reserve a bike
directly on the site that already hosts their brand and content.

## Problems it solves

- **No dedicated rental tooling in WordPress.** WooCommerce can sell products, but
  rentals need date ranges, availability, and double-booking protection.
- **Manual booking via phone/email is error-prone.** Availability lives in someone's
  head or a paper calendar.
- **Scattered inventory info.** Bike specs, sizes, and prices are inconsistent
  across pages.

## How it should work (user experience)

### Visitor / customer
1. Opens a page where the owner placed `[bike_listing]`.
2. Sees the available bikes as cards (image, name, type, size, price).
3. Clicks "Book" on a bike and fills in `[bike_booking_form]` with name, email,
   start date, and end date.
4. Submits; gets a clear success or error message (e.g. "unavailable for those
   dates").

### Owner / administrator
1. Adds each bike under **Bikes** in the WordPress admin, setting type, size,
   hourly/daily rate, and status.
2. Reviews incoming requests under **Bookings**, approving or rejecting them.
3. Configures currency and business defaults under **Settings → Bike Rental**.

## UX goals

- Zero learning curve: use native WordPress admin screens and terminology.
- Front-end output inherits the active theme's styling by default, with a small
  stylesheet for the rental-specific components.
- Clear, accessible forms and feedback messages.
