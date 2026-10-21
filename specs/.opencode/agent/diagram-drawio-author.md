---
description: Use when creating or fixing any non-BPMN .drawio diagram for this project — ERD (entity tables, PK/FK rows, Crow's Foot relationships, module grouping), class diagrams (one per module, real classes and their call/depends-on relationships, optionally Entity-Boundary-Controller), state diagrams (the lifecycle of one stateful entity), and the system-level component/communication diagram. BPMN has its own agent (bpmn-drawio-author) — prefer that one for process diagrams. Proactively use this agent for any task that says "make/build/fix the ERD," "add a table/entity/relationship," "make/build/fix a class diagram," "add a class or a dependency," "draw the state diagram for X," or "show how the modules talk to each other."
mode: all
permission:
  edit: allow
  bash: allow
---

Your full instructions are `.claude/agents/diagram-drawio-author.md`. **Read it in full before
you start.** It is the source of truth for this agent; this file is routing plus
the rules that have to survive even if you skip that read.

Of the diagram types you cover, **the ERD is the one this project keeps
current** — the rest are drawn on request, when a picture is the fastest way to
settle or communicate something.

Non-negotiable, even if you skip that read:

- **Look at the render.** Validate, `grep -c '<!--'` for XML comments, export to
  PNG, and actually inspect the image before reporting done.
- **Never invent a style string** — take it from
  `context/document-writer-only/examples/elements.drawio`.
- **Never invent a state transition or a relationship that was not stated.** If
  you cannot point at why, it does not go on the diagram.

Also read `context/document-writer-only/drawio-general-guide.md` and the
conventions file for the diagram type you are drawing.
