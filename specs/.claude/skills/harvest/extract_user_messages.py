#!/usr/bin/env python3
"""Print the user's own messages from this project's transcripts.

Two sources: Claude Code (`~/.claude/projects/<slug>/*.jsonl`) and opencode
(`~/.local/share/opencode/opencode.db`, sessions whose directory is this
project or below it). Session ids are prefixed `cc:` / `oc:`.

Only what the user typed: no tool output, no system reminders, no slash-command
wrappers, and queued duplicates collapsed. That is the harvest skill's evidence —
shape is the user's word, so nothing an agent said is extracted.

    extract_user_messages.py --since 2026-01-20 --grep 'order|invoice'
    extract_user_messages.py --session 1a2b3c4d
"""
import argparse
import datetime
import glob
import json
import os
import re
import sqlite3
import sys


# Context-compaction summaries arrive as user turns but are written by the agent.
AGENT_WRITTEN = ("This session is being continued from a previous conversation",)


def project_dir() -> str:
    slug = re.sub(r"[^A-Za-z0-9]", "-", os.getcwd())
    return os.path.expanduser(f"~/.claude/projects/{slug}")


def text_of(message) -> str:
    content = message.get("content")
    if isinstance(content, list):
        content = " ".join(
            part.get("text", "")
            for part in content
            if isinstance(part, dict) and part.get("type") == "text"
        )
    return content.strip() if isinstance(content, str) else ""


def claude_code_rows(directory):
    for path in glob.glob(os.path.join(directory, "*.jsonl")):
        session = "cc:" + os.path.basename(path)[:8]
        with open(path, errors="ignore") as fh:
            for line in fh:
                try:
                    entry = json.loads(line)
                except ValueError:
                    continue
                if entry.get("type") != "user" or entry.get("isMeta") or entry.get("isCompactSummary"):
                    continue
                yield entry.get("timestamp", ""), session, text_of(entry.get("message", {}))


def opencode_rows(db, project):
    if not os.path.exists(db):
        return
    conn = sqlite3.connect(f"file:{db}?mode=ro", uri=True)
    query = """
        SELECT m.session_id, m.time_created, p.data
        FROM message m
        JOIN session s ON s.id = m.session_id
        JOIN part p ON p.message_id = m.id
        WHERE (s.directory = ? OR s.directory LIKE ?)
          AND json_extract(m.data, '$.role') = 'user'
        ORDER BY m.time_created, p.time_created
    """
    for session_id, created, data in conn.execute(query, (project, project + "/%")):
        part = json.loads(data)
        if part.get("type") != "text" or part.get("synthetic"):
            continue
        stamp = datetime.datetime.fromtimestamp(created / 1000, datetime.timezone.utc).isoformat()
        yield stamp, "oc:" + session_id[4:12], (part.get("text") or "").strip()


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--since", help="YYYY-MM-DD, inclusive")
    ap.add_argument("--until", help="YYYY-MM-DD, inclusive")
    ap.add_argument("--session", help="transcript id prefix")
    ap.add_argument("--grep", help="case-insensitive regex on the message text")
    ap.add_argument("--dir", default=project_dir(), help="Claude Code transcript directory")
    ap.add_argument(
        "--opencode-db",
        default=os.path.expanduser("~/.local/share/opencode/opencode.db"),
    )
    args = ap.parse_args()

    pattern = re.compile(args.grep, re.I) if args.grep else None
    rows = []
    sources = [claude_code_rows(args.dir), opencode_rows(args.opencode_db, os.getcwd())]
    for source in sources:
        for stamp, session, text in source:
            if args.session and args.session not in session:
                continue
            if not text or text.startswith("<") or text.startswith(AGENT_WRITTEN):
                continue
            day = stamp[:10]
            if args.since and day < args.since:
                continue
            if args.until and day > args.until:
                continue
            if pattern and not pattern.search(text):
                continue
            rows.append((stamp, session, text))

    rows.sort()
    previous = None
    for stamp, session, text in rows:
        if (session, text) == previous:
            continue
        previous = (session, text)
        print(f"[{stamp[:10]} {session}] {text}\n")
    return 0


if __name__ == "__main__":
    sys.exit(main())
