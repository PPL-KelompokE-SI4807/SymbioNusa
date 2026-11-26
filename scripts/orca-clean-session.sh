#!/bin/bash
# Orca Session Cleanup Script
# Run this at start of each work session to prevent phantom agent spawning.
#
# IMPORTANT: This script does NOT kill the orcad daemon/host.
# It only cleans session state so phantom agents don't resume after restart.
#
# Usage: ./scripts/orca-clean-session.sh

set -euo pipefail

ORCA_CONFIG_DIR="${ORCA_USER_DATA:-$HOME/.config/orca}"
cd /home/agent-dev/projects/SymbioNusa

echo "🧹 Cleaning Orca session state (daemon stays running)..."

# 1. Remove session databases — this is the root cause of phantom agents
#    Deleting these prevents resumed agents from spawning after daemon restart
echo "  Removing session databases..."
rm -f "$ORCA_CONFIG_DIR/agent-session-journal.db"*
rm -f "$ORCA_CONFIG_DIR/orchestration.db"*
rm -f "$ORCA_CONFIG_DIR/orchestration.db-shm"
rm -f "$ORCA_CONFIG_DIR/orchestration.db-wal"

# 2. Reset orchestration state (clears runs/tasks/workers without touching daemon)
echo "  Resetting orchestration state..."
if /home/agent-dev/.config/orca/cli/bin/orca status --json >/dev/null 2>&1; then
    /home/agent-dev/.config/orca/cli/bin/orca orchestration reset --all --json >/dev/null 2>&1 || true
else
    echo "  ℹ️  Orca runtime not reachable — skipping orchestration reset"
fi

# 3. Close any agent terminals (keep user shell terminals)
echo "  Closing agent terminals..."
TERMINALS=$(/home/agent-dev/.config/orca/cli/bin/orca terminal list --json 2>/dev/null || echo '{}')
echo "$TERMINALS" | python3 -c "
import json, sys
d = json.load(sys.stdin)
for t in d.get('result', {}).get('terminals', []):
    if t.get('agentIdentity'):
        print(t.get('handle'))
" 2>/dev/null | while read -r handle; do
    if [ -n \"$handle\" ]; then
        /home/agent-dev/.config/orca/cli/bin/orca terminal close --terminal "$handle" --json >/dev/null 2>&1 || true
    fi
done

echo "✅ Orca session cleaned. Daemon still running."
echo "   Run './scripts/orca-verify-clean.sh' to confirm clean state."
