#!/usr/bin/env bash
# Hostinger: do NOT use public/storage symlinks — Apache returns 403.
# Uploaded files are served via routes/storage.php (see public/.htaccess).

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

rm -f storage/app/public/public
rm -rf public/storage

php artisan route:cache

echo "Removed public/storage symlink. Files are served through Laravel /storage/... route."
