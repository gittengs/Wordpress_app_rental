# System Patterns — Bike Rental

## System architecture

The plugin is a standard WordPress plugin using an OOP, hook-driven architecture.
Nothing executes at file-load time except the autoloader and boot call; all behavior
is attached to WordPress hooks.

```
bike-rental.php            → constants + autoloader + register hooks + boot
includes/
  class-plugin.php         → singleton bootstrap; wires feature classes; activation
  class-post-types.php     → registers 'bike' and 'booking' CPTs + taxonomies
  class-meta.php           → bike meta fields, meta boxes, save/validation
  class-bookings.php       → booking CRUD + availability / double-booking checks
  class-shortcodes.php     → [bike_listing], [bike_booking_form]
  class-settings.php       → admin settings page (Settings API)
  class-assets.php         → conditional CSS/JS enqueue
assets/                    → css + js shipped with the plugin
uninstall.php              → data cleanup on delete
```

## Key technical decisions

| Decision | Choice | Rationale |
|---|---|---|
| Storage for bikes | Custom post type `bike` | Native admin UI, revisions, REST support |
| Storage for bookings | Custom post type `booking` | Simple v1; avoids schema migrations |
| Bike details | Post meta (`_bike_*`) | Attached to the CPT; hidden from custom-fields UI |
| Availability | Date-range overlap query on `_booking_*` meta | No extra table needed in v1 |
| Front-end | Shortcodes | Works with any theme and page builder |
| Bootstrap | Singleton `Plugin::instance()` on `plugins_loaded` | Predictable init order |
| Autoloading | Custom PSR-4-style `spl_autoload_register` | No Composer dependency |

## Design patterns in use

- **Singleton** — `Plugin` provides one bootstrap instance.
- **Hook registration object** — each class owns a `register_hooks()` method.
- **Separation of concerns** — data (Post_Types/Meta), logic (Bookings),
  presentation (Shortcodes/Assets), config (Settings).

## Component relationships

```
bike-rental.php
   └── BikeRental\Plugin::instance()->init()
          ├── Post_Types  → registers CPTs on init
          ├── Meta        → registers meta + meta boxes
          ├── Bookings    → booking logic, availability checks
          ├── Shortcodes  → renders listings/forms (uses Bookings + Meta)
          ├── Settings    → admin-only settings page
          └── Assets      → enqueues css/js
```

## Critical implementation paths

1. **Activation** — `register_activation_hook` → `Plugin::activate()` registers CPTs
   then `flush_rewrite_rules()` so `/bikes/` permalinks work immediately.
2. **Booking submission** — shortcode form → nonce + sanitize → `Bookings::create()`
   → availability check → persist as `booking` CPT → redirect with status message.
3. **Availability** — for a requested range, query `booking` posts for the same bike
   whose `_booking_start`/`_booking_end` overlap and whose status is active.
