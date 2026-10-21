# Shape Notes

How each business area **thinks**: its skeleton, its intent, the reasons behind
its rules, and which way to lean when nothing else says. The things a session
would otherwise have to be told again.

Not in the startup set. `context/RULES.md` points at the file for the area a
task touches.

## Why this exists

The learning loop (`context/gotchas.md` → `context/distill.md`) compiles **facts
about the system**, and it verifies each one against the repository. That is
right for facts, and it shuts out a second kind of knowledge entirely: **what
the business means, why a rule exists, and which way to lean when nothing
says.** "Taking a sample is free, testing it is not" or "the app is always
right, the PDF is not" cannot be verified against code, so the pass dropped or
parked them, and they lived only in chat. Every new session then had to be told
the shape of an area again.

Capture was also incidental: an entry existed only if a working session thought
to append one. Most of what the user explained — corrections, "no, it's more
like…", the reasoning behind a design — was never written down anywhere.

Retrieval over raw transcripts (RAG) was considered for this and rejected.
Retrieved chat fragments cannot tell a settled position from one that was later
reversed, and they read with more authority than they have. Shape has to be
distilled to stay short enough to load whenever its area is touched. Retrieval
stays reserved for large, stable reference sources — regulations, contracts —
where there is nothing to distil.

## What goes here, and what does not

| Here | Not here |
|---|---|
| What the business means by a thing, and why | What the code does line by line: `context/index/map.md` |
| The principle several rules derive from | An architectural choice and its consequence: `context/index/decisions/` |
| The flow, the roles, who decides what | A convention or trap: `context/coder-only/` |
| Which way to lean when a new case is ambiguous | Issue state: `pm/` |
| Questions that are genuinely still open | A general preference of the user's not tied to an area: agent memory |

If a line needs a `file:line` to be true, it is mechanism. It belongs to its
owner; a shape note may **point** there, never restate it.

## Evidence: every claim carries one of two

- **The user's word.** A short quote and its date, or "user, <date>" for a
  decision recorded as theirs in `pm/log.md` / `pm/archive.yaml`. This is the
  only evidence for intent, reasons and leanings.
- **The system.** A pointer to code, a decision, or a doc (`OrderService::cancel()`,
  a decision number, `docs/statuses.md`). This is the evidence for skeleton:
  what the flow is, which roles exist, what states a thing moves through.

A claim with neither is an agent's inference and does not go in. Never present a
code-derived claim as the user's intent: the code says what *is*, and only the
user says what is *meant*.

## Rules

1. **Code wins, and conflicts are raised.** Precedence is code and tests >
   `context/` facts and decisions > shape > transcripts. When a shape note and
   the code disagree, ask the user which one moved; do not quietly follow
   either.
2. **Short.** A file should read in a few minutes. Past that, it is restating
   mechanism, so cut it back to pointers.
3. **Settled vs open.** Open questions live in their own section so a reader
   never mistakes one for a rule.
4. **Written through the loop.** New shape arrives as a `type: shape` entry in
   `context/gotchas.md`, usually drafted by the harvest skill
   (`.claude/skills/harvest/`), and is promoted by the distill pass
   (`context/distill.md`). No second buffer — that would be a second thing to
   lose. A statement the user reverses **replaces** the old claim; it is not
   appended beside it.
5. **The first seed may be written directly.** Bootstrapping an area from
   surviving transcripts, `pm/log.md` decisions and the code is a one-off.
   Everything after it goes through the buffer.

## File layout

One file per business area, named in the business's language. Sections, in
order, skipping any with nothing to say:

- **In one paragraph**: the area as the user would explain it to a new hire.
- **Flow**: the skeleton, from the system.
- **Principles**: the few ideas several rules derive from.
- **Who decides what**
- **Leans**: when in doubt, which way.
- **Open**: unsettled, and who can settle it.
- **Where the mechanism lives**: pointers only.

## Areas

Draw the value chain once, left to right, with each area under the part of it
that it covers — a reader should see which file to open before reading the
table.

| Area | File | App | Message mentions |
|---|---|---|---|
| — | _None yet._ | — | — |

"Message mentions" is the trigger list: read the area's file when the user's message uses
one of those words — the business's own terms, in its language, not the code's names. It is
the only copy; `context/sync-manuals.py` copies it into the OpenCode manual. Adding an area:
add its row here, then run `python3 context/sync-manuals.py`.

## Coverage

Seed early. Claude Code prunes its transcripts after 30 days by default, so the
user's explanations from the first month are gone unless someone harvests them
before then — in the project this layer came from, everything older than that
had to be reconstructed from `pm/log.md` and `pm/archive.yaml`. An area the user
has discussed little is mostly skeleton; mark its leanings as missing rather
than guessing them.
