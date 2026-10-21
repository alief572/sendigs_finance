#!/usr/bin/env python3
"""Generate `AGENTS.md` from `CLAUDE.md`.

`AGENTS.md` (OpenCode and other runtimes that read it) is the same document as
`CLAUDE.md`. Regenerating one from the other by hand drifted, so the
regeneration is a script. A difference can exist in exactly two places, and
anything else is drift:

- the SUBSTITUTIONS list below;
- OPENCODE_APPENDIX, a section appended to AGENTS.md only. OpenCode has no
  per-user memory and follows pointers less reliably than Claude Code, so it
  gets an explicit step-by-step procedure. It is a routing layer: it says *when*
  to read an owner and names it, and restates a rule only as a one-line trigger.
  Its `{{SHAPE_TRIGGERS}}` marker is filled from the Areas table in
  `context/shape/README.md`, the one copy of the shape trigger words.

Run from the repository root after editing `CLAUDE.md`, `context/manual-opencode.md`
or the shape Areas table:

    python3 context/sync-manuals.py            # write AGENTS.md
    python3 context/sync-manuals.py --check    # exit 1 if it is stale, write nothing

It fails rather than silently overwriting if a source is missing or a
substitution or marker no longer matches.
"""

import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SOURCE = os.path.join(ROOT, "CLAUDE.md")
TARGET = os.path.join(ROOT, "AGENTS.md")
OPENCODE_APPENDIX = os.path.join(ROOT, "context", "manual-opencode.md")
SHAPE_README = os.path.join(ROOT, "context", "shape", "README.md")
SHAPE_MARKER = "{{SHAPE_TRIGGERS}}"

# Every intentional difference between the two runtimes, in order. Anything
# else must not differ: add it here deliberately, with a reason, or fix the
# manual instead.
SUBSTITUTIONS = [
    ("# CLAUDE.md\n", "# AGENTS.md\n"),
    (
        "**`AGENTS.md` is generated from this file** by `context/sync-manuals.py`",
        "**This file is generated from `CLAUDE.md`** by `context/sync-manuals.py`",
    ),
]


def read(path):
    if not os.path.exists(path):
        sys.exit(f"missing {path}")
    return open(path, encoding="utf-8").read()


def shape_triggers():
    """The trigger table for the OpenCode manual, from the Areas table in shape/README.md."""
    text = read(SHAPE_README)
    rows = text[text.index("## Areas"):].split("\n")
    header = next((i for i, line in enumerate(rows) if line.startswith("| Area |")), None)
    if header is None or [c.strip() for c in rows[header].strip("|").split("|")][-1] != "Message mentions":
        sys.exit("context/shape/README.md: the Areas table must end in a 'Message mentions' column")
    out = ["| Message mentions | Read `context/shape/…` |", "|---|---|"]
    for line in rows[header + 2:]:
        if not line.startswith("|"):
            break
        cells = [c.strip() for c in line.strip("|").split("|")]
        if cells[0] == "—":
            continue
        name = re.search(r"\[`([^`]+)`\]", cells[1])
        if not name or not cells[-1]:
            sys.exit(f"context/shape/README.md: area row has no file link or no trigger words: {line!r}")
        out.append(f"| {cells[-1]} | `{name.group(1)}` |")
    if len(out) == 2:
        out.append("| — | _No areas yet. Add a row to the Areas table in `context/shape/README.md`._ |")
    return "\n".join(out)


def build():
    text = read(SOURCE)
    for old, new in SUBSTITUTIONS:
        if old not in text:
            sys.exit(f"substitution no longer matches, fix the script: {old!r}")
        text = text.replace(old, new)
    appendix = read(OPENCODE_APPENDIX)
    if SHAPE_MARKER not in appendix:
        sys.exit(f"{SHAPE_MARKER} missing from {OPENCODE_APPENDIX}")
    appendix = appendix.replace(SHAPE_MARKER, shape_triggers())
    return text.rstrip("\n") + "\n\n" + appendix.rstrip("\n") + "\n"


def main():
    text = build()
    if "--check" in sys.argv[1:]:
        if not os.path.exists(TARGET) or open(TARGET, encoding="utf-8").read() != text:
            sys.exit("AGENTS.md is stale, run python3 context/sync-manuals.py")
        print("ok: AGENTS.md is current")
        return
    open(TARGET, "w", encoding="utf-8").write(text)
    print(f"wrote AGENTS.md from CLAUDE.md ({len(SUBSTITUTIONS)} substitution(s), plus context/manual-opencode.md)")


if __name__ == "__main__":
    main()
