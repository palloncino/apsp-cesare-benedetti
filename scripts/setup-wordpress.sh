#!/usr/bin/env bash
# One-time WordPress setup for apsp-cesare-benedetti (XAMPP).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP="/Applications/XAMPP/xamppfiles/bin/php"
MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"
WP_CLI="$ROOT/scripts/wp-cli.phar"
SITE_URL="http://localhost/apsp-cesare-benedetti"

cd "$ROOT"

echo "Creating database apsp_cesare_benedetti..."
"$MYSQL" -u root -e "CREATE DATABASE IF NOT EXISTS \`apsp_cesare_benedetti\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [[ ! -f "$WP_CLI" ]]; then
  curl -fsSL -o "$WP_CLI" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
fi

if ! "$PHP" "$WP_CLI" core is-installed 2>/dev/null; then
  echo "Installing WordPress..."
  "$PHP" "$WP_CLI" core install \
    --url="$SITE_URL" \
    --title="APSP Cesare Benedetti" \
    --admin_user="admin" \
    --admin_password="apsp-cesare-benedetti-dev" \
    --admin_email="dev@localhost.local" \
    --skip-email
fi

"$PHP" "$WP_CLI" theme activate astra
"$PHP" "$WP_CLI" plugin activate \
  header footer homepage-links-grid nice-grid custom-menus-plugin \
  custom-services-paper private-folder-generator \
  apsp-css-editor apsp-google-map custom-document-manager \
  2>/dev/null || true

"$PHP" "$WP_CLI" rewrite structure '/%postname%/' --hard
"$PHP" "$WP_CLI" rewrite flush --hard

echo ""
echo "Site URL: $SITE_URL"
echo "Admin:    $SITE_URL/wp-admin/"
echo "Login:    admin / apsp-cesare-benedetti-dev"
echo ""
echo "To mirror production content, import an All-in-One WP Migration backup"
echo "from the staging site (Tools → Export on source, Import here)."
