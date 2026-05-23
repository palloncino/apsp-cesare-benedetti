#!/usr/bin/env bash
# Slim local Cesare Benedetti copy: drop bulk media/docs, disable heavy plugins.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP="${ROOT}/scripts/wp-cli.phar"
PHP="/Applications/XAMPP/xamppfiles/bin/php"
UP="${ROOT}/wp-content/uploads"

echo "==> Removing bulk upload folders (keeps wp-content/uploads/2024/09 for header assets)..."
rm -rf \
	"${UP}/2024/08" \
	"${UP}/2024/10" \
	"${UP}/2024/11" \
	"${UP}/2024/12" \
	"${UP}/AllegatiAttiAlboPretorio" \
	"${UP}/wp-statistics" \
	"${UP}/user-documents" \
	"${UP}/files"

echo "==> Removing AI1WM backup symlink (full .wpress stays in Downloads)..."
rm -f "${ROOT}/wp-content/ai1wm-backups/"*.wpress 2>/dev/null || true

echo "==> Truncating debug log..."
: > "${ROOT}/wp-content/debug.log" 2>/dev/null || true

echo "==> Deactivating plugins not needed for local blueprint..."
"${PHP}" "${WP}" plugin deactivate \
	all-in-one-wp-migration \
	all-in-one-wp-migration-unlimited-extension \
	broken-link-checker \
	wp-statistics \
	stream \
	media-sync \
	post-views-counter \
	real-time-auto-find-and-replace \
	albo-pretorio-on-line-master-MOD \
	"Statistiche Accessi" \
	--path="${ROOT}" 2>/dev/null || true

echo "==> Done. Uploads size:"
du -sh "${UP}"
du -sh "${ROOT}"
