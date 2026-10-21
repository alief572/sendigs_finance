# Building the layers

Reasoning and checklists for starting a `context/` layer. Moved out of the files agents load
(`coder-only/README.md`, `index/README.md`, `index/map.md`), where they cost tokens on every
task but only matter when a layer is created or reshaped. Nothing here is a working rule; the
layer files hold those.

## `coder-only/`: before you write `testing.md`, and if you add CI

### What `testing.md` will need to say

These held in the project this framework came from, independent of its stack.
Write each down, measured against your own setup, when the suite exists:

- **Never overlap two runs against one shared test database.** Two runs that
  both migrate and truncate the same database — a backgrounded one and a
  foreground one, or two terminals — deadlock each other and fail whatever
  happened to be running. One overlap produced 20 unrelated failures, another
  92, none of which reproduced alone. **A failure count that moves between runs
  is the tell**; a real regression fails the same tests every time.
- **Parallel runs are safe only when each worker gets its own database.** That
  property does not transfer between applications or configurations, so state
  why it holds for this one.
- **Parallelism pays off locally more than on CI.** A suite whose cost was
  per-test application boot went from 313s to 57s on 16 local cores, but only
  from 361s to 288s on a 2-core CI runner. Measure on each before promising a
  number.
- **Format only the files you changed**, with the formatter's changed-files
  mode. A bare run reformats the whole repository and buries the real diff.
- **Run a migration's rollback, not just the migration.** A migrate-then-rollback
  check found two `down()` methods that had never worked. With manual deploys, a
  broken rollback is otherwise discovered mid-incident, at the moment it is
  needed.

### If the project adds CI

Record the choices as a decision; these are the ones that proved to matter:

- **Decide whether CI reports or gates**, and say which. Reporting-only is
  defensible — but then a red build will not stop a bad commit, and that
  consequence is accepted in writing rather than papered over.
- **A check that cries wolf destroys CI's only power, which is being read.** Run
  change-based checks (tests, format) per push, and time-based checks
  (dependency vulnerability audits, which go red on someone else's disclosure)
  on a schedule.
- **A check cannot watch what the workflow skips.** Skipping documentation-only
  pushes to save minutes also blinds any check about documentation files; see
  `context/distill.md` → "When it runs".
- **Keep deployment out of it unless it is chosen deliberately.** Anything
  committed that schedules itself runs wherever the repository is pulled —
  including a production server deployed by `git pull`.

## `index/`: the inventory generator contract

Write the generator in whatever suits the stack. What matters is the contract:

**1. One line per thing, answering both questions at once.**

```
Name | path/under/src | Kind -> what it depends on
```

A single `grep PaymentService` must return where it lives **and** what it
touches, with no second lookup in a second file. Splitting those answers across
a paths file and a dependencies file was tried in the project these conventions
came from and cost roughly 22k tokens for the same information.

**2. It is grepped, not read.** Say so in the file's own header. A 400-line
inventory is an excellent grep target and a useless thing to load whole — and a
reader who does not know that will load it whole.

**3. Edges come from the source, never from naming.** An edge inferred from a
naming convention is a guess that looks like a fact.

**4. Drop edges that carry no information.** Framework scaffolding that imports
itself by construction inflates the file and tells a reader nothing the path
did not.

**5. The generator refuses.** This is what keeps it alive. A generator that
silently emits a partial result produces a file that is quietly wrong; one that
exits non-zero when something is unclassified — a new module nobody assigned, a
reference nobody categorised — forces the human decision that keeps the index
honest. Take counts from the source of truth (the database, the compiler, the
file system) rather than counting them off the artifact.

**6. One definition of "module", read by every generator.** If the ERD, the
class diagram and the inventory each group things into modules, the grouping
lives in one place — typically the ERD generator's table-to-module list — and
the others read it from there instead of keeping a copy. Two lists of modules
will disagree within a month, and a reader has no way to tell which one is
right.

**7. Regenerate as part of the change that made it stale**, and commit the
result. A stale index is worse than no index: it sends a reader confidently to a
path that moved. There is no automated freshness check here and there is not
meant to be one — running the generator is a line in the definition of done, not
a pipeline someone else maintains.

## `index/map.md`: why it is not machine-parsed

**NOT MACHINE-PARSED, by design.** This is an index of pointers — closer to a
class diagram in text than to a config file. It is read, never loaded: no
tooling parses it, and `note:` values are prose that may contain colons,
quotes, or anything else that helps a reader. The `key: value` shape below is
for human and agent legibility only. The file is `.md`, not `.yaml`, precisely
so that nothing — reader or tool — expects it to parse. Do not "fix" it into
strict YAML, and do not build tooling that parses it.

(Renamed from `map.yaml`. The extension was the only signal a reader got
before this warning, and it promised the wrong thing.)
