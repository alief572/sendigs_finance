# Cross-model review with `opencode`

Generic, reusable instructions for running `opencode` as a reviewer or sparring
partner from any Claude Code session. Nothing here is specific to this project —
it can be copied to another repo as-is. If a project grows its own rules about
*when* to reach for this (ticket criteria, required sign-off), put those in a
separate file and link to it from here; do not assume such rules exist until the
file does.

The point is a model with **different blind spots**, not a second opinion from
the same mind.

## Principles

- **Who runs it:** the main Claude Code session, which has Bash. Do not add Bash
  to a read-only subagent to accommodate this.
- **Verify every claim against the source before acting on it.** Another model
  invents things exactly as readily as any model does; "opencode said X" is not
  evidence of X. This is the rule that makes the whole technique safe, and it is
  the first one abandoned under time pressure.
- **Never `Read` the raw JSONL into your own context.** Every file the other
  model reads is echoed back inside its `tool_use` events. Measured on one real
  session: the turn where it read two files produced **46 KB**; the next turn in
  the same conversation, which read nothing, produced **1.8 KB**. Extract the
  reply, discard the rest.
- **Give path pointers with line ranges**, not pasted file contents — and not
  "read the whole file" when one section is relevant.
- **One session, one topic.** Do not continue a session about topic A to discuss
  B. To branch from an existing history without disturbing it, use `--fork`.
- **The reviewer is not blind, and this surprises people.** See below.

## What the other model already knows before you say anything

`opencode` auto-loads project context at startup: the manual named in
`opencode.json`'s `instructions` array, every skill under the configured skill
paths, and the instructions of each enabled MCP server. Asked to list what it
had loaded, the model correctly named the project manual, five skills and two
MCP servers — before reading a single file. That start-up context measured
**~22k input tokens**.

Two consequences:

- **It inherits your framing.** An "independent" audit that starts from your own
  manual is less independent than it looks. When you want a genuinely outside
  read, say so explicitly in the prompt ("ignore the project manual's framing;
  judge this against X") or point it at a different baseline.
- **If the repo's manual is not the filename `opencode.json` loads, the reviewer
  starts blind.** Check the `instructions` array when setting this up in a new
  repo. A repo whose manual is `CLAUDE.md` needs that name listed, or the model
  begins with no project context at all.

## Setup

```bash
opencode run "<message>" --agent plan -m <provider>/<model> --format json --title "<session-name>"
```

- `--agent plan` is **read-only, enforced by the runtime** — not a promise the
  model makes. Asked directly to create a file, it answered "I can't create
  files while plan mode is active" and returned a plan instead; no file was
  written. Keep "do not edit any file" in the prompt as belt-and-braces, but the
  flag is the actual mechanism. Use `--agent build` ONLY when the task really is
  to change files.
- `--format json` emits JSONL. Never read it raw — extract (below).
- `--title` names the session. This is the handle you recover it by later, so
  make it descriptive and unique per topic (`spar-cache-strategy`,
  `audit-migration-rollback`).
- `-m` takes a model id **exactly as `opencode models` prints it**. Copy it from
  there; see the error section for why a typo is expensive to diagnose.
- `--variant high` raises reasoning effort where the model supports it.
- `--pure` runs without external plugins, if you need a reproducible baseline.

Measured envelope on a fast model (`deepseek-v4.1-flash`): **13–31 s per turn**,
fractions of a cent. A fresh session pays for the full startup context; a
continuation reads it from cache and costs roughly a fifth as much. Cost is not
a reason to avoid this, but starting a new session for every question is waste.

## Extracting the reply

Use `jq` when it is available — it unescapes properly:

```bash
# the reply text (one text part per reply; do NOT pipe to `tail -1`,
# which truncates a multi-line answer to its last line)
jq -r 'select(.part.type=="text") | .part.text' out.jsonl

# the session id, for continuing
jq -r 'select(.sessionID) | .sessionID' out.jsonl | head -1

# what it cost
jq -r 'select(.part.tokens) | "in=\(.part.tokens.input) out=\(.part.tokens.output) cost=$\(.part.cost)"' out.jsonl | tail -1

# which tools it actually used — worth checking that it really read the files
jq -r 'select(.part.tool) | .part.tool' out.jsonl | sort | uniq -c
```

Fallback without `jq`. This works, but leaves `\"` escapes in the output:

```bash
grep -o '"sessionID":"[^"]*"' out.jsonl | head -1

grep -o '"type":"text"[^}]*"text":"[^"]*\(\\.[^"]*\)*"' out.jsonl \
  | grep -o '"text":"[^"]*\(\\.[^"]*\)*"$' \
  | sed 's/^"text":"//;s/"$//' \
  | tail -1 \
  | sed 's/\\n/\n/g'
```

## When a run fails

**Check the exit code.** With `> out.jsonl 2>&1` nothing reaches your terminal,
so a failed run looks exactly like a successful one until you extract an empty
reply.

| Failure | Exit | What you get |
|---|---|---|
| Bad model id | 1 | A JSON error object saying **"Unexpected server error"** — it does *not* say "unknown model". A typo in the model name is indistinguishable from a provider outage. |
| Bad session id | 1 | Plain ANSI-coloured text, `Error: Session not found` — **not JSON**, despite `--format json`. Parsers must tolerate this. |

A failed run still creates a session, which appears in the list as
`New session - <timestamp>`. Harmless, but do not mistake it for a real one.

## Mode 1 — Conversation (stateful, bounded rounds)

For **pressure-testing** something before it is finalised — a design, an
argument, an outline, a draft. **Cap the rounds** (default 4; say so in the
prompt if you want different). Stop at the cap and take the best available
result rather than negotiating to consensus.

The pattern is consistent enough to plan around: **round 1** produces analysis —
often sharp but aimed at things you have already decided; **round 2**, after you
supply the constraints it could not have known, produces something directly
usable.

A real example of that shift. Round 1 returned three failure modes in a set of
rules, each anchored to specific line numbers. Round 2 — told which finding
didn't matter and why, told that "add a checklist" was the wrong shape of fix
for a solo-developer project, and asked for replacement sentences under 40 words
— returned two paste-ready edits. Same model, same session, 18 seconds.

```bash
# Turn 1 — new session
opencode run "<reviewer/sparring role + project context + path pointers with line
ranges (not pasted contents) + the specific thing to pressure-test + 'do not edit
any file' + a cap on how many points to return>" \
  --agent plan -m <provider>/<model> --format json --title "spar-<topic>" \
  > /tmp/round1.jsonl 2>&1

# extract the sessionID and the reply (see above)

# Turn 2+ — MUST pass -s <sessionID> to stay in the same history
opencode run "<the context it could not have known + which findings you accept +
a concrete demand for this round: the output shape, and a length cap>" \
  -s <sessionID> --agent plan -m <provider>/<model> --format json \
  > /tmp/round2.jsonl 2>&1
```

After the last round: summarise into short notes, **verify each claim against
the source**, then apply what survives. Do not paste the raw transcript to the
user — report what was agreed, what was corrected, and what is still open.

## Mode 2 — Critique (stateless, single shot)

For an **independent audit** of something already written. Not a negotiation:
one call, no follow-up. Runs well in parallel with your own critique pass for
cross-checking.

```bash
opencode run "<independent-reviewer role + ALL context in ONE prompt: the
baseline or source of truth, the rules that apply, the exact files and line
ranges under audit + the report format you want (e.g. BLOCKER/MAJOR/MINOR, with
location and a concrete proposed fix) + 'do not guess; say so when unsure'>" \
  --agent plan -m <provider>/<model> --format json --title "audit-<topic>" \
  > /tmp/audit.jsonl 2>&1
```

Because there is no second round, **the prompt has to be complete up front.**

**Ask for findings anchored to `file:line`.** It costs the model nothing and it
is the difference between an audit you can verify in two minutes and one you
have to re-derive. In the example above, every finding carried line numbers, and
checking them was faster than reading the critique.

Then **verify every BLOCKER/MAJOR against the source** before acting — including
the ones that sound obviously right. If you also ran your own audit, merge and
dedupe the findings into one list before reporting.

## Which mode

| Situation | Mode |
|---|---|
| Draft, design or argument not yet final; pressure-test before locking it | Conversation (Mode 1) |
| Something is written; you want an independent audit | Stateless (Mode 2) |
| You want a genuinely independent opinion (no shared history) | Stateless, new session |
| You need fast iteration with context corrections | Conversation, capped rounds |

## Managing sessions

Sessions are recoverable from the CLI — you do not have to write ids down,
though recording one next to the work it belongs to still helps:

```bash
opencode session list              # id, title, last-updated — the reason --title matters
opencode export <sessionID>        # full session as JSON
opencode stats                     # token usage and cost
opencode session delete <sessionID>
```

Because `session list` is keyed by title, **a distinctive `--title` is the real
handle**. A session called "New session - 2026-09-11T14:38:30Z" is one you will
never find again.

When a topic is finished, stop passing `-s` for it. There is no need to delete
it unless you want the list clean.

## Prompt checklist

Before sending, confirm the prompt has:

- [ ] The role, and explicitly that this is a critique, not a collaboration
- [ ] Path pointers with line ranges — no pasted file bodies
- [ ] The constraints already decided, so it does not re-litigate them
- [ ] The output shape you want, and a cap on length or number of points
- [ ] A request for `file:line` anchors on every finding
- [ ] "Do not edit any file" (belt-and-braces behind `--agent plan`)
- [ ] "Say when you are unsure rather than guessing"
