#!/usr/bin/env bash
# Bump the PowerBachat theme version everywhere WordPress reads it, then build
# powerbachat-<version>.zip next to this script.
#   ./bump-version.sh 1.0.2 "What changed in this release"
set -euo pipefail
cd "$(dirname "$0")"
NEW="${1:?usage: bump-version.sh <version> \"changelog line\"}"
NOTE="${2:-Maintenance update.}"
THEME=powerbachat

sed -i.bak -E "s/^Version: .*/Version: ${NEW}/" "$THEME/style.css"
sed -i.bak -E "s/define\( 'POWERBACHAT_VERSION', '[^']+' \);/define( 'POWERBACHAT_VERSION', '${NEW}' );/" "$THEME/functions.php"
sed -i.bak -E "s/^Stable tag: .*/Stable tag: ${NEW}/" "$THEME/readme.txt"
python3 - "$THEME/readme.txt" "$NEW" "$NOTE" <<'PY'
import sys
path, ver, note = sys.argv[1:]
s = open(path).read()
s = s.replace("== Changelog ==\n", f"== Changelog ==\n\n= {ver} =\n* {note}\n", 1)
open(path, "w").write(s)
PY
rm -f "$THEME"/*.bak

zip -qr "powerbachat-${NEW}.zip" "$THEME" -x "*.DS_Store"
echo "PowerBachat ${NEW} -> powerbachat-${NEW}.zip"
