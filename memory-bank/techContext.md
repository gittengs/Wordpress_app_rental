# Tech Context — Bike Rental

## Technologies used

- **Language:** PHP 7.4+ (target), written against WordPress 6.0+ APIs.
- **Platform:** WordPress (plugin).
- **Front-end:** vanilla CSS + vanilla JavaScript (no framework, no build step).
- **Standards:** WordPress PHP Coding Standards (WPCS), WordPress i18n.

## Development setup

| Tool | Status | Notes |
|---|---|---|
| Node.js | ✅ v22.23.2 | Available; used only for optional tooling/dev servers. |
| npm | ✅ 10.9.8 | Available. |
| PHP-CLI | ❌ not installed | Cannot `apt-get install` — a long-running environment process holds the dpkg lock (see below). |
| Composer | ❌ not installed | Not required for v1 (no PHP dependencies). |
| WP-CLI | ❌ not installed | Useful for local WP install + plugin testing. |
| phpcs/WPCS | ❌ not installed | Needed to enforce WP coding standards automatically. |

### Local WordPress for testing (recommended path)

Because PHP is absent locally, testing options are:

1. **Install PHP + WP-CLI** (e.g. `apt-get install php-cli php-mysql`) and run a
   local WordPress with SQLite or MySQL.
2. **Docker**: run the official `wordpress` image and mount this repo's `bike-rental/`
   folder into `wp-content/plugins/bike-rental`.
3. **Remote/staging WP site**: zip `bike-rental/` and upload via the Plugins screen.

## Technical constraints

- Must remain PHP 7.4 compatible — no PHP 8-only syntax.
- No Composer autoloading; the plugin ships its own autoloader.
- No build step — CSS/JS are hand-written and enqueued as-is.
- Must not assume any particular theme, page builder, or third-party plugin.

## Dependencies

- **Runtime:** WordPress core only.
- **Dev (optional, not yet installed):** PHPUnit + `wp-phpunit/wp-phpunit`,
  `squizlabs/php_codesniffer`, `wp-coding-standards/wpcs`.

## Tool usage patterns (for Cline)

- Read `memory-bank/` files at the start of every task.
- Use `search_codebase` before editing to find existing patterns.
- Keep each edit small and focused; update `activeContext.md` + `progress.md` after
  meaningful milestones.

### PHP validation without PHP-CLI

`apt-get` is blocked in this workspace by a pre-existing environment process that
holds the dpkg lock, so PHP cannot be installed. Until that changes, validate PHP
using the Node-based parser instead:

```bash
# one-time setup
mkdir -p /tmp/phpcheck && cd /tmp/phpcheck && npm init -y && npm install php-parser@3

# syntax check every plugin file
node /tmp/phpcheck/check.js /path/to/bike-rental

# structural check: autoloader mapping + class reference resolution
node /tmp/phpcheck/verify.js /path/to/bike-rental
```

JavaScript can be checked with `node --check`.
