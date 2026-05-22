#!/usr/bin/env bash
# Copy blueprint repo files (_repo_stash) into the running WordPress tree.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STASH="$ROOT/_repo_stash"

if [[ ! -d "$STASH/plugins" ]]; then
  echo "No _repo_stash/plugins found. Edit wp-content/plugins directly or restore _repo_stash."
  exit 1
fi

rsync -a --delete "$STASH/plugins/" "$ROOT/wp-content/plugins/"
echo "Synced plugins → wp-content/plugins/"
echo "Theme: use mu-plugins/cesare-benedetti-bootstrap.php (do not overwrite full Astra functions.php)."
