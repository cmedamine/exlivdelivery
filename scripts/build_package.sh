#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DIST="$ROOT/dist"
PKGDIR="$DIST/package"
ZIPNAME="colislivrer-hostinger.zip"

rm -rf "$DIST"
mkdir -p "$PKGDIR"

echo "Copying files to package directory..."
rsync -a --progress \
  --exclude 'dist' \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'scripts' \
  --exclude '*.zip' \
  --exclude '*.log' \
  --exclude '.env' \
  --exclude 'install.php' \
  --exclude 'README_INSTALLATION.md' \
  ./ "$PKGDIR/"

# Ensure uploads/index.html and other safe files exist
if [ ! -d "$PKGDIR/uploads" ]; then
  mkdir -p "$PKGDIR/uploads"
  echo "" > "$PKGDIR/uploads/index.html"
fi

cd "$DIST"

if command -v zip >/dev/null 2>&1; then
  echo "Creating ZIP $ZIPNAME..."
  zip -r "$ZIPNAME" package > /dev/null
  echo "Package created: $DIST/$ZIPNAME"
  ls -lh "$DIST/$ZIPNAME"
else
  TGZNAME="colislivrer-hostinger.tar.gz"
  echo "zip not available — creating tar.gz $TGZNAME instead..."
  tar -czf "$TGZNAME" package
  echo "Package created: $DIST/$TGZNAME"
  ls -lh "$DIST/$TGZNAME"
fi
