# Quickstart

For someone who wants to start a project from this repository, or to test it. The
[README](README.md) explains what each part is and why; this page is what to do.

## What you need

- `git`, and `python3` on the path (the guard hook and the scripts use it).
- An AI coding agent: **Claude Code** (reads `CLAUDE.md`) or **OpenCode** (reads the generated
  `AGENTS.md`).
- Only for diagram and workbook work: draw.io Desktop (for the `drawio` CLI export) and
  `openpyxl`.
- Licence: PolyForm Noncommercial 1.0.0 (`LICENSE`), so trying it out is fine; commercial
  use needs the author's permission. Third-party notes: `THIRD_PARTY.md`.

## Start a project

```bash
git clone https://github.com/EldwinPr/Projectinator.git my-project
cd my-project
rm -rf .git && git init                  # your history starts clean
git config core.hooksPath .githooks      # turns on the commit-message check
```

Then make it yours, in this order:

1. **`context/general-rules.md`**: fill the two `_This project:_` placeholders (the language
   your business terms use, and `trunk-only` or `branch-per-issue`).
2. **`CLAUDE.md`**, section "Never": once the project has code, write its exact test
   commands there, including any flag that is load-bearing.
3. **`.agents/plugins/repo-guard/hooks/guard.py`**: add stack-specific commands to
   `PROJECT_RULES` when you have a stack (the file shows the shape).
4. Drop client material into `input/<YYYY-MM-DD-slug>/` with a `notes.md` (see
   `input/README.md`). Delete `context/document-writer-only/examples/` if you do not want the
   demos.
5. `python3 context/sync-manuals.py` after editing `CLAUDE.md`.

## Your first session

Open your agent in the folder. It reads `CLAUDE.md`, which sends it to `context/RULES.md`.
Things to say:

- *"I have notes from the client kickoff. File them under input/ and extract the requests."*
- *"Add a tracker row for request R-3, and plan it."* (a plan appears under `pm/issues/`)
- *"Implement issue 1 from its plan."* (the middle layer plans; the `coder` subagent builds)
- *"Run the distill pass."* (after a dozen or so entries in `context/gotchas.md`)

The loop: `input/` → `docs/requests.md` → a `pm/tracker.yaml` row → a plan when the change is
observable → build → verify → `pm/archive.yaml` + `pm/log.md`. Lessons go to
`context/gotchas.md` and the distill pass files them where they belong. Only the ERD has to
stay current; other diagrams are optional.

## Five-minute check that it works

| Run | Expect |
|---|---|
| `python3 context/lint.py` | `0 error(s)`; at most a warning that the commit hook is not installed |
| `python3 context/sync-manuals.py --check` | `ok: AGENTS.md is current` |
| `python3 .agents/plugins/repo-guard/hooks/guard.py --selftest` | `ok: 21 denied, 17 allowed` |
| `git commit --allow-empty -m "test"` (hook installed) | rejected: needs `Refs <id>` or `(no issue)` |
| Ask the agent to run `git add -A` | blocked by `repo-guard` |

## If you are testing it

Please note, and send back:

- Where the agent did not follow `CLAUDE.md` or `context/RULES.md`, and what it did instead.
- Any guard denial that was wrong (a legitimate command blocked). It is string matching and
  errs toward denying, so false positives are expected and useful.
- Anything that failed on your setup, especially Windows (`python3` versus `python`).
- Instructions you had to read twice.

## Known limits

- The guard is string matching, not a sandbox. OpenCode gets the base rules only; the
  stricter "executor" rules apply to Claude Code's `coder` subagent.
- `context/index/inventory.md` (the generated code index) is described but not included: you
  write the generator for your stack, or grep.
- `context/coder-only/` is deliberately thin until a stack is chosen; the README there says
  what to write and when.
- Multi-agent extras beyond a middle layer and its subagents (separate executors, a
  bookkeeper agent) are not part of this template.
