# APSP Cesare Benedetti — WordPress blueprint

Custom theme tweaks and APSP plugins for **APSP Cesare Benedetti** (reference / blueprint for `apsp-avio`, `apsp-giacomo-cis`, etc.).

| Item | Value |
|------|--------|
| Local URL | http://localhost/apsp-cesare-benedetti |
| Database | `apsp_cesare_benedetti` |
| WP admin (after import) | `UtenteA` / `apsp-cesare-local-dev` |
| WP admin (pre-import only) | `admin` / `apsp-cesare-benedetti-dev` (replaced by restore) |
| Staging (historical) | https://apspcesarebenedetti.chebellagiornata.it |

## What’s in the Git repo

This repository tracks **custom code only** (not a full WordPress tree):

- `plugins/` — header, footer, grids, menus, etc.
- `functions.php` — legacy Astra `functions.php` snapshot (do **not** replace the full Astra file; use `wp-content/mu-plugins/cesare-benedetti-bootstrap.php` for Bootstrap enqueue)
- `header-html/` — static header reference
- `TODO` — migration checklist

WordPress core, `wp-config.php`, uploads, and page content live outside git (or in `.gitignore` after local bootstrap).

## Local setup (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP.
2. From this directory:

```bash
./scripts/setup-wordpress.sh
```

3. Open http://localhost/apsp-cesare-benedetti

### Import production backup (CLI — large `.wpress` files)

Plugins: **All-in-One WP Migration** + **Unlimited Extension** (copied from Avio; active).

1. Place the `.wpress` file in `wp-content/ai1wm-backups/` (or symlink from `~/Downloads`).
2. Import without the 64MB browser upload limit:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/apsp-cesare-benedetti
/Applications/XAMPP/xamppfiles/bin/php -d memory_limit=2G scripts/wp-cli.phar ai1wm restore \
  YOUR-BACKUP-FILE.wpress --yes --path=/Applications/XAMPP/xamppfiles/htdocs/apsp-cesare-benedetti
```

3. Log in at http://localhost/apsp-cesare-benedetti/wp-admin/ (production admin user; reset local password if needed):

```bash
/Applications/XAMPP/xamppfiles/bin/php scripts/wp-cli.phar user update UtenteA \
  --user_pass='apsp-cesare-local-dev' --path=/Applications/XAMPP/xamppfiles/htdocs/apsp-cesare-benedetti
```

Imported **2026-05-22**: `www-apsp-cesarebenedetti-it-20260522-172408-wnsg23epgm4e.wpress` (~2.8GB). URLs already point to `http://localhost/apsp-cesare-benedetti`. You can delete the `.wpress` from Downloads after verifying the site.

## Sync custom code after editing repo files

If you edit files under `_repo_stash/` (copies of the original repo layout):

```bash
./scripts/sync-blueprint-to-wp.sh
```

## Database

```sql
CREATE DATABASE IF NOT EXISTS `apsp_cesare_benedetti` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`wp-config.php` uses the XAMPP MySQL socket (same pattern as `apsp-avio` and `apsp-giacomo-cis`).
