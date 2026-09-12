# Progress — Bike Rental

## Current status

**Scaffolding complete.** The plugin skeleton, Cline rules, and Memory Bank are in
place. Feature logic is stubbed out and ready to be built upon.

## What works

- [x] Repo initialized with `.gitignore` and git history.
- [x] `.clinerules/memory-bank.md` + `.clinerules/wordpress-conventions.md`.
- [x] `memory-bank/` with all six core documents.
- [x] Plugin main file with header, constants, autoloader, and lifecycle hooks.
- [x] Feature classes scaffolded: `Plugin`, `Post_Types`, `Meta`, `Bookings`,
      `Shortcodes`, `Settings`, `Assets`.
- [x] Front-end assets (`bike-rental.css`, `bike-rental.js`) and `uninstall.php`.
- [x] `readme.txt` (WordPress.org format), `CHANGELOG.md`, `languages/` placeholder.
- [x] `README.md` + `DEVELOPMENT.md` with setup, lint, test, and release steps.
- [x] JS syntax validated with `node --check`.

## What's left to build

- [ ] Verify plugin activates in a real WordPress install (no PHP locally yet).
- [ ] Bike meta admin UI polish (list-table columns, rate formatting).
- [ ] Booking form end-to-end: submit → validate → persist → feedback.
- [ ] Admin booking management (approve/reject, status transitions).
- [ ] Availability calendar / date picker on the front end.
- [ ] Settings page fields (currency, business name, terms text) wired up.
- [ ] Internationalization: generate `.pot` file in `/languages`.
- [ ] Optional: WooCommerce payments integration.
- [ ] Optional: Gutenberg blocks for listing/booking.

## Known issues

- **No local PHP toolchain** — `php-cli` is not installed and cannot be installed
  because a long-running environment `apt-get` process holds the dpkg lock
  (`/var/lib/dpkg/lock-frontend`). Mitigations: use Docker (see `techContext.md`),
  or validate with the Node-based `php-parser` scripts documented in `techContext.md`.
- **Validation performed:** all 9 PHP files parse cleanly, every class maps to its
  autoloader filename, and all class references resolve. `node --check` passes on the
  JS. A real WordPress runtime test is still pending.
- Availability logic uses meta queries; acceptable at small scale but should be
  revisited (custom table / indexed columns) if the fleet grows large.

## Evolution of project decisions

- **2026-09-12** — Project started. Chose shortcodes over blocks for v1; chose CPTs
  over custom tables for bikes/bookings; deferred payments and Composer tooling.
