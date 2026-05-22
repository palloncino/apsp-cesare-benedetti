# APSP Cesare Benedetti — WordPress blueprint

Custom theme tweaks and APSP plugins for **APSP Cesare Benedetti** (reference / blueprint for `apsp-avio`, `apsp-giacomo-cis`, etc.).

| Item | Value |
|------|--------|
| Local URL | http://localhost/apsp-cesare-benedetti |
| Database | `apsp_cesare_benedetti` |
| WP admin (dev) | `admin` / `apsp-cesare-benedetti-dev` |
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

### See the real site (pages, menus, media)

The clone does **not** include the production database. To mirror staging/production:

1. On the source WordPress: **All-in-One WP Migration → Export** (file `.wpress`).
2. Locally: activate **All-in-One WP Migration**, then **Import** that file.
3. After import, run (if URLs still point to staging):

```bash
/Applications/XAMPP/xamppfiles/bin/php scripts/wp-cli.phar search-replace \
  'https://apspcesarebenedetti.chebellagiornata.it' 'http://localhost/apsp-cesare-benedetti' --all-tables
```

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
