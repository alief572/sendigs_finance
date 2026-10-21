# The Distill Pass

The compile step of the self-learning loop. It reads `context/gotchas.md`,
promotes what is durable into the file that owns it, drops what is not, empties
the buffer, and opens a pull request with the result.

```
context/gotchas.md  →  distill pass  →  context/ (compiled truth)
```

The runtime definitions are `.claude/agents/distill.md` and
`.opencode/agent/distill.md`, and both are deliberately thin — **this file is
the procedure**. Where they disagree, this one wins.

## When it runs

**Manually.** Somebody decides it is time and starts it. No cron, no timer, no
scheduled job — an unattended agent with write access to the repository is a
thing to adopt deliberately, not by default.

**Nothing surfaces staleness either, and a CI check is not the fix.** The
project this came from had one, and removed it as misleading: CI skipped
`**.md`-only pushes to save minutes, and appending to the buffer is a `.md`-only
change, so the check never ran on the commits that made the buffer stale. It let
the buffer pass its threshold without ever showing a warning — worse than no
check, because silence reads as "fine".

So noticing is a reader's job: past roughly a dozen entries, or a month since
the oldest `date:`, it is worth a pass.

## It opens a PR, and it does not open an issue

**Delivery follows the branching model** (`context/general-rules.md` → Version Control): where this says PR, a trunk-only project makes one commit instead, only when the user asks.

This pass is **exempt** from the `tracker row ⟺ capsule ⟺ external issue` rule.
It creates no `pm/` row and no external issue. Promotion is editorial work on
`context/`, not tracked work; mirroring every pass into the tracker would bury
it in issues that close the day they open.

Work on a branch. The promotions **and** the emptied buffer go in the same PR,
so a rejected PR loses neither — the buffer is only cleared on the branch where
its contents have already landed somewhere else.

**If a pass promotes nothing, do not open a PR.** An empty PR carrying an
unchanged buffer is noise. Report the dispositions and stop; that report is the
audit trail for a pass that found nothing durable.

## Three dispositions, all first-class

Every entry ends in exactly one of these. "Leave" is a decision, not a failure
to decide, and the report has a row shape for each.

| | When | Effect on the buffer |
|---|---|---|
| **Promote** | Durable, verified, and some `context/` file owns the subject | Entry removed |
| **Drop** | It no longer reproduces, the destination already says it, **or** it describes a condition an open `pm/` row exists to change | Entry removed, reason and owning issue id recorded |
| **Leave** | Deliberately deferred — waiting on an issue to close, or genuinely unresolved | Entry stays, with the reason and what would settle it |

The third drop case is the one that bites. An entry can be true, unduplicated,
and still not belong in `context/`: if a tracked issue exists to reverse the
condition, promoting it writes a line that the next commit falsifies. `context/`
records what is currently true, **not what is currently broken and scheduled for
repair.** Drop it and name the issue, or leave it until that issue closes —
never promote it.

## Procedure

**1. Read the whole buffer first.** Entries are appended over weeks and often
overlap; two entries about the same trap promote as one paragraph, not two.

**2. Verify every entry before promoting it.** Entries can be months old and the
repository has moved. If an entry cites a file, open it. If it describes a
command's behaviour, the claim must still hold. If it describes a rendered
diagram, render it. An entry that no longer reproduces is **dropped with a note
in the report**, not promoted — promoting a stale claim into `context/` is worse
than losing it, because everything downstream will then trust it.

**A partially-true entry is corrected, not discarded.** If the mechanism holds
but the citation has moved — a renamed file, a page that was restructured —
promote it with the citation corrected and note the correction. Drop only when
the underlying claim itself fails. Also check whether the wrong citation was
copied anywhere else, `pm/` included, and say so: a bad reference propagates.

**A `type: shape` entry is verified against the user, not the repository.** An
intent claim passes if it quotes the user or records their confirmation, with a
date; a skeleton claim (flow, role, state) passes if its code or doc pointer
still resolves. Drop it if a later user statement or a decision contradicts it
— and when promoting a reversal, *replace* the old claim in the shape file
rather than adding beside it. Check against code only that its pointers still
resolve; if the code plainly contradicts it, **leave** it and say so in the
report — that conflict is the user's to settle. An entry with neither a quote
nor a pointer is an agent's inference, not shape: drop it.

**3. Route each surviving entry to the file that owns the subject.** The `type`
field is a hint, not the decision:

| Subject | Owner |
|---|---|
| How to draw or author an artifact type | `context/document-writer-only/{that type}-conventions.md` |
| Cross-cutting draw.io mechanics, export and verification | `context/document-writer-only/drawio-general-guide.md` |
| Deriving use cases, workbook editing, `openpyxl` behaviour | `context/document-writer-only/workbook-conventions.md` |
| How to work in this repo, regardless of artifact type | `context/general-rules.md` |
| Which artifact or code owns which behaviour | `context/index/map.md` |
| Business term ↔ our name | `context/index/vocabulary.md` |
| An architectural choice **and its consequence** | a new file under `context/index/decisions/`, plus its row in `decisions.md` |
| Implementation conventions, once the project has code | `context/coder-only/{the relevant file}.md` |
| Repository tooling — `.gitignore`, exports, build, CI | `context/coder-only/stack.md`, or `README.md` while there is no code |
| Business intent, the reason behind a rule, which way to lean (`type: shape`) | `context/shape/{area}.md`, in the section its format names (`context/shape/README.md`) |

**Choose the section, not just the file.** Section choice follows the
destination's own headings. Adding a new section inside an existing file is fine
and is not "reorganising"; creating a new *file* is.

If nothing owns the subject, **say so in the report rather than inventing a new
file.** A new `context/` file is a structural change and wants a decision of its
own.

**4. Write it in the destination's voice, not the buffer's.** These files are
read at session start by every future session, so promoted text must be terse,
state the mechanism, and keep the evidence (a path, a command, a commit). Cut
the narrative. Before writing, check the destination does not already say it —
if it does, the entry was a duplicate and is dropped.

**5. Empty the buffer.** Delete the promoted and dropped entries; keep the
header and the `## Entries` section with its `_Empty. Append below._`
placeholder. **Never delete the file.** Entries marked **leave** stay exactly as
they are — removing one is a drop, and drops are recorded.

**6. Record what went where.** Three tables in the PR description, always:

| Was in the buffer | Promoted to |
|---|---|
| Gateway style attribute diverges from the spec | `document-writer-only/bpmn-conventions.md` → "Shape styles" |

Then one for **dropped** entries with the reason and any owning issue id, and
one for **left** entries with why each waits and what would settle it. All
three, every time — a reader must be able to account for every entry that was in
the buffer when the pass started.

That audit is the point: it is how a reviewer checks the pass without re-reading
the buffer, and how anyone later can tell where a rule came from.

## Scope limits

- **Never edit `pm/`.** Not the tracker, not the log, not a capsule.
- **Never touch application code.** An entry describing a bug does not become a
  fix — it becomes a `pm/` row, if anything, and that is the user's call, not
  this pass's.
- **Do not reorganise `context/`.** Promote into the existing structure. If the
  structure is wrong, say so in the PR and stop.
