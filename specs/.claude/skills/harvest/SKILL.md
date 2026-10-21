---
name: harvest
description: Use at the end of a session where the user explained how part of the business works, corrected a misunderstanding, or gave the reason behind a design — or when the user says "harvest", "save the context from this chat", "remember how this works". Reads the user's own messages from the transcripts and drafts shape entries (`type` shape) into context/gotchas.md for the user to approve. Never writes context/shape/ directly; the distill pass promotes.
---

# Harvest

Turn what the user **said** into `type: shape` entries in `context/gotchas.md`.
Shape is the user's word about what the business means, why, and which way to
lean. Its rules, and why it exists, are in `context/shape/README.md`.

## 1. Get the evidence

Only the user's own messages count. Extract them — never work from memory of the
chat, and never from what an agent said:

```bash
python3 .claude/skills/harvest/extract_user_messages.py --session <id-prefix>
python3 .claude/skills/harvest/extract_user_messages.py --since 2026-01-20 --grep 'order|invoice'
```

The script reads both Claude Code transcripts (`~/.claude/projects/<slug>/`)
and opencode's database, for sessions run from this repository. Run it from the
repository root: the transcript directory is derived from the working
directory.

Default scope is the current session (its transcript id is the newest `.jsonl`
in the project's transcript directory). If the user names an area or a date
range, use `--grep` / `--since` instead.

## 2. Keep only shape

Keep a message when it says something a future session would otherwise have to
be told again:

- what a business term or step **means**, or who owns it
- the **reason** behind a rule or a design
- a **correction** of an agent's misunderstanding ("no, it's more like…")
- a **leaning** — what the user prefers when a case is ambiguous
- an open question the user flagged as unsettled

Drop: one-off UI tweaks, bug reports, instructions for the task at hand, status
chatter, and anything that is mechanism (that goes into gotchas under its normal
`type`, if anywhere). Drop anything already said in `context/shape/{area}.md` or
elsewhere in `context/` — grep before drafting. A later message that reverses an
earlier one wins; draft only the final position and note that it replaces the
old one.

## 3. Draft, then ask

Show the user the drafts **before** writing anything — a short list, one line
of claim plus the quote each. They approve, trim or reject. Then append the
approved ones to the bottom of `context/gotchas.md`:

```markdown
## Short name for the idea

- **type:** shape
- **area:** orders
- **date:** 2026-01-28

The claim, in one or two sentences, in the shape file's voice.

**Quote:** "the user's words, trimmed, typos left alone" (2026-01-15)
**Replaces:** the earlier claim it reverses, if any.
```

Every entry carries a quote and its date. No quote → it is an inference, and it
does not go in.

## Never

- Never write `context/shape/` directly. The distill pass promotes
  (`context/distill.md`).
- Never quote an agent as if it were the user.
- Never append without the user's approval of the drafts.
- General preferences of the user that are not about a business area belong in
  agent memory, not here.
