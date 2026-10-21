# ERD Conventions

Entity-relationship diagram conventions, system-level. Drafted as soon as entities are known, refined as real migrations land. **This is the one diagram the project is expected to keep current** — every other diagram type here is optional. There's no single canonical ERD standard (notation varies Chen vs. Crow's Foot) — no spec to extract from, this file is written directly.

## Notation: Crow's Foot

Use Crow's Foot notation — it's what draw.io's ERD shapes default to, and it reads relationship cardinality directly on the connector without a separate legend. Don't mix in Chen-style diamonds for relationships; keep the whole diagram one notation.

Verified against the installed draw.io Desktop's ER shape library (not from `search_shapes` — see the note in `document-writer-only/bpmn-conventions.md` for why):

| Cardinality | connector attribute |
|---|---|
| Exactly one | `ERone` |
| Mandatory one (one and only one) | `ERmandOne` |
| Zero or one | `ERzeroToOne` |
| Many | `ERmany` |
| One or many | `ERoneToMany` |
| Zero or many | `ERzeroToMany` |

Set as `startArrow=<value>` / `endArrow=<value>` on the connecting edge — one end of the relationship gets the source cardinality, the other end the target cardinality. Entity boxes are `shape=table;childLayout=tableLayout;container=1;` with one row per attribute — draw.io's native ER table shape, not a plain rectangle with a text list.

## Keep the model ORM-shaped

The model should mirror what the ORM can express cleanly — one-to-many, many-to-many, and simple one-to-one relationships — not aspirational modeling that fights the ORM once real code is written. If a relationship needs something an ORM handles awkwardly (a relation with its own attributes, a genuinely polymorphic relation, a self-referencing hierarchy), flag it explicitly on the diagram rather than drawing it as if it were a plain relationship — the awkwardness should be visible at design time, not discovered at migration time.

**Draw a many-to-many directly only when the relationship itself carries no independent meaning.** If the M:N has a real business name (a recipe/BOM, an enrollment, an assignment), give it an explicit junction table instead — e.g. `Menu_Item ↔ Ingredient` should be `Menu_Item —< Recipe_List >— Ingredient`, not a bare M:N edge, because "which ingredients a dish needs" is a concept the client would recognize and name, not an ORM implementation detail. A direct M:N edge is still fine for genuinely meaningless pivots (tags on a post, for instance).

**A table that represents a staff-performed action needs a `Staff_Id` FK** — if a UC's actor is a named staff role (Cook, Server, Kitchen Staff, Cashier) and the action produces or updates a row, that row should record who did it. Prefer attaching the FK to the **natural process record that already exists** (e.g. `Order_Detail.Cook_Id` / `Order_Detail.Server_Id`) over inventing a wrapper entity whose only job is to hold the attribution (an earlier pass on the restaurant demo added a `Kitchen_Ticket` table for exactly this and then had to remove it once `Order_Detail` was recognized as the real process record — a separate "ticket" entity added nothing once staff could be attributed directly on the line item).

**An inventory/stock entity should default to being a transaction ledger, not a snapshot**, unless told otherwise — a "kartu stock" (stock card) pattern: one row per movement, a `Type` discriminator (Input / Output / Adjustment), and a nullable FK to whatever triggered the movement (e.g. `Stock_Card.Order_Detail_Id` for Output-type rows consumed by an order). A single mutable "current quantity" column looks simpler but throws away the audit trail and the "why did this change" question a real inventory feature always ends up needing.

## Naming stays code-legal

Table names and column names on this diagram follow whatever `context/coder-only/*.md` already commits to for this project (key strategy, enums versus strings, soft-delete columns, naming case, etc.) — this diagram should never show a design that those conventions would then forbid once it is turned into a real migration. Before a stack is chosen there is nothing to comply with yet, so record the assumptions the ERD is making and reconcile once it is. If the ERD and an implementation convention disagree, fix the ERD; the implementation conventions are the constraint, not a suggestion to negotiate around at diagram time.

## Grouping entities by Module

Visually cluster entities by their owning workbook `Modul` (`document-writer-only/workbook-conventions.md`'s Modules sheet) — makes a growing ERD legible at a glance and keeps it traceable back to the workbook. Draw each module as a plain dashed rectangle (`rounded=0;whiteSpace=wrap;html=1;dashed=1;fillColor=none;strokeColor=#666666;verticalAlign=top;align=left;spacingLeft=8;spacingTop=6;fontStyle=1;fontColor=#666666;`, module name as `value=`) sized to enclose that module's tables, placed **before** the tables in document order so it renders behind them (z-order follows document order in draw.io — later cells draw on top). This is a background rectangle, not a true parent container — entity tables keep absolute coordinates rather than becoming children of the group, which avoids the Lane-relative-coordinate arithmetic that BPMN Pools/Lanes require (see `bpmn-conventions.md`'s coordinate-space warning). Relationships crossing module boundaries are normal and fine — an ERD tolerates more line crossings than a BPMN diagram; route them through the gap between group boxes, not through another group's tables.

**Tolerating a crossing is not the same as leaving it ambiguous.** Because module grouping forces cross-group relationships through shared gaps, this is the one diagram type where crossings can't be designed away, so they have to be made *readable* instead: give each long run its own corridor (explicit waypoints, ~20–40px apart, one horizontal band and one vertical lane per connector) and add `jumpStyle=arc;` to the connectors that cross others, so an intersection renders as a visible hop rather than a plain right-angle junction that reads like a join. Declare the jump on one consistent side of each crossing pair only — see the two line-jump/corridor entries in `drawio-general-guide.md` for the full mechanic and the failure modes. A first-pass ERD that routes cross-group connectors with the default router will pass validation and still be misread.

## PK/FK-only draft pass

For a new or fast-growing ERD, it's fine to draft with **only the PK row and any FK rows** per table (name, no other business columns) before deciding real attributes — this validates the relationship/cardinality model cheaply and matches the workbook's Entities sheet, which also doesn't carry attribute-level detail yet. Add business columns once they're actually known; don't block the relationship diagram on knowing every field first.

## Before hand-building a large one, ask who will open it

Size is a **rot mechanism**, not merely an inconvenience. A whole-schema drawing
has no regular reader, so it has nobody to catch it when it drifts — and it
drifts fastest exactly when the project is moving. In the project this framework
came from, a hand-built ERD carrying 58 entities against a 66-table schema went
untouched for a month and nobody noticed, because opening it was never part of
anyone's work.

Generating it is the fix, but only under two conditions: **the generator can
refuse, and somebody runs it.** A generator that emits a partial diagram when it
meets something it does not understand produces a drawing that is quietly wrong,
which is worse than one that is obviously old. Make it exit non-zero on an
unassigned entity or an uncategorised reference, take counts from the source of
truth rather than counting them off the diagram, and regenerate it as part of
the change that moved the schema — the same contract the code index has in
`context/index/README.md`.

If generation is not on the table, **draw per-module slices small enough to be
read** — those get opened, so those get corrected. The same reasoning bounds the
class diagram (`class-diagram-conventions.md` already caps it per module) and is
why an inventory of hundreds of items belongs in a generated list to grep, never
in a picture.

## Lifecycle

- **Drafted early** from the workbook's Entities sheet (`document-writer-only/workbook-conventions.md`) plus whatever use cases are confirmed so far — the ERD doesn't wait for every use case to be locked, it grows alongside them.
- **Refined at issue close time** once real migrations exist for the entities that issue touched — the diagram should converge toward "matches the actual schema," not stay a Phase-1 snapshot forever. Same update trigger as `class-diagram-conventions.md`.
- If the workbook's Entities sheet and the ERD disagree about whether an entity exists, the workbook is the intake list (has this been identified as needed) and the ERD is the design (how it actually relates to everything else) — reconcile rather than letting them drift silently.
