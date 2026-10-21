# Class Diagram Conventions

How to draw the class diagram for a module. This is an **architecture diagram, not a UML domain model** — it shows the real classes that exist (or will exist) in a module's code and how they depend on each other, not conceptual domain entities with attributes/multiplicities.

## No fixed architectural pattern

This diagram doesn't assume a specific architecture — not a layered Model/Service/UI chain, not BCE (Boundary-Control-Entity), not hexagonal/ports-and-adapters, not anything else. Whatever the module's classes and their dependencies actually turn out to be once the stack and architecture are chosen, that's what goes on the diagram. Don't force a module's real structure into a pattern this file doesn't mandate — the point of the diagram is "these are the actual classes and what they call," not "here's how well the module conforms to architecture X."

**This is a default, not a ban.** The warning above exists to stop *unprompted* defaulting to BCE/EBC or any other named pattern — if the user explicitly asks for a specific architecture (confirmed on the restaurant demo: "use a simple entity boundary controller"), use it. See [Entity-Boundary-Controller, when chosen](#entity-boundary-controller-when-chosen) below for how to structure it.

Once this project's stack/architecture is settled, record the *actual* recurring shapes (a service layer, a repository, whatever the real codebase does) in `context/coder-only/` if a stable pattern emerges — this file stays about the diagramming convention, not about prescribing an architecture.

## Entity-Boundary-Controller, when chosen

If EBC/BCE is the confirmed architecture, structure the diagram as three left-to-right bands —
Boundary, Control, Entity — with a dependency arrow only ever pointing rightward (Boundary →
Control → Entity), never entity-to-control or control-to-boundary. Verified on the restaurant
demo (`context/document-writer-only/examples/restaurant demo/restaurant-class-order-management.drawio`,
`context/document-writer-only/examples/restaurant demo/restaurant-class-full.drawio`):

- **Stereotype label**: prefix the class name with `«boundary»` / `«control»` / `«entity»` on its
  own line above the name, inside the same box — not a separate shape. In XML, the angle quotes
  are `&#171;`/`&#187;` (numeric refs, not `&laquo;`/`&raquo;` — those are HTML entities and don't
  reliably decode inside an XML attribute) and the line break is `&lt;br&gt;` (an escaped `<br>`,
  since a raw `<`/`>` isn't legal inside an XML attribute value but `html=1` on the style renders
  the decoded `<br>` as an actual break): `value="&#171;control&#187;&lt;br&gt;PlaceOrderController"`.
- **Layer color** (this project's convention, draw.io's own standard preset triad — good contrast,
  immediately scannable): Boundary `fillColor=#dae8fc;strokeColor=#6c8ebf;` (blue), Control
  `fillColor=#ffe6cc;strokeColor=#d79b00;` (orange), Entity `fillColor=#d5e8d4;strokeColor=#82b366;`
  (green). Apply uniformly — every class in a layer gets that layer's colors, no exceptions for
  emphasis.
- **Layer grouping**: same dashed-background-rectangle technique as the ERD's module grouping (see
  `erd-conventions.md` and `drawio-general-guide.md`) — one dashed box per layer, labeled
  "Boundary"/"Control"/"Entity", added before the class boxes in document order so it renders
  behind them.
- **Fan-out routing**: when one Control class depends on several Entity classes (very common — a
  controller orchestrating one use case often touches most of a module's entities), have every
  outgoing edge exit the Control box from the **same point** (`exitX=1;exitY=0.5` on all of them).
  Lines from a single shared point can't cross each other by construction; varying the exit point
  per edge to "spread them out" is what actually causes ugly crossings when the targets are at
  different heights. Confirmed by hitting this exact bug and fixing it mid-session.
- **A combined diagram spanning several modules is legitimately harder to read than one diagram
  per module** — confirmed on the restaurant demo: a single EBC diagram for all 9 use cases (8
  boundaries, 9 controllers, 10 entities) needed dense, heavily-crossing dependency lines once
  `Order`/`OrderDetail` were shared by 5+ controllers, while the single-module version stayed
  clean. This is direct evidence for — not an exception to — the "one diagram per module" rule
  below. A combined version is still fine to produce **in addition to** the per-module diagrams
  when the user wants the whole system at a glance (mirrors the BPMN "rollup diagram" pattern in
  `bpmn-conventions.md`) — just don't let it replace the per-module ones as the default
  deliverable.

## Drawing rules

- **One box = one real class**, named exactly as it appears in code — `SalesOrderService`, not "Sales Order Service" or "Sales Order Service Class". If the class doesn't exist yet, don't put it on the diagram; draft it in the plan first.
- **Arrows show dependency direction only** — "calls" / "depends on", drawn as a plain open-arrowhead line. This is not a UML association/composition/aggregation diagram — don't add multiplicities, roles, or composition diamonds; none of that applies to a "what calls what" architecture map.
- **One diagram per module**, not one global diagram — a module's classes stay legible at module scope; a system-wide version of this diagram would just be `guide/component-conventions.md`'s job at a coarser grain.
- **Small modules can stay undocumented** until they grow enough classes that the dependency structure isn't obvious at a glance. Don't draft a diagram for a two-class module just for completeness.
- **Update at issue close time** when a new class is added to a module — same trigger as the ERD (see `document-writer-only/erd-conventions.md`), not a separate scheduled review.
- **When an entity is renamed or removed in the ERD, check every class diagram that references it.** Class diagrams that name entity classes drift silently otherwise — hit this for real on the restaurant demo, where the ERD renamed `Stock`→`Stock Card` and removed `Kitchen_Ticket` but the already-built Order Management class diagram kept the old names until caught during an unrelated visual-polish pass. The ERD is the design of record for entity shape (per its own lifecycle note); anything downstream that names an entity is a consumer of that name, not an independent source.

## draw.io shapes

Verified against the installed draw.io Desktop's UML shape library (not from `search_shapes` — see the note in `document-writer-only/bpmn-conventions.md` for why):

| Element | style |
|---|---|
| Class box (plain, no attribute/method compartments — this diagram doesn't need them) | `rounded=0;whiteSpace=wrap;html=1;` |
| Dependency arrow ("calls"/"depends on") | `endArrow=open;endSize=12;html=1;` (drop the spec's `dashed=1` — a solid line reads better for "this is the real call chain," not a UML dependency's usual dashed/transient meaning) |

If a module's diagram ever needs to distinguish a strong compile-time dependency from a soft/late-bound one, use dashed vs solid rather than introducing new box shapes — keep the vocabulary small.
