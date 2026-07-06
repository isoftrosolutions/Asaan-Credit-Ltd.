#!/bin/bash
LOCKFILE=/tmp/backup.lock
if [ -f "$LOCKFILE" ] && kill -0 $(cat "$LOCKFILE") 2>/dev/null; then
    echo "Backup already running (PID $(cat $LOCKFILE))"
    exit 1
fi
echo $$ > "$LOCKFILE"
trap 'rm -f "$LOCKFILE"' EXIT
cd "$(dirname "$0")/.."
php scripts/backup.php >> storage/backups/cron.log 2>&1
