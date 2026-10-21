#!/usr/bin/env python3
"""One guard for every agent: deny tool calls that break CLAUDE.md's "Never" rules.

Each agent hands it the call as JSON on stdin, in its own shape:
  {"tool_name": ..., "tool_input": {...}}
Exit 2 with a reason on stderr denies the call; exit 0 allows it.

Wired into:
  Claude Code  .claude/settings.json, PreToolUse
  OpenCode     .opencode/plugin/repo-guard.ts, tool.execute.before

Two strengths. Every agent: no writes to input/, .git/ or the generated AGENTS.md,
no .env, no `git add -A`, no deleting context/gotchas.md, plus PROJECT_RULES. The
executor also may not stage, commit, push, stash, discard work or make a branch: the
middle layer does those, when the user asks. A call is an executor's when Claude Code
reports it from inside an EXECUTOR_AGENTS subagent (hook input `agent_id` /
`agent_type`) or when the caller passes --executor. OpenCode does not tell the hook
which agent is calling, so it gets the base strength only.

This is string matching on paths and commands, not a shell parser. It stops the honest
mistake, not a determined workaround (a script that writes to input/), and it errs
toward denying: a command whose text merely contains `.env` is blocked. The protected
folders are the repo's own: a path is checked relative to the repo root, so an app folder
that only shares the name (src/components/input/) is not input/.

Self-check: python3 guard.py --selftest
"""

import json
import os
import re
import sys

# The repo root: this file is .agents/plugins/repo-guard/hooks/guard.py, four levels down.
ROOT = os.path.realpath(os.path.join(os.path.dirname(os.path.abspath(__file__)), *[".."] * 4))
# Subagents that execute a plan and never commit; the caller reviews and commits.
EXECUTOR_AGENTS = {"coder"}
# Paths no agent writes (CLAUDE.md "Never"; generated manuals), relative to ROOT.
PROTECTED_PATH = re.compile(r"^(input/|\.git/|AGENTS\.md$)")
# The repo's input/ in a command: `input/`, `./input/` or under ROOT, never `src/input/`.
INPUT_DIR = r"(?<![\w.\-/])(?:\./|" + re.escape(ROOT) + r"/)?input/"
ENV_FILE = re.compile(r"(^|/)\.env$")
# Tool names that write a file, across Claude Code and OpenCode.
WRITER = re.compile(r"write|edit|patch", re.IGNORECASE)
PATCH_FILE = re.compile(r"^\*\*\* (?:Add|Update|Delete) File: (\S+)", re.MULTILINE)

# `git` plus any options before the subcommand (`git -C dir push`, `git --no-pager push`).
GIT = r"\bgit(?:\s+-[cC]\s+\S+|\s+--\S+)*\s+"

ALWAYS = [
    (re.compile(r"\.env(?!\.example)\b"), ".env holds secrets"),
    (re.compile(r"(>>?|\btee\b|\brm\b|\bmv\b|\bcp\b|\btouch\b|\bmkdir\b|sed\s+-i)[^;&|\n]*" + INPUT_DIR),
     "input/ is append-only client material"),
    (re.compile(GIT + r"add\s+(-A|--all|\.)(\s|$)"), "stage by path, never git add -A or ."),
    (re.compile(r"(\brm\b|\bmv\b|\bgit\s+rm\b)[^;&|\n]*context/gotchas\.md"),
     "context/gotchas.md is emptied by the distill pass, never deleted"),
]
EXECUTOR_ONLY = [
    (re.compile(GIT + r"push\b"), "git push is for the user"),
    (re.compile(GIT + r"(add|commit|stash|restore|rebase|reset\s+--hard|clean)\b"),
     "the middle layer stages, commits and discards work, not the executor"),
    (re.compile(GIT + r"checkout\s+(--|\.(\s|$)|-[bB]\b)|" + GIT + r"switch\s+-[cC]\b"),
     "discards uncommitted work or makes a branch"),
]
# Rules for commands that are dangerous in THIS project's stack: (compiled pattern, reason).
# Fill in once the stack is chosen, matching the exact commands in CLAUDE.md's "Never"
# section. For a Laravel app whose tests run in a container, for example:
#   (re.compile(r"\b(php\s+artisan\s+test|vendor/bin/(pest|phpunit))\b"), "run tests in the container"),
#   (re.compile(r"\b(migrate:fresh|migrate:reset|db:wipe)\b"), "wipes a database"),
PROJECT_RULES = []


def paths(args):
    found = [args.get(k) for k in ("path", "file_path", "filePath", "notebook_path")]
    found += PATCH_FILE.findall(args.get("patchText") or args.get("patch") or "")
    return [p for p in found if isinstance(p, str)]


def repo_relative(path):
    """*path* relative to ROOT, or None when it lies outside the repo. A relative path is
    taken from ROOT, which is where the agents run."""
    rel = os.path.relpath(os.path.realpath(os.path.join(ROOT, path)), ROOT)
    return None if rel == ".." or rel.startswith(".." + os.sep) else rel.replace(os.sep, "/")


def verdict(call, executor=False):
    """Reason to deny *call*, or None to allow it."""
    tool = call.get("tool_name") or ""
    args = call.get("tool_input") or {}
    executor = executor or call.get("agent_type") in EXECUTOR_AGENTS
    for path in paths(args):
        if ENV_FILE.search(path):
            return f"{path} holds secrets (CLAUDE.md)"
        rel = repo_relative(path)
        if WRITER.search(tool) and rel and PROTECTED_PATH.search(rel):
            return f"{path} is protected (CLAUDE.md, Never)"
    command = args.get("command")
    if not isinstance(command, str):
        return None
    for pattern, reason in ALWAYS + PROJECT_RULES + (EXECUTOR_ONLY if executor else []):
        if pattern.search(command):
            return reason
    return None


def selftest():
    def call(tool, **tool_input):
        agent = tool_input.pop("agent_type", None)
        return {"tool_name": tool, "tool_input": tool_input, **({"agent_type": agent} if agent else {})}

    deny = [
        (call("write", path="input/2026-10-01-x/notes.md"), False),
        (call("Edit", file_path=os.path.join(ROOT, "AGENTS.md")), False),
        (call("Write", file_path=os.path.join(ROOT, "input/2026-10-01-x/notes.md")), False),
        (call("Read", file_path="/repo/app/.env"), False),
        (call("apply_patch", patchText="*** Update File: input/a.md\n"), False),
        (call("Bash", command="echo hi > input/x.txt"), False),
        (call("Bash", command="rm -rf ./input/old"), False),
        (call("Bash", command=f"mv x {ROOT}/input/"), False),
        (call("Bash", command="cat app/.env"), False),
        (call("Bash", command="git add -A"), False),
        (call("Bash", command="git add ."), False),
        (call("Bash", command="rm context/gotchas.md"), False),
        (call("shell", command="git commit -am x"), True),
        (call("shell", command="git add src/a.py"), True),
        (call("shell", command="git push origin main"), True),
        (call("shell", command="git -C . push origin main"), True),
        (call("shell", command="git stash"), True),
        (call("shell", command="git checkout -b scratch"), True),
        (call("shell", command="git checkout -- ."), True),
        (call("Bash", command="git commit -m x", agent_type="coder"), False),
        (call("Bash", command="git push", agent_type="coder"), False),
    ]
    allow = [
        (call("write", path="docs/requests.md"), True),
        (call("Read", file_path="input/2026-10-01-x/notes.md"), True),
        (call("Edit", file_path="context/gotchas.md"), False),
        (call("Grep", path="input"), True),
        # an app folder that only shares the name is not the repo's input/
        (call("Write", file_path=os.path.join(ROOT, "src/components/input/Text.tsx")), False),
        (call("write", path="src/components/input/Text.tsx"), False),
        (call("Write", file_path="/tmp/input/x.txt"), False),
        (call("Bash", command="cp t.tsx src/components/input/Text.tsx"), False),
        (call("Bash", command="mkdir -p app/Forms/input/"), False),
        (call("Bash", command="grep -rn foo input/"), True),
        (call("Bash", command="cp .env.example .env.example.bak"), False),
        (call("Bash", command="git push origin main"), False),
        (call("Bash", command="git add src/a.py && git commit -m x"), False),
        (call("Bash", command="git commit -m x", agent_type="distill"), False),
        (call("Bash", command="git diff --stat", agent_type="coder"), False),
        (call("shell", command="git log --grep=push --oneline"), True),
        (call("Bash", command="echo hi > /tmp/a\ncat input/notes.md"), False),
    ]
    for c, executor in deny:
        assert verdict(c, executor), f"should deny: {c}"
    for c, executor in allow:
        assert verdict(c, executor) is None, f"should allow: {c} ({verdict(c, executor)})"
    print(f"ok: {len(deny)} denied, {len(allow)} allowed")


def main():
    args = sys.argv[1:]
    if args == ["--selftest"]:
        return selftest()
    try:
        c = json.loads(sys.stdin.read() or "{}")
    except json.JSONDecodeError:
        return 0  # not a tool call we understand; the agent's own approval still applies
    reason = verdict(c, executor="--executor" in args)
    if reason:
        print(f"blocked by repo-guard: {reason}", file=sys.stderr)
        sys.exit(2)
    return 0


if __name__ == "__main__":
    main()
