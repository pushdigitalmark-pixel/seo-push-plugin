#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
VER="$(cat "$ROOT/.version")"
DIST="$ROOT/dist"
mkdir -p "$DIST"

PLUGIN_SRC="$ROOT/plugin"
ZIP="$DIST/vibe-coding-seo-$VER.zip"

# Ensure version string inside main plugin file matches .version
MAIN="$PLUGIN_SRC/vibe-coding-seo.php"
if ! grep -q "Version:\s\+$VER" "$MAIN"; then
  echo "Updating plugin header version to $VER"
  sed -i.bak -E "s/(Version:\s+)[0-9]+\.[0-9]+\.[0-9]+/\1$VER/" "$MAIN"
fi

cd "$PLUGIN_SRC/.."
zip -r "$ZIP" "plugin" -x "*/.*" -x "*/node_modules/*"
echo "Built: $ZIP"
