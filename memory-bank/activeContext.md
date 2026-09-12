# Active Context — Bike Rental

## Current focus

**Phase: Project scaffolding.** Just initialized the Memory Bank, `.clinerules`,
and the initial plugin skeleton. Next up is fleshing out the feature classes and
verifying the plugin activates cleanly.

## Recent changes

- Created `.clinerules/memory-bank.md` (persistent-memory instructions).
- Created `.clinerules/wordpress-conventions.md` (always-apply project rules).
- Initialized the `memory-bank/` documents.
- Scaffolded the `bike-rental/` plugin: main file, autoloader, and feature classes
  (`Plugin`, `Post_Types`, `Meta`, `Bookings`, `Shortcodes`, `Settings`, `Assets`).
- Added `assets/css/bike-rental.css`, `assets/js/bike-rental.js`, and
  `uninstall.php`.
- Re-skinned `assets/css/bike-rental.css` to the **happybike.no** (Happy Bike)
  design language: deep green-charcoal `#23332d`, crimson accent `#cf0050`,
  warm peach `#ffe4d9` / cream `#fff6f2` neutrals, `#444444` body gray, `#bfbfbf`
  quiet gray. Tokens exposed as `--bike-rental-*` custom properties on `:root`
  (so standalone notices/empty states resolve them). Pill-shaped uppercase CTAs,
  uppercase tight-tracked labels/titles, 4/8px radii. Bumped version to `0.1.1`.

## Next steps

1. Install/enable PHP-CLI locally (or provide a Docker/WP-CLI workflow) so the plugin
   can be syntax-checked and run against a local WordPress.
2. Install the plugin in a local WordPress and confirm activation creates the CPTs
   and flushes rewrite rules.
3. Build out the booking form handler + availability validation and test it.
4. Add admin list-table columns for bike rates/status.
5. Write a first round of automated tests (PHPUnit + WP test suite) if a WP install
   is available.

## Active decisions and considerations

- **Repo layout:** dev scaffolding (`.clinerules/`, `memory-bank/`) lives at the repo
  root; the shippable plugin lives in `bike-rental/`.
- **No Composer/build tooling in v1** — keeps the plugin portable.
- **Shortcodes first**, Gutenberg blocks later.
- Bookings stored as a `booking` custom post type (not a custom table) in v1 for
  simplicity and native admin UI.

## Important patterns and preferences

- Every feature class registers its own hooks via `register_hooks()`.
- Nonces + capability checks + sanitize/escape on every data path.
- All strings internationalized with the `bike-rental` text domain.
- **Visual design language (from happybike.no):** Bricks Builder site; brand
  colors are deep green-charcoal `#23332d`, crimson `#cf0050`, peach `#ffe4d9`,
  cream `#fff6f2`, text `#0e1513`/`#444444`, quiet `#bfbfbf`, amber `#ffa011`;
  typeface is **Figtree** (weights 500/800, tight tracking, uppercase headings);
  pill-shaped buttons and 4/8px radii. The plugin's front-end CSS mirrors these
  via `--bike-rental-*` custom properties and inherits the theme's base font.

## Learnings and insights

- Local environment has **Node 22** but **no PHP**, and `apt-get` is blocked by a
  stale environment process holding the dpkg lock. PHP was therefore validated with
  the Node `php-parser` package instead of `php -l`.
- The IDE's shell completion detection is unreliable in this container; redirecting
  command output to a file and reading it back with `read_files` is more dependable.
