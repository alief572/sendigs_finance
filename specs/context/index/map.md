# Map — what owns what

Traceability index: a use case or feature, and the artifacts — and later, the
code — that realise it. Agents load this to navigate without re-reading the
whole repository; humans use it for tickets and onboarding. Update it when
ownership changes, not merely because a file exists.

> **Not machine-parsed, by design**: prose pointers, `.md` not `.yaml` on purpose. Do not
> convert it to YAML or build tooling that parses it (why: `context/guide/building-the-layers.md`).

**This file is the prose layer** — ownership, use cases, and the "why" that no
scanner can produce. It does not hold the inventory. `context/index/README.md`
explains the three index files and what each one answers.

---

## Use cases → artifacts

One entry per use case or feature worth tracing, naming where its behaviour is
specified and what implements it. Only the lines that are true get written — a
UC with no diagram simply has no `bpmn:` line.

```
uc: UC-01
  name: {the workbook's own wording, in the client's language}
  bpmn: docs/diagrams/{file}.drawio → page "{Level 2 — ...}"
  entities: [{Entity}, {Entity}]
  state: docs/diagrams/{file}.drawio, when this UC drives a lifecycle
  code: ~
  note: {what a reader would otherwise have to reconstruct — a rule not visible
        in the diagram, an exception, the decision that shaped it}
```

_Empty. Add entries as use cases are confirmed._

## Diagrams → what they own

Which diagram is authoritative for which part of the model, so that two diagrams
never quietly disagree about the same thing.

```
file: docs/diagrams/{file}.drawio
  pages: [{page name}, {page name}]
  owns: {the process / entities / lifecycle this is the source of truth for}
  derived_from: input/{YYYY-MM-DD-slug}/ or docs/workbook.xlsx
  note: {drift risk, known gaps, who confirmed it and when}
```

_Empty._

## Entities → where the rules live

The workbook's Entities sheet is the deduped list. This section is only for
entities carrying a rule that sheet has no column for — a lifecycle, an
invariant, a naming exception.

_Empty._
