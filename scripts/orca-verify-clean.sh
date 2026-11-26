#!/bin/bash
# Orca State Verification Script
# Checks for phantom agents and verifies clean session state.
# Auto-runs on cd into SymbioNusa (add to shell rc).
#
# Usage: ./scripts/orca-verify-clean.sh

set -euo pipefail

ORCA_CLI="/home/agent-dev/.config/orca/cli/bin/orca"

echo "🔍 Verifying Orca clean state..."

# Check if Orca runtime is reachable
if ! $ORCA_CLI status --json >/dev/null 2>&1; then
    echo "ℹ️  Orca runtime not reachable (start with './scripts/orca-start-host.sh' if needed)"
    echo "✅ Orca state verified (runtime offline)"
    exit 0
fi

# Check terminals for agent sessions
TERMINALS=$($ORCA_CLI terminal list --json 2>/dev/null || echo '{}')
AGENT_COUNT=$(echo "$TERMINALS" | python3 -c "
import json, sys
d = json.load(sys.stdin)
agents = [t for t in d.get('result', {}).get('terminals', []) if t.get('agentIdentity')]
print(len(agents))
" 2>/dev/null || echo "0")

if [ "$AGENT_COUNT" -gt 0 ] 2>/dev/null; then
    echo "❌ Found $AGENT_COUNT agent terminal(s):"
    echo "$TERMINALS" | python3 -c "
import json, sys
d = json.load(sys.stdin)
for t in d.get('result', {}).get('terminals', []):
    if t.get('agentIdentity'):
        print(f'  {t.get(\"handle\")} - {t.get(\"agentIdentity\")} - {t.get(\"title\")}')
"
    echo "   Run './scripts/orca-clean-session.sh' to remove them"
    exit 1
else
    echo "✅ No agent terminals found"
fi

# Check orchestration runs
RUNS=$($ORCA_CLI orchestration run-list --json 2>/dev/null || echo '{}')
RUN_COUNT=$(echo "$RUNS" | python3 -c "
import json, sys
d = json.load(sys.stdin)
print(len(d.get('result', {}).get('runs', [])))
" 2>/dev/null || echo "0")

if [ "$RUN_COUNT" -gt 0 ] 2>/dev/null; then
    echo "⚠️  Found $RUN_COUNT orchestration run(s)"
else
    echo "✅ No orchestration runs"
fi

# Check git status
cd /home/agent-dev/projects/SymbioNusa
CHANGES=$(git status --porcelain 2>/dev/null | wc -l)
if [ "$CHANGES" -gt 0 ]; then
    echo "⚠️  Git has $CHANGES uncommitted change(s)"
else
    echo "✅ Git working tree clean"
fi

echo "✅ Orca state verified clean"
