#!/bin/bash
# Orca Host Startup Script - for when orca-ide isn't auto-started
# This starts the Orca host in the background, keeping it alive
# Usage: ./orca-start-host.sh

set -euo pipefail

ORCA_IDE="/opt/orca/squashfs-root/orca-ide"
LOG="/tmp/orca-host.log"
PID_FILE="/tmp/orca-host.pid"

echo "🚀 Starting Orca host..."

# Check if already running
if /home/agent-dev/.config/orca/cli/bin/orca status --json 2>/dev/null | python3 -c "import json,sys; d=json.load(sys.stdin); exit(0 if d.get('result',{}).get('runtime',{}).get('reachable') else 1)" 2>/dev/null; then
    echo "✅ Orca host already running and reachable"
    exit 0
fi

# Start orca-ide serve in background
nohup "$ORCA_IDE" serve --supervise --force --port 6768 --pairing-address 100.125.205.24 > "$LOG" 2>&1 &
PID=$!
echo "$PID" > "$PID_FILE"
echo "   PID: $PID"
echo "   Log: $LOG"

# Wait for it to be ready
echo "   Waiting for startup..."
for i in $(seq 1 15); do
    sleep 1
    if /home/agent-dev/.config/orca/cli/bin/orca status --json 2>/dev/null | python3 -c "import json,sys; d=json.load(sys.stdin); exit(0 if d.get('result',{}).get('runtime',{}).get('reachable') else 1)" 2>/dev/null; then
        echo "✅ Orca host ready"
        exit 0
    fi
    echo "   ... waiting ($i/15)"
done

echo "❌ Orca host failed to start. Check: $LOG"
tail -30 "$LOG"
exit 1
