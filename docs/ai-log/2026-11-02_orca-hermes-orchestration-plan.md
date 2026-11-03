# Orca + Hermes Orchestration Operating Plan

> **Session:** 2026-11-02 | **Phase:** 1–8 complete | **Status:** System ready, no real PBI work started
> **Lead Orchestrator:** Hermes (Mayhesa's PBIs only: 01, 02, 03, FND-*)

---

## Executive Summary

Built a **self-improving Hermes Lead Orchestrator** for SymbioNusa that can plan, delegate, PM, review, orchestrate, and manage end-to-end using **Orca CLI**, **Atlassian MCP (Jira)**, and **FigCraft MCP**.

### Architecture

| Layer | Tool | Role |
|-------|------|------|
| **Control Plane** | Orca CLI v1.4.245 | Worktree, Run/Task/Dispatch/Worker lifecycle |
| **PM/Orchestrator** | Hermes v0.22.5 | Planning, delegation, Jira sync, code review, UI design |
| **Coding Engine** | OpenCode (via Orca) | Implementation, debugging, tests |
| **Workflow Engine** | Goose (planned) | Build/test/validation automation |
| **Model Gateway** | 9Router (port 20128) | Shared LLM access |
| **Project Record** | Jira (SYM project, Atlassian MCP) | Issue tracking, sprint management |
| **UI Design** | FigCraft MCP (collab server) | Screens, components, handoff |

---

## Phase Completion Log

### Phase 1: Capability Map & Communication Discovery ✅
- Orca v1.4.245, Hermes v0.22.5 verified
- Terminal census completed
- Plugin `orca-hermes-lifecycle` status checked

### Phase 2: Baseline Assessment (Oneshot) ✅
- Verified 9Router gateway at `http://127.0.0.1:20128/v1`
- OpenCode registered in Orca agent catalog
- Atlassian MCP: cloudId `ecfbf62a-1cc5-4a58-bbdf-bfa872d8139a` (SYM)
- FigCraft collab: `wss://129.150.59.234:8443/figcraft/`, file `0a415cb1d39fc627`

### Phase 3: Lead Orchestrator Operating Plan ✅
- Saved as `docs/ai-log/2026-11-02_orca-hermes-orchestration-plan.md` (this doc)

### Phase 4: Skill Improvement Pipeline ✅
- **`symbionusa-lead-orchestrator`** — Main PM skill (ownership guard: PBI 01-03, FND-*)
- **`orca-worktree-management`** — Worktree create/list/show/remove
- **`symbionusa-code-review`** — Zero-flaw 6-gate review (DDL, StateMachine, lockForUpdate, Policy, Pest, Pint/Larastan)
- **`symbionusa-jira-sync`** — Bidirectional Jira-Orca sync
- All 4 skills: lint pass, test pass, docs generated

### Phase 5-6: ORCA Integration Testing ✅
| Operation | Verified |
|-----------|----------|
| `orca worktree create` | ✅ branch `Agent/pbi-XX`, status `in-progress` |
| `orca agent launch --agent opencode` | ✅ terminal with `agentIdentity: "opencode"` |
| `orca agent launch --agent hermes` | ✅ terminal with `agentIdentity: "hermes"` |
| `orca orchestration run-create` | ✅ Run ID returned |
| `orca orchestration worker-start` (opencode, hermes) | ✅ Workers dispatched |
| `orca orchestration worker-list` | ✅ Active workers visible |
| `orca orchestration worker-stop` | ✅ Terminals closed |
| `orca worktree rm --force` | ✅ Clean removal |

### Phase 7: MCP Integration Verification ✅
- **Atlassian MCP**: `searchJiraIssuesUsingJql` → SYM-8 (PBI-04) query successful
- **FigCraft MCP**: `inspect_ui()` → canvas state returned

### Phase 8: Autonomous Improvement Loop ✅
- Test framework: `~/.hermes/skills/tests/test_skills.py` (frontmatter, structure, commands)
- All 4 skills pass custom test suite
- Doc generator: `~/.hermes/skills/tests/generate_docs.py` → `docs/skills/index.md`, `sidebar.ts`
- Cleanup: PBI-04, PBI-05-test, PBI-06-regression worktrees deleted

---

## Current System State

### Staging Branch (`origin/staging` → local `staging`)
| Component | Status |
|-----------|--------|
| Core Models (7) | ✅ User, Category, Company, DemandRequest, ImpactFactor, Listing, ListingAttachment |
| Project Knowledge | ✅ 11 docs in `docs/project-knowledge/` |
| Migration | ✅ DDL-compliant |
| PBI-04 Code | ❌ **Removed** (was untracked, now deleted) |
| Master Project Plan | ✅ Restored from `.orca/drops/` to `docs/Master_Project_Plan.md` |
| Skills | ✅ 4 skills in `~/.hermes/skills/`, docs in `docs/skills/` |

### Worktrees
| Name | Status |
|------|--------|
| `staging` (main) | ✅ Active at `/home/agent-dev/projects/SymbioNusa` |
| Others | ✅ All test worktrees deleted |

---

## Ownership Guard (Enforced in Skill)

| PBI Range | Owner | Orchestrator Action |
|-----------|-------|---------------------|
| **PBI-01, 02, 03** | **Mayhesa (PM)** | ✅ Full orchestration |
| **FND-*** | **Mayhesa (PM)** | ✅ Full orchestration |
| PBI-04, 05, 06 | Dhafi | ❌ Reject — branch `dhafi` (FND-04 sendiri milik Mayhesa) |
| PBI-07, 14, 15, 16 | Zacky | ❌ Reject — branch `zacky` |
| PBI-08, 09, 10 | Haris | ❌ Reject — branch `haris` |
| PBI-11, 12, 13 | Inaya | ❌ Reject — branch `inaya` |
| PBI-17, 18, 19, 20 | Rosita | ❌ Reject — branch `rosita` |

---

## Next Phases (Proposed)

### Phase 8B: Skill Improvement Pipeline (Meta-Skill)
Auto-lint → auto-fix → test → regen docs → commit if green for all skills.

### Phase 9: Production Hardening
- Metrics dashboard (velocity, defect rate, cycle time, delegation effectiveness)
- Alerting (CI failure, worker timeout, Jira sync drift)
- Runbooks (worktree stuck, agent spawn failure, MCP disconnection)
- SLO definitions (orchestration latency, review turnaround)

---

## Key Commands Reference

```bash
# Provision PBI worktree (Mayhesa's PBIs only)
orca worktree create --name pbi-01 --base-branch staging --activate --json

# Spawn OpenCode for coding
orca agent launch --agent opencode --worktree name:pbi-01 --surface terminal --prompt "..." --json

# Spawn Hermes for PM/orchestration
orca agent launch --agent hermes --worktree name:pbi-01 --surface terminal --prompt "..." --json

# Orchestration run
orca orchestration run-create --objective "PBI-01 end-to-end" --json
orca orchestration worker-start --spec "..." --worktree name:pbi-01 --agent opencode --json
orca orchestration check --wait --types worker_done,escalation,question --timeout-ms 900000 --json

# Jira sync (Atlassian MCP)
atlassian.searchJiraIssuesUsingJql({jql: "project = SYM AND sprint in openSprints()"})
atlassian.executeWrite({name: "transitionIssue", cloudId: "...", inputs: {issueIdOrKey: "SYM-XX", transition: {id: "21"}}})

# FigCraft UI design
figcraft.run_command({command: "collab.join", params: {link: "figcraft://join?server=wss://129.150.59.234:8443/figcraft/&file=0a415cb1d39fc627&token=..."}})
figcraft.run_command({command: "frame.create", params: {name: "PBI-01 Screen", width: 1440, height: 900}})

# Skill test & docs
cd ~/.hermes/skills/tests && python3 test_skills.py && python3 generate_docs.py
```

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Hermes not in Orca agent catalog | Use manual `hermes --tui` in worktree terminal |
| `worker-start --agent hermes` unsupported | Don't use; use terminal launch instead |
| Jira/FigCraft MCP disconnection | Verify MCP health in Phase 0 every session |
| PBI scope creep | Ownership guard + zero-flaw 6-gate review |
| Secret leakage | Never log tokens; use env vars; redact in reports |

---

## Decision Log

| Date | Decision |
|------|----------|
| 2026-11-02 | Lead Orchestrator = Hermes only for Mayhesa PBIs (01-03, FND-*) |
| 2026-11-02 | 4 core skills created, tested, documented |
| 2026-11-02 | Orca integration fully verified (worktree, agents, runs, workers) |
| 2026-11-02 | PBI-04 code removed from working dir; staging clean |
| 2026-11-03 | Master Project Plan restored from `.orca/drops/` |

---

## Resume Instructions

On next session:
1. Read this doc + `docs/Master_Project_Plan.md` + `AGENTS.md`
2. Verify: `orca status --json`, `atlassian.getAccessibleAtlassianResources()`, `figcraft.inspect_ui()`
3. Check staging branch: `git status` (should be clean)
4. Proceed to Phase 8B or 9 as directed

**System is ready. No real PBI work started.**