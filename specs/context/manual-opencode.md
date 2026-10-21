## OpenCode Operating Procedure

This section exists only in `AGENTS.md`. Claude Code gets this guidance from its
own tooling and per-user memory; OpenCode has neither, so it is written out
here step by step. **Follow it literally.** Where a step points at a file, the
file owns the rule: the step tells you *when*, the file tells you *what*.

(Source: `context/manual-opencode.md`, appended by `context/sync-manuals.py`.
Edit it there, never in `AGENTS.md`.)

### Step 1: Classify the message before doing anything

| The user says | You do | You do NOT |
|---|---|---|
| a question; "just answer", "explain", "what do you think", "don't do anything yet" | answer, reading files if needed | edit, migrate, commit, create issues |
| "check …", "look up {id}", "gather what you can", "look around" | investigate and report findings | edit |
| "plan", "write the plan", "make an issue" | write `pm/` and the external tracker only | touch application code |
| "go ahead", "do it", "implement", "fix it" | implement, after Step 3 | skip Step 3 |
| "commit", "push" | commit or push, as asked | push when only "commit" was said |

If you cannot tell which row applies, ask one short question.

### Step 2: Load context in this order

Stop as soon as you have enough; a question may need only 1–3.

1. `context/RULES.md`, once per session.
2. `pm/active.json`, then grep `pm/tracker.yaml` for the issue id or a keyword.
   Most work already has an issue.
3. **The shape note for the area** (table below), before reading code. It says
   what the area means and which way the user leans.
4. To find code: grep `context/index/inventory.md` for a name;
   `context/index/vocabulary.md` for a business term; `context/index/map.md`
   for ownership.
5. Implementation only: `context/coder-only/README.md`, then only the files it
   names as relevant.

#### Trigger words → shape note (generated from the Areas table in `context/shape/README.md`)

{{SHAPE_TRIGGERS}}

List the words the user actually types — UI labels and the business's own
terms, in the business's language — not the code's names for them. A message
can hit two rows; read both.

### Step 3: Before you change anything

- **Application code** needs the plan the Planning Gate asks for:
  `context/general-rules.md` → Planning Gate. No plan where one is required →
  stop, say so, and offer to draft one.
- **Data.** Follow the "Never" section above. Before any command that writes
  data, print the environment, connection, database name and target.
- **Tests.** Use exactly the commands the "Never" section names. Never start a
  test run while another one against the same test database is still running,
  including your own backgrounded one.
- **Formatter.** Only on files you changed. Never run it with no arguments.
- **Unrequested hardening, cleanup or refactors.** Do not disrupt a working
  environment for them. Mention what you saw and move on.
- **Scope.** If the change needs something outside the plan, stop and ask.
  Never fold it in silently.
- **Framework APIs.** Verify against the installed version or its current
  documentation. Never from memory.

### Step 4: How to answer

- **Short and plain first.** Lead with the answer in a few lines, and offer
  detail rather than dumping it.
- **Open decisions as numbered questions** the user can answer tersely ("1.
  yes 2. the most recent one").
- **Use the business's words** as they appear in the UI. Do not translate them.
- **Say what you verified and how.** If you did not run something, say so.

### Step 5: Before you finish

- **Capture what the user taught you.** If they explained how something works,
  why a rule exists, corrected you, or stated a preference about an area:
  draft a `type: shape` entry with their exact words and the date, show it to
  them, and append it to `context/gotchas.md` only if they agree. The format is
  in `gotchas.md` → Entry format; the `harvest` skill drafts these from the
  transcripts.
- **Capture what the code taught you.** A trap or a surprising fact goes into
  `context/gotchas.md` under its normal `type`.
- **Never write `context/shape/` directly.** The distill pass promotes
  (`context/distill.md`).
- **Commit or push only when asked.** How: `context/general-rules.md` →
  Version Control.

### Never

- Never let a shape note override the code. If they disagree, tell the user.
- Never edit `AGENTS.md` by hand. It is regenerated.
- Never skip the "Never" section above because a step here seemed to cover it.
