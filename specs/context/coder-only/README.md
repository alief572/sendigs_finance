# Coder Context

The default task context for **building, debugging, reviewing, operating and
maintaining the application**. Artifact work uses
`context/document-writer-only/` instead, and the two are not loaded together.

Until a stack is chosen this file is the only thing here, and that is correct:
the conventions below cannot be written honestly before there is code to
describe. A directory of plausible-sounding guesses about a framework nobody
has picked is worse than an empty one — it reads as decided.

## Creating this layer

Add a file when it becomes true, not in advance, and give each **exactly one
subject**. The set that has proven necessary:

| File | Owns |
|---|---|
| `stack.md` | What is installed and at which version; which application a session is in, if there is more than one; repository-level tooling |
| `{framework}-conventions.md` | How this project uses its framework, and where it deliberately departs from the defaults |
| `database-conventions.md` | Keys, naming, nullability, soft deletes, enums-versus-strings, and which references are enforced |
| `testing.md` | How to run the suite, **which database it touches**, and every hazard that can destroy real data |
| `project-exceptions.md` | Where this project knowingly violates its own conventions, and why |

Two carry more weight than the rest:

- **`testing.md` is safety-critical.** The most expensive mistake available in a
  mature project is a test run pointed at a live database. Write the exact
  commands, including any flag that is load-bearing, and say what happens
  without it. If a command is dangerous when typed the obvious way, that
  sentence belongs in `CLAUDE.md` too — it is one of the few rules that cannot
  rely on a pointer being followed.
- **`project-exceptions.md` prevents the same argument twice.** An undocumented
  deliberate violation gets "fixed" by the next person and re-violated by the
  one after that.

What `testing.md` will need to say, and what to record if the project adds CI, is in
`context/guide/building-the-layers.md` (not loaded for ordinary coding tasks).

## Load Order

Read in this order and stop when the task is covered:

1. `stack.md` — confirms what you are working in
2. The task-relevant convention file here
3. The specification that governs the behaviour being changed — statuses,
   permissions, the requirement it came from — and `context/shape/{area}.md`
   for what the area means and which way to lean, when one exists
4. `context/index/map.md` for ownership, `context/index/inventory.md` (grep it)
   for the complete picture, `context/index/vocabulary.md` for business language
5. The confirmed plan under `pm/issues/{slug}/`, when the work has one

## Maintenance Workflow

1. Reproduce the problem, or establish concrete evidence from code, tests, logs,
   or a scoped data inspection.
2. Identify the root cause and the affected behaviour before editing.
3. Inspect nearby implementations and every affected persistence object.
4. Make the smallest safe correction that preserves unrelated behaviour.
5. Add a regression test when feasible.
6. Run the narrowest verification first, then the relevant broader checks.
7. Update the ERD if the schema moved, and durable context only if the resulting
   system truth changed.

**Do not answer an actionable bug report with a documentation proposal.** Carry
the workflow through implementation unless the user asked only for analysis.

## Boundaries

- `context/coder-only/` records durable implementation truth and conventions.
- `context/index/` maps behaviour to code and records architectural decisions.
- `docs/` holds specifications and client-facing artifacts. It is not the
  default destination for maintenance work.
- `pm/` records work state and history. A durable fact discovered while closing
  an issue belongs in `context/`, not buried in a `plan.md` that gets deleted
  when the issue closes.
- Inspect current code and schema before trusting an old issue plan.

Do not duplicate a rule across files. Update its single durable home and remove
the superseded copy, so future sessions cannot read two versions of it.
