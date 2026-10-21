# context/files/

Published third-party reference material the conventions are distilled from — specs, standards,
practitioner guides. The counterpart to `input/`: that layer holds what the **client** gave us,
this one holds what a **standards body or vendor** published.

Referenced today by:

| Document | Cited in |
|---|---|
| OMG BPMN 2.0 — `formal-11-01-03.pdf` | `document-writer-only/bpmn-conventions.md` |
| OMG UML 2.5.1 — `formal-17-12-05.pdf` | `document-writer-only/state-conventions.md` |

Both conventions files cite these inline by section number "for verification". **That only works
if the document is actually here.** A `§10.5.2` pointing at a file nobody can open is not a
citation — it is a claim, and once the person who wrote it moves on, the rule behind it gets
re-argued from memory. This is the same guarantee `input/` exists to provide, applied to
external sources.

## Rules

- **Obtain and place the file, or commit a stub.** Specs are large and often carry
  redistribution restrictions, so they are git-ignored by default (see `.gitignore`). When a spec
  is excluded, commit a `<name>.txt` beside it giving the exact document title, version,
  publisher, and where to obtain it — enough for anyone cloning the repo to fetch the same
  document and check the citation.
- **Cite the version, not just the section.** Section numbers move between spec revisions. A
  citation names the document as filed here (`formal-11-01-03.pdf`, §10.5.2), never a bare
  section number.
- **Read-only.** Nothing in here is edited. Findings extracted from a spec go into the relevant
  `document-writer-only/*.md` conventions file, in our own words, with the citation attached.
