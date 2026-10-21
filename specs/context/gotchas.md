# Gotchas — The Learning Buffer

Raw, uncompiled observations from real work. This file is the **input layer** of
the self-learning loop:

```
context/gotchas.md  →  distill pass  →  context/ (compiled truth)
```

It is the knowledge equivalent of `input/`: cheap to append to while working,
never authoritative, and periodically digested into the files that are. The
procedure that digests it is `context/distill.md`.

## Why this exists at all, given the repo once decided against it

An earlier version of this framework dropped the buffer, on the grounds that
every entry ended up duplicated into a conventions file anyway. That reasoning
holds only when someone files the lesson **at the moment they learn it**, into
the right file, in that file's voice. In practice the moment a lesson appears is
the moment attention is somewhere else — mid-task, with the real work
unfinished — and the choice on offer is not "file it properly or append it
here", it is "append it here or lose it". Losing it is what actually happened:
learnings defaulted into the issue log, which nothing reads at session start,
and the conventions files never received them.

So the buffer is back, with the constraint that makes it safe: **it is not read
as guidance.** It is intake, and the distill pass is what turns intake into
truth.

## Three rules

**1. This file is never deleted.** The distill pass **empties** it and leaves it
here with this header intact. Deleting it breaks the loop, and it breaks quietly
— the compile output stays behind and looks healthy while nothing new
accumulates. In the project this framework came from, the buffer was twice swept
away as migration debris, and both times nobody noticed for weeks. If you are
reorganising `context/` and this file looks like leftovers: it is not.

**2. It is not read at session start.** `context/RULES.md` deliberately omits
it. Entries here are unverified and unfiled; treating them as guidance defeats
the point of compiling them. Read it when you are appending to it, or when you
are running the distill pass.

**3. Nothing here is durable truth yet.** A rule that already has an owner in
`context/` belongs in that owner, not here. This file is for the observation
that does not yet have a home.

## What belongs here

Anything a future session would have wanted to know and could not have derived
from the artifacts themselves:

- a tool or library that does not behave the way its documentation implies
- a trap that cost real time, or that can destroy work
- a convention that emerged from a decision made in passing
- a piece of ownership that moved
- what the user said the business **means**, why a rule exists, or which way to
  lean — a `type: shape` entry, drafted by the harvest skill
  (`.claude/skills/harvest/`) and approved by the user before it lands here

Not: issue status, narrative history, or anything already stated in `context/`.
Those are `pm/tracker.yaml`, `pm/log.md`, and their existing owner respectively.

## Entry format

Append to the bottom. One `##` section per observation; delete the `_Empty. Append below._`
line when you add the first.

```markdown
## Short name for the fact

- **type:** convention | hazard | trap | ownership | decision | tooling | workflow | shape
- **area:** orders (shape entries only — the `context/shape/` file it feeds)
- **date:** YYYY-MM-DD
- **issue:** UC-07 (optional)

What is true, in one or two sentences, with a file path, a command, or a
rendered artifact where it helps.

**Why:** the mechanism — what causes this, so a reader can tell when it applies.
**How to apply:** what to do differently next time.
```

The **Why** line is what separates an entry that can be promoted from one that
cannot. "Gateway styles need `gwType=`" is a note; "the installed shape library
diverges from the spec's attribute names, so a plausible-looking style renders
blank" is a rule that generalises to the next attribute.

A `shape` entry replaces the **Why** / **How to apply** pair with the user's
own words, because the user is its only evidence:

```markdown
**Quote:** "the user's words, trimmed, typos left alone" (YYYY-MM-DD)
**Replaces:** the earlier claim it reverses, if any.
```

No quote and no pointer to code or a decision means it is an agent's inference,
and it does not go in. `context/shape/README.md` owns the rules.

`type` is a routing hint for the compile, not a taxonomy:

| type | roughly |
|---|---|
| `convention` | a project rule |
| `hazard` | a trap, especially one that can destroy work |
| `ownership` | which artifact or code owns which behaviour |
| `decision` | an architectural choice and its consequence |
| `tooling` | repository-level tooling, exports, build, `.gitignore` |
| `shape` | what the business means, why, which way to lean — **quote the user** and date it; the code cannot verify it |

**This is a hint about the kind of fact, not a destination.** Routing is decided
by `context/distill.md`, which owns the destination table — do not restate that
table here, or the two will drift.

## When to run a pass

Nothing surfaces staleness automatically, so noticing is a reader's job: a
glance at the `## Entries` headings gives the count, and the oldest `date:`
gives the age. Past roughly a dozen entries, or a month, it is worth a pass.

---

## Entries

_Empty. Append below._
