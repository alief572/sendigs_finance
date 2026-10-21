#!/usr/bin/env python3
"""Cheap consistency checks for the context layer. Run after touching `context/`:

    python3 context/lint.py

Errors (exit 1): AGENTS.md is stale, a retired name is still used, a
backticked repo path does not exist, the decisions index and `decisions/` disagree.
Warnings (exit 0): the gotchas buffer is overdue or malformed, a context file has no
inbound reference, the commit-msg hook is not installed.

It checks that the layer agrees with itself, not that what it says is true.
"""

import datetime
import glob
import os
import re
import subprocess
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)

# Names that were renamed; an old spelling left in a live file is drift. Lines that
# say "renamed" are history and pass. Add a pair when you rename an agent, file or role.
RETIRED = {}  # e.g. {"old-agent-name": "new-agent-name"}
# Paths the docs deliberately name before they exist: the generated inventory, and the
# stack-dependent coder-only files the template tells you to write once a stack is chosen.
# Remove an entry once the file exists.
NOT_YET = {"context/index/inventory.md", "context/coder-only/stack.md",
           "context/coder-only/testing.md", "context/coder-only/project-exceptions.md"}
# Convention and framework docs that use illustrative file names; their paths are not checked.
EXAMPLES = ("context/document-writer-only/", "context/guide/component-conventions.md",
            "context/guide/building-the-layers.md", "context/guide/cross-model-review.md")
# Where a backticked path may be relative to: the repo root, or an app folder.
APPS = [""]  # add "<app>/" entries if the repo holds several apps
PATH_ROOTS = ("context/", "docs/", "pm/", "input/", ".claude/", ".opencode/", ".agents/", ".githooks/")
BUFFER_ENTRIES, BUFFER_DAYS = 12, 30

errors, warnings = [], []


def read(path):
    return open(path, encoding="utf-8").read()


def live_files():
    """The files agents load or follow: manuals, context/, agent and recipe definitions."""
    files = ["CLAUDE.md", "README.md"]
    for pattern in ("context/**/*.md", ".claude/agents/*.md", ".opencode/agent/*.md"):
        files += glob.glob(pattern, recursive=True)
    return sorted(f for f in set(files) if "/examples/" not in f)


FILES = live_files()
TEXT = {f: read(f) for f in FILES}

# 1. The generated manual (AGENTS.md) is current.
run = subprocess.run([sys.executable, "context/sync-manuals.py", "--check"], capture_output=True, text=True)
if run.returncode:
    errors.append((run.stderr or run.stdout).strip())

# 2. Retired names.
for f, text in TEXT.items():
    if f == "context/lint.py":
        continue
    for n, line in enumerate(text.split("\n"), 1):
        for old, new in RETIRED.items():
            if old in line and "renamed" not in line:
                errors.append(f"{f}:{n}: `{old}` was renamed to `{new}`")

# 3. Backticked repo paths resolve.
TOKEN = re.compile(r"`([A-Za-z0-9_.\-/]+)`")
for f, text in TEXT.items():
    if f.startswith(EXAMPLES):
        continue
    for token in TOKEN.findall(text):
        if not token.startswith(PATH_ROOTS) or token in NOT_YET:
            continue
        # a spec that cannot be committed leaves a `<name>.txt` stub (context/files/README.md)
        if not any(os.path.exists(a + token) or os.path.exists(a + token + ".txt") for a in APPS):
            errors.append(f"{f}: `{token}` does not exist")

# 4. Decisions index and decision files agree.
rows = set(re.findall(r"^\| (\d+) \|", read("context/index/decisions.md"), re.M))
bodies = {m.group(1) for p in glob.glob("context/index/decisions/*.md")
          if (m := re.match(r"(\d+)-", os.path.basename(p)))}
for n in sorted(rows - bodies):
    errors.append(f"decisions.md has row {n} but no decisions/{n}-*.md")
for n in sorted(bodies - rows):
    errors.append(f"decisions/{n}-*.md has no row in decisions.md")

# 5. Gotchas buffer.
gotchas = read("context/gotchas.md")
head, _, entries = gotchas.partition("\n## Entries")
types = set(re.findall(r"[a-z]+", re.search(r"\*\*type:\*\* ([a-z |]+)\n", head).group(1)))
blocks = [b for b in re.split(r"\n## ", entries)[1:]]
if blocks and "_Empty. Append below._" in entries:
    warnings.append("gotchas.md: the 'Empty' placeholder is still there above entries")
dates = []
for b in blocks:
    title = b.split("\n")[0]
    kind = re.search(r"\*\*type:\*\* (\S+)", b)
    date = re.search(r"\*\*date:\*\* (\d{4}-\d{2}-\d{2})", b)
    if not kind or kind.group(1) not in types:
        warnings.append(f"gotchas.md: '{title}' has type {kind.group(1) if kind else 'none'}, not one of {sorted(types)}")
    if date:
        dates.append(datetime.date.fromisoformat(date.group(1)))
    else:
        warnings.append(f"gotchas.md: '{title}' has no date")
    if kind and kind.group(1) != "shape" and "**Why:**" not in b:
        warnings.append(f"gotchas.md: '{title}' has no Why, so it cannot be promoted")
age = (datetime.date.today() - min(dates)).days if dates else 0
if len(blocks) > BUFFER_ENTRIES or age > BUFFER_DAYS:
    warnings.append(f"gotchas.md: {len(blocks)} entries, oldest {age} days: a distill pass is due")

# 6. Context files nothing points at.
for f in FILES:
    if not f.startswith("context/") or f.endswith(("/README.md", "/RULES.md")) or "/decisions/" in f:
        continue
    name = os.path.basename(f)
    if not any(name in text for other, text in TEXT.items() if other != f):
        warnings.append(f"{f}: no other live file mentions it")

# 7. Every skill under .claude/skills is credited in THIRD_PARTY.md (or listed as written here).
third = read("THIRD_PARTY.md") if os.path.exists("THIRD_PARTY.md") else ""
for d in sorted(glob.glob(".claude/skills/*/")):
    name = os.path.basename(d.rstrip("/"))
    if f"`{name}`" not in third:
        warnings.append(f".claude/skills/{name}: not in THIRD_PARTY.md (credit it, or list it as written for this repo)")

# 8. The commit-msg hook is installed in this clone.
hooks = subprocess.run(["git", "config", "--get", "core.hooksPath"], capture_output=True, text=True).stdout.strip()
if hooks != ".githooks":
    warnings.append("commit-msg hook not installed: git config core.hooksPath .githooks")

for e in errors:
    print("error:  ", e)
for w in warnings:
    print("warning:", w)
print(f"{len(errors)} error(s), {len(warnings)} warning(s)")
sys.exit(1 if errors else 0)
