# Workbook Conventions

How to read/extend the use case workbook (`docs/workbook.xlsx`).

## Scope boundary

This is a pipeline for **creating** a specification, not maintaining a running system.

**In scope** — a system that doesn't exist yet. Requirements arrive in one of three forms,
and this workbook is the normalizer that turns any of them into engineering-actionable UC
rows. Everything entering is a new capability by definition.

**Out of scope** — defects against delivered behavior, change requests on a live system,
refactors, ops work. These already have an owning UC; there is nothing to derive. They go
straight to `pm/tracker.yaml` without touching this workbook. This is why the `CLAUDE.md`
hard gate ("no issue gets created from a bare request that hasn't been promoted") is not
a contradiction — in a creation pipeline every incoming item *is* a new requirement.

**The one way back in.** During a build, "bug" covers two different things and they route
differently:

- *Code doesn't do what the UC says* → a defect. Ticket it; the pipeline is untouched.
- *The UC was wrong, incomplete, or missing* → a **requirements event**. It re-enters at the
  input layer and produces or amends a UC row.

The second sounds exactly like a bug report on arrival ("users can't complete checkout"
when nobody ever specified guest checkout). If it gets ticketed as a defect, the workbook
silently goes stale and the Entities sheet and ERD drift from what's actually being built.
Classify before ticketing.

Creation ends when the workbook is a complete, confirmed specification. After that it stops
being an intake and becomes the reference baseline, and only the re-entry rule above reopens it.

## The raw input layer (`input/`)

Underneath the three layers below sits the material they were formalized *from*: photos of
whiteboards, scans of the forms the client uses today, exported spreadsheets, screenshots,
email threads, transcripts. It lives in `input/`, one folder per intake event, append-only —
see `input/README.md` for the full intake rule.

It is a layer rather than a folder because it carries a guarantee the rest of the pipeline
depends on: **every downstream artifact can name the thing that justified it.** A UC that says
the hold is ten minutes, a state transition, an entity that exists at all — each should trace to
something a client actually provided. Where it can't, that is worth knowing before the diagram
goes in front of them.

Two rules matter at this boundary:

- **Never derive straight from `input/`.** Raw material is extracted and classified into
  `docs/requests.md` (or a BPMN, or an FR list) first, and the routes below start from *there*.
  Same discipline `requests.md` already has relative to the workbook, one layer earlier.
- **Cite the intake folder, not the file** — `input/2026-08-13-kickoff/` — so a citation survives
  a file being renamed.

Published third-party specs are *not* input; they go to `context/files/`. The split is
provenance: `input/` is what the client gave us, `context/files/` is what a standards body
published.

## The three input layers

Requirements arrive already formalized to different degrees. All three normalize to the same
output — a UC row — but the transformation runs in a different direction for each:

| Layer | What you're handed | Transformation |
|---|---|---|
| BPMN | a confirmed to-be process model | **consolidates** — many tasks → fewer UCs |
| FR | written functional requirements | **decomposes** — one FR → one or more UCs |
| User reqs | raw stakeholder statements (`docs/requests.md`) | **clarifies** — one vague item → 0..n UCs |

FR/NFR is a layer you reason *through*, not one you always materialize. Every user request is
an FR or NFR by theory, but writing that down every time is bookkeeping that doesn't pay for
itself. Materialize an explicit FR only when it earns its keep — the client handed you an
actual FR document, or one FR spawns several UCs and you need the grouping for traceability.

## UC admission test

Every candidate must pass **all five**, regardless of which input layer it came from. This is
the contract that keeps the three routes converging on one quality bar instead of drifting.

1. **Named actor** — a role, not "the system." Fails: "data gets archived nightly" (no actor —
   that's a constraint, see *Where NFRs go*).
2. **Actor-meaningful goal** — the actor is measurably better off afterward. Fails: "generate
   receipt" — nobody sets out to do that on its own.
3. **Observable outcome** — the `Output` column is non-empty. Fails: "validate input" — an
   internal step with no outcome the actor can see.
4. **System involvement** — excludes purely manual work. Fails: "chef cooks the dish" — zero
   system behavior to specify.
5. **One sitting** — one actor, one session, no handoff to another actor mid-flow. Fails:
   "process refund" when finance must approve partway → that's two UCs.

Tests 1–4 are admission checks: they decide whether a candidate is a use case at all, and they
apply on every route. **Test 5 is a granularity anchor, and it applies only where the input has
no structure of its own to anchor on — Routes 2 and 3.** On Route 1 the BPMN already carries the
granularity decision in its User tasks, and that structure wins; see the precedence note there.
Used in its own lane, test 5 kills the classic failure mode that an FR or a raw request invites:
"Manage Orders" fails it (many sittings), while "Click Save" fails test 2 (not a goal).

## Readiness bar

Robustness means refusing bad input rather than guessing at it. Before derivation starts:

- **BPMN** — the diagram must be confirmed to-be, not a draft.
- **FR** — must yield an actor. If you can't name who does it, it's either an NFR or it's
  underspecified. Ask; don't invent one.
- **User req** — must survive one clarifying pass. A vague statement gets a question back,
  never a guessed UC.

## Route 1 — BPMN → UC

Use cases derived from a confirmed to-be BPMN diagram — **not** one row per BPMN task box.

**Derivation rule**: one UC row per **User task** (the actor-facing entry point where a human
looks at a screen and does something) — `User`, `Nama Use Case`, `Modul` all describe that
actor and their goal. Any **Service / Business Rule / Send / Receive** tasks that fire
automatically as a direct consequence of that User task (no actor decision in between) get
folded into the same UC's `Deskripsi` as a numbered sub-flow, not given their own row — e.g. a
customer's single "scan to pay" action triggering generate-bill → send-payment-request →
receive-result → generate-receipt is **one** UC ("Process Payment"), not four. **Manual tasks**
(zero system involvement — cooking, physically walking somewhere) are excluded entirely; they
never become a UC since there's no system behavior to specify.

**Precedence: on this route the User task is the unit of granularity, and admission test 5 does
not override it.** Tests 1–4 still gate each candidate, but a run of consecutive User tasks
performed by the same actor in one session stays as one UC *per task* — it does not collapse
into a single "one sitting" use case. The two rules pull in opposite directions by design: the
User task is a decision the modeller already made about where the actor-facing steps divide, and
re-deciding it from the sequence flow throws that away.

This was gotten wrong once, in the movie-booking example: an earlier pass called this rule "a
specialization of test 5" and let the test win, collapsing three User tasks (Browse Showtimes /
Select Seats from Map / Submit Payment Details) into one UC. Three User tasks means three UCs.
If a User task genuinely shouldn't be its own use case, the fix is to change the BPMN — merge the
tasks there, or demote one to a Service task — not to merge at derivation time, because then the
diagram and the workbook stop agreeing about what the actor does.

Rationale: a use case is a complete, actor-meaningful interaction, not an internal computation
step — "generate a receipt" is never something anyone sets out to do on its own. This was a
real correction made while deriving UCs for `context/document-writer-only/examples/restaurant demo/restaurant-bpmn.drawio` — an initial pass
gave every BPMN task its own row (11 rows) before being consolidated to 5, one per User task.

**`User` comes from the Lane. `Modul` does not.** A Lane is an *actor* — the org unit whose
person performs the task — so it maps cleanly onto the `User` column. It does **not** map onto
`Modul`, which is a grouping of system capability. The two often have similar names, which is
exactly why this gets conflated: on the bolt-manufacturing BPMN the nine Lanes (Penjualan,
Teknik, Perencanaan, Pembelian, Gudang, Produksi, Kualitas, Logistik, Keuangan) were copied
straight into `Modul`, producing an org chart rather than a module architecture. Derive `Modul`
independently, from a recognised ERP module set (SAP's SD/MM/PP/QM/FI or Odoo's equivalents),
and let several Lanes collapse into one module where they share a capability — Teknik +
Perencanaan + Produksi are all Manufacturing; Gudang + Logistik are both Inventory.

Two consequences make this worth getting right rather than fixing later: `Modul` becomes the
**module grouping on the ERD** and the unit for **one class diagram per module**. Too-granular
modules yield a set of two-class diagrams that `class-diagram-conventions.md` explicitly says
shouldn't be drawn at all.

Don't create a module that no UC lands in, even when the source narrative mentions the function
(machine/tooling maintenance was named in the bolt narrative but no User task touched it) — an
empty module is a promise the workbook can't keep.

Lands on sheet **UC BPMN**.

## Route 2 — FR → UC

An FR under-specifies, so this route **expands**. One FR commonly satisfies as several UCs.

Split an FR wherever it crosses an admission-test-5 boundary — a change of actor, or a wait on
someone else. "Clients can self-serve their orders" is one FR and at least three UCs (place,
track, cancel), because each is a separate sitting.

An FR that yields **zero** UCs after the readiness bar is an NFR that was mislabeled — route it
per *Where NFRs go* rather than forcing a row.

When one FR produces multiple UCs, record the FR statement verbatim in each resulting row's
`Deskripsi` preamble so the grouping survives — that's the traceability the explicit FR layer
would otherwise provide.

Lands on sheet **UC Non-BPMN**.

## Route 3 — User req → UC

The rawest input, and the only one where the count can go *down* to zero. A user request is
ambiguous by default; the work is deciding whether it's one UC, several, or nothing at all.

Run the clarifying pass first, then the admission test. Outcomes:

- Passes → promote to a UC row on **UC Non-BPMN**.
- Describes a quality constraint → *Where NFRs go*.
- Duplicates existing behavior → no new row; note the existing `Kode` against the request.
- Fails the bar → stays in `requests.md` with a one-line reason. It never gets a `Kode`.

`docs/requests.md` is append-only capture, never a task queue. Don't work a request directly.

## Closure test

What makes derivation auditable instead of vibes. Run before calling a source "done":

- **BPMN** — every User task maps to exactly one UC; every non-User task is either folded into
  a named UC's sub-flow or explicitly excluded as manual. No orphan tasks.
- **FR** — every FR traces to at least one UC, or is reclassified as an NFR.
- **User req** — every captured request is promoted, deduped against an existing `Kode`, or
  carries a rejection reason.

## Dedup

`Kode` uniqueness prevents duplicate *identifiers*. It does not prevent the same capability
arriving twice from two sources — once in the BPMN, once from the client saying it out loud.

Before assigning a new `Kode`, match on **actor + goal** across both UC sheets, not just on the
code. Same actor pursuing the same goal is the same use case regardless of which route found it.

## Where NFRs go

An item that fails admission test 1 (no nameable actor) is usually a quality constraint, not a
use case. NFRs never decompose into use cases — they attach to a scope and are verified by a
test, not by an actor flow.

Record them against the `Kode` or `Modul` they constrain, and always with a **measurable fit
criterion** (Volere's term) — a number or a testable predicate. "System should be fast" is not
a requirement; "order search returns p95 under 2s at 500 rows" is. An NFR written unmeasurably
is not verifiable and should be sent back for clarification the same way a vague user request is.

A dedicated NFR sheet is not yet defined — deliberately, since the term is jargon that buys a
client nothing and the pipeline's primary output is use cases. Revisit if constraint volume
grows enough that inlining stops working.

## Sheet "Modules"

Columns: `Modul`, `Deskripsi`, `Owner`. One row per business module/domain (Sales, Finance, Planning, etc). This is a reference list for the `Modul` column on both use case sheets — not a hierarchy, just a lookup.

## Sheet "UC BPMN"

Output of Route 1. Columns: `Kode` (UC-xx), `Nama Use Case`, `User` (actor), `Modul`, `Input`, `Deskripsi`, `Output`, `Entity/Objek Terkait` (comma-separated, feeds the Entities sheet — keep spelling/case consistent across rows).

`Kode` is unique across the whole workbook, not just this sheet.

## Sheet "UC Non-BPMN"

Output of Routes 2 and 3. Same columns as "UC BPMN". Holds use cases that didn't originate from the BPMN diagram.

`Kode` prefix `UC-N` (e.g. `UC-N01`) to keep these visually distinct at a glance; still unique workbook-wide, never reuses a `Kode` already used on "UC BPMN".

## Sheet "Entities"

Columns: `Entity yang dibutuhkan`, `Modul`, `Owner`, `ERD` (bool). Deduped list pulled from the `Entity/Objek Terkait` column on both "UC BPMN" and "UC Non-BPMN".

**`Modul` is the single owning module — the one module allowed to write the entity.** Exactly
one, never a list, even though several modules typically read it. Assign it to whichever
module's UC *creates* the record, not to whichever touches it most: `Lot Produk` is referenced
by 15 UCs across three modules but is created by Manufacturing, so Manufacturing owns it.
`Owner` stays the human data steward (a role, e.g. `Staf Kualitas`) — the accountability answer,
not the architectural one; the two columns deliberately answer different questions.

Single ownership is what makes the schema decomposable later. It is not a commitment to
microservices or to any other architecture — it just keeps that option open at no cost, whereas
an entity with two writing modules has to be untangled before any split is possible.

The entities read by the *most* modules are the ones worth watching: they mark where a module
boundary would actually have to be cut, and a count that keeps climbing is the early signal that
a boundary is drawn in the wrong place.

In Google Sheets, kept live via a formula unioning both ranges. `UNIQUE`/`ARRAYFORMULA`/`QUERY`/`FLATTEN` are Google Sheets-only and don't port to `.xlsx` as a live formula — refresh manually or via script if working from the `.xlsx` directly.

`Owner` filled in manually once the list generates. `ERD` marks whether the entity has made it into the ERD yet.

## Out of scope for this workbook

UC-to-code traceability lives in `context/index/map.md`, not in this workbook. This workbook stays scoped to requirements and entities only.

## Editing `docs/workbook.xlsx` programmatically

Use `openpyxl` (no formulas live in this workbook, so a recalculation pass isn't
required, though it's harmless if LibreOffice is available in the environment; it wasn't in
this project's Windows dev setup — `AF_UNIX` missing — so don't assume it'll run).

**`openpyxl`'s `ws.max_row` goes stale across separate save/reload cycles** — appending via
`next_row = ws.max_row + 1` in one script invocation, saving, then doing the same in a later
*separate* invocation can silently overwrite an existing row instead of appending after it. Hit
this three separate times on the same file: it clobbered `UC-N01` on one append; a
"clear rows 2..max_row" refresh of the Entities dedup left stale duplicate rows behind because
`max_row` was also wrong at clear time; and a later cleanup pass found `UC BPMN` still carrying
6 blank trailing rows from an earlier rewrite that never got trimmed, plus the `Entities` sheet's
own header cell (`A1`) silently blanked during one of the rebuild scripts — headers are just as
vulnerable to this as data rows, not only append targets. Mitigation:
- After every write, immediately re-load the file fresh and print the affected sheet(s) to confirm rows landed where expected — never assume a write succeeded from the script's own return value alone.
- When clearing/rebuilding a sheet's data rows (e.g. refreshing the Entities dedup), clear a generously oversized range (rows 2–40, not `2..ws.max_row`) and explicitly `ws.delete_rows()` down to the real last row afterward, so no stale trailing rows survive a shrink.
- Re-write header cells explicitly as the last step of any rebuild script, after all row operations — don't assume a header survives a clear/rebuild cycle untouched.
- See `.claude/agents/workbook-xlsx-author.md` for the agent that applies all of this automatically.
