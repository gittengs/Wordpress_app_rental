# Development Guide — Bike Rental

## Repository layout

```
Wordpress_app_rental/
├── .clinerules/            # Cline rules (dev-only, not shipped)
│   ├── memory-bank.md
│   └── wordpress-conventions.md
├── memory-bank/            # Cline persistent memory (dev-only, not shipped)
├── bike-rental/            # ← the shippable WordPress plugin
├── README.md
├── DEVELOPMENT.md
└── .gitignore
```

Nothing outside `bike-rental/` is part of the distributed plugin.

## Local environment

| Requirement | Purpose | Status in this workspace |
|---|---|---|
| PHP 7.4+ (`php-cli`) | Run `php -l`, WP-CLI, PHPUnit | ❌ not installed |
| Node.js 22 | Optional JS tooling | ✅ installed |
| MySQL or SQLite | Local WordPress database | ❌ |
| WP-CLI | Install/configure a local WordPress | ❌ |

### Option A — Docker (recommended if PHP is unavailable)

```bash
docker run --name bike-rental-wp -p 8080:80 \
  -v "$PWD/bike-rental:/var/www/html/wp-content/plugins/bike-rental" \
  -e WORDPRESS_DB_HOST=db \
  wordpress:latest
```

Then visit http://localhost:8080, complete the WordPress install, and activate
**Bike Rental**.

### Option B — Install PHP + WP-CLI

```bash
sudo apt-get update && sudo apt-get install -y php-cli php-mysql
# Install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp
```

## Linting & static analysis

```bash
# PHP syntax check on every file
find bike-rental -name '*.php' -print0 | xargs -0 -n1 php -l

# WordPress Coding Standards (optional)
composer global require wp-coding-standards/wpcs
phpcs --standard=WordPress bike-rental
```

```bash
# JavaScript syntax check
node --check bike-rental/assets/js/bike-rental.js
```

## Testing checklist (manual, until PHPUnit is wired up)

1. Activate the plugin → no PHP notices; **Bikes** and **Bookings** menus appear.
2. Create a bike with rates + status → confirm values persist after reload.
3. Add `[bike_listing]` to a page → bikes render with price/status.
4. Add `[bike_booking_form]` to a page → submit a valid booking → success message.
5. Submit an overlapping booking for the same bike → rejected with an error.
6. Set a bike to **Maintenance** → it can no longer be booked.

## Releasing

```bash
# Create a distributable zip of just the plugin
cd bike-rental && zip -r ../bike-rental.zip . -x '*.DS_Store'
```

## Conventions

All code must follow `.clinerules/wordpress-conventions.md` (WordPress coding
standards, security requirements, i18n). Read `memory-bank/` before starting work.
