#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "Repository root: $ROOT"

if ! command -v php >/dev/null 2>&1; then
  echo "ERROR: php CLI not found. Install PHP (>=7.4) and re-run this script." >&2
  exit 2
fi

echo "Running PHP syntax check (php -l) on all .php files (excluding vendor and PHPExcel)..."
find . -type f -name '*.php' -not -path './vendor/*' -not -path './PHPExcel/*' -print0 | xargs -0 -n1 php -l

echo "PHP syntax check completed."

if command -v composer >/dev/null 2>&1; then
  echo "Running 'composer validate'..."
  composer validate --no-check-publish || true
else
  echo "composer not found; skipping 'composer validate'."
fi

echo "Quick HTTP smoke tests (starts built-in PHP server on 127.0.0.1:8000)..."

# Start server in background
php -S 127.0.0.1:8000 -t . >/tmp/app_server.log 2>&1 &
PID=$!
sleep 1

echo "Server started (PID=$PID). Checking important endpoints..."

check() {
  URL=$1
  echo -n "Checking $URL ... "
  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL") || HTTP_CODE="000"
  echo "$HTTP_CODE"
}

check http://127.0.0.1:8000/
check http://127.0.0.1:8000/login.php
check http://127.0.0.1:8000/ajax.php?action=allnotifs

echo "Stopping built-in server (PID=$PID)"
kill $PID || true
wait $PID 2>/dev/null || true

echo "Audit finished. Review output above and /tmp/app_server.log for server logs."
