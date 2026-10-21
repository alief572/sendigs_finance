# Multi-Agent Setup

Who does what, and how work moves between agents. **The baseline is one middle-layer
agent** (Claude Code or OpenCode) that plans, does the work itself or delegates it to a
subagent, reviews, and commits. Everything below works with just that.

## Roles

| Role | Agent | Does | Never |
|---|---|---|---|
| **Middle layer** (one per session) | Claude Code or OpenCode | Discusses one project; classifies the work; writes and commits the plan; does it or delegates; reviews; stages, commits, updates the tracker | Delegates without a committed plan; accepts a delegated report unchecked |
| **Subagent** | `.claude/agents/<name>.md`: `coder`, `distill`, the drawio and workbook authors | One bounded job from the caller's prompt; reports back | Takes work from anyone but the middle layer; works outside the plan's scope. `coder` also never stages, commits or pushes |

- Small tasks the middle layer does itself; larger, repetitive or reading-heavy ones it delegates.
- Deterministic outputs stay with their scripts (`context/sync-manuals.py`, and any ERD
  generator the project adds), run by whoever changed the source.

## Flow

1. **Classify and plan** (middle layer): `general-rules.md` → Work Classification, then
   Planning Gate. Substantial work gets `pm/issues/<id>-<slug>/plan.md`, acceptance criteria
   as runnable commands, committed before anyone builds from it. Nothing decided stays in
   chat: it lands in `decisions.md`, `docs/` or `pm/`.
2. **Execute**: the middle layer itself, or a subagent given the plan path.
3. **Gate**: the plan's acceptance criteria pass.
4. **Review** (middle layer): reproduce reported failures yourself (`general-rules.md` →
   Working Discipline), read the diff against the plan's scope, run the suite once.
5. **Record**: stage by path, commit when the user asks, move the tracker row
   (`general-rules.md` → Definition Of Done).

## Delegating to a subagent

The subagent starts cold: it knows its definition, your prompt and whatever it reads.
Whatever the prompt leaves out, it either guesses or reads, and every file read is paid for
again on every later turn. A `coder` prompt names:

```
Execute pm/issues/<id>-<slug>/plan.md (committed; issue <id>).
Read only: the plan, CLAUDE.md, <the convention files this task covers>.
Out of scope: <what nearby code looks tempting but is not in the plan>.
Verify: <exact commands>   (or: "verification deferred: report what to run")
```

- **Model.** Pin one in the agent definition when the project settles on it. A light model
  makes the prompt above non-optional.
- **One test run at a time.** The suite shares one test database, so two subagents that run
  tests must not overlap (`CLAUDE.md` → Never). A read-only one, or one told "verification
  deferred", can run beside it.
- **The subagent never commits.** The caller reviews, stages by path and commits; the guard
  enforces this for `coder`.

## Context each agent loads

| Agent | Loads by itself | Here |
|---|---|---|
| Claude Code | `CLAUDE.md` | Source manual |
| OpenCode | `AGENTS.md` (also named in `opencode.json`) | Generated: `CLAUDE.md` + `context/manual-opencode.md` |

Regenerate with `python3 context/sync-manuals.py`; never edit `AGENTS.md`.

## Guardrails

One script, `.agents/plugins/repo-guard/hooks/guard.py` (JSON on stdin, exit 2 denies),
checks every tool call **before** it runs, whatever the mode or approval. Hooks are the
guarantee; approval prompts are judgement. Self-check: `python3
.agents/plugins/repo-guard/hooks/guard.py --selftest`.

| Agent | Wired in |
|---|---|
| Claude Code | `.claude/settings.json`, `PreToolUse` on `Bash\|Read\|Write\|Edit\|MultiEdit\|NotebookEdit\|Grep\|Glob` |
| OpenCode | `.opencode/plugin/repo-guard.ts`, `tool.execute.before` |

- **Every agent:** no writes to `input/`, `.git/`, `AGENTS.md`; no reading or writing `.env`;
  no `git add -A` or `git add .`; no deleting `context/gotchas.md`; plus `PROJECT_RULES`.
- **Executor strength** (no staging, committing, pushing, stashing, restoring, rebasing,
  resetting, cleaning, or making a branch) applies in Claude Code to every call made inside a
  subagent listed in `EXECUTOR_AGENTS` (`coder`), which the hook recognises by `agent_type` in
  its input. **OpenCode does not tell the hook which agent is calling**, so its `coder` has the
  base strength only and its "never commit" rests on its definition.
- **`PROJECT_RULES` is empty on purpose.** Once the stack is chosen, add the commands that are
  dangerous in it (tests outside their container, a database wipe) next to the exact commands
  in `CLAUDE.md`'s "Never" section.
- String matching: stops honest mistakes, not a deliberate workaround, and errs toward
  denying. A command whose text merely contains `.env` is blocked. Models usually refuse on
  their own first; the guard is for when they don't.
- Another runtime needs its own small adapter that feeds `guard.py` the same JSON.

## Agent definitions

Each subagent is written **once**, in `.claude/agents/<name>.md`. OpenCode's
`.opencode/agent/<name>.md` is routing plus "read `.claude/agents/<name>.md`" plus a short
must-survive list; the mirror may not contradict the definition. Adding an agent: write the
definition, then its mirror.

## Setting up a machine

The repo carries everything project-specific. Each clone needs:

1. `git config core.hooksPath .githooks`: turns on the commit-message check.
2. `python3` on the path (the guard and the scripts use it; on Windows the launcher may be
   `python` or `py -3`, so change the command in `.claude/settings.json` and `repo-guard.ts`).
3. Start every agent with the repository as its working directory.
4. Run `python3 context/lint.py` after touching `context/`.
