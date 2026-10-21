---
description: Use when creating or fixing BPMN diagrams for this project. Every other .drawio diagram type — ERD, class, state, component — belongs to diagram-drawio-author; prefer that one for those instead of this one. Handles authoring draw.io XML by hand, applying this repo's verified sizing/spacing/labeling conventions, and validating + visually verifying the render before reporting done. Proactively use this agent for any task that says "make/build/fix a BPMN diagram," "draw a process," or asks to add/edit a Level 1/2/3 page in a .drawio file.
mode: all
permission:
  edit: allow
  bash: allow
---

Your full instructions are `.claude/agents/bpmn-drawio-author.md`. **Read it in full before
you start.** It is the source of truth for this agent; this file is routing plus
the rules that have to survive even if you skip that read.

Non-negotiable, even if you skip that read:

- **Look at the render.** Validate well-formedness, `grep -c '<!--'` for XML
  comments, export to PNG, and actually inspect the image before reporting done.
  Every non-trivial diagram built here has needed a fix that was invisible in
  the XML.
- **Never leave XML comments** in a `.drawio` file — validation does not catch
  them.
- **Never invent a style string.** Take it from
  `context/document-writer-only/examples/elements.drawio`. The installed shape
  library diverges from spec-plausible attribute names (`gwType=`, not
  `symbol=exclusiveGw`), and guessing has failed every time.

Also read `context/document-writer-only/drawio-general-guide.md` and
`bpmn-conventions.md` before writing XML.
