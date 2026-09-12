# Project Brief — Bike Rental

## Overview

**Bike Rental** is a WordPress plugin that adds a complete bicycle-rental system to
a WordPress site. It is distributed as an add-on ("plugin") that the site owner
installs into their own WordPress installation.

## Core Requirements

1. **Inventory management** — the owner can add, edit, and retire bicycles, each with
   its own details (type, size, rates, availability).
2. **Availability tracking** — the system knows which bikes are available, rented, or
   under maintenance, and prevents double-booking.
3. **Bookings** — customers can request/place a rental for a bike over a date range,
   and the owner can review and manage those bookings.
4. **Front-end display** — visitors can browse bikes and start a booking via
   shortcodes embedded in any page or post.
5. **Admin experience** — a simple settings screen plus native WordPress list tables
   for bikes and bookings.

## Goals

- Ship a clean, self-contained plugin that drops into `wp-content/plugins/`.
- Follow WordPress coding standards and security best practices throughout.
- Keep v1 dependency-free (no Composer, no build step) so it is portable.

## Non-Goals (v1)

- Online payment processing (planned for a later phase, likely via WooCommerce).
- Customer accounts / self-service dashboard.
- Multi-vendor or marketplace functionality.
- Gutenberg block editor UI (shortcodes first; blocks later).

## Success Criteria

- [ ] Plugin activates without errors on a stock WordPress 6.x site.
- [ ] Owner can create bikes with type/rates/status and see them listed in admin.
- [ ] `[bike_listing]` renders available bikes on the front end.
- [ ] `[bike_booking_form]` lets a visitor submit a booking request securely.
- [ ] Double-booking the same bike for overlapping dates is rejected.
- [ ] No unescaped output or unsanitized input anywhere.
