# Component Diagram Conventions

Working reference for the single system-level component/communication diagram (`docs/diagrams/component-overview.drawio`) showing how a modular monolith's modules talk to each other. Distilled from OMG UML 2.5.1 (`context/files/formal-17-12-05.pdf`); own words, spec sections cited inline for verification. Not a copy of the spec — read the spec directly for edge cases.

## What a Component is (UML notation)

A Component is a Classifier rectangle with the keyword `«component»`, optionally showing the **component icon** in the top-right corner: two small rectangles protruding from the box's left edge (§11.6.4). If the icon is shown, the `«component»` keyword may be dropped — but keep the keyword anyway for readability at a glance.

A Component is a self-contained, replaceable unit: it hides its internals and exposes only a formal contract of **provided** and **required** interfaces (§11.6.3.1). That's the property this diagram exploits — draw each module as a black box and only show what crosses its boundary.

## Provided vs. required interfaces (lollipop/socket)

- **Provided interface** ("lollipop/ball") — a small open circle attached by a solid line to the component, labeled with the interface name. Represents a service the component offers to others (§10.4.4, §11.6.4).
- **Required interface** ("socket") — a small open half-circle attached by a solid line, representing a service the component needs from its environment (§10.4.4).
- A matching ball and socket from two components can be plugged directly together to show an assembly between them (§11.2.4, §11.6.5) — this is the compact way to show "component A satisfies a dependency of component B" without drawing a separate interface box.
- Lollipop/socket is optional notation for interface-level detail. This project's diagram is coarser than that (see below) — reach for lollipop/socket only if a specific edge needs to call out which named interface/contract is being satisfied, not as the default for every edge.

## Dependency arrows

A Dependency is a dashed line with an open arrowhead, pointing from the client (the element that depends) to the supplier (the element depended on) (§7.7.4). It signifies that a change to the supplier may impact the client (§7.7.1). Between components, an undecorated dependency arrow just means "some further unspecified kind of relationship exists" (§11.6.5) — which is exactly why this project's convention (below) requires every edge to carry an explicit label naming the real mechanism, instead of leaving it as a bare UML dependency.

## This project's convention: one system-level diagram

This is a **single, system-level diagram** — not one diagram per module. Contrast with [`document-writer-only/class-diagram-conventions.md`](../document-writer-only/class-diagram-conventions.md), which is drawn per-module at class granularity (real classes and their dependencies, no fixed architectural pattern assumed). This diagram sits one level up and coarser: each box here is an entire business domain (e.g., Sales, Finance, Planning), not a class.

Rules for drawing it:

- **One box = one real module** of the system (a business domain), not a class, not a controller, not a single endpoint.
- **Every edge must be labeled with the actual communication mechanism** — one of:
  - a direct call within the same monolith process (in-process method/service call),
  - a queue job (async, dispatched and processed later, possibly by a worker),
  - an HTTP call to an external system or integration (crosses a real network boundary).
  Never leave an edge unlabeled — a bare arrow between modules doesn't say enough to be useful here.
- **Visually distinguish in-process calls from boundary-crossing calls.** This is the entire point of the diagram: in a modular monolith, the boundary between two internal modules is a *convention* enforced by code review, not something the OS or network enforces — while a queue job or an external HTTP call crosses a *real* process/network boundary. The diagram must make that difference visible at a glance, not just legible from the label text.
- **Update only when new information surfaces it.** Add an edge when a sequence diagram (or equivalent analysis) reveals a cross-module call that isn't already on the diagram. Not on a fixed schedule, not automatically at every issue close — this diagram tracks confirmed cross-module calls only.

## draw.io shapes

Verified against the installed draw.io Desktop app's bundled shape library (`app.asar`) — not from `search_shapes` (see the note in `document-writer-only/bpmn-conventions.md` for why). Re-verify if draw.io Desktop is ever updated to a version with a different shape library.

| Element | style |
|---|---|
| Component box | `shape=component;align=left;spacingLeft=36;` — draw.io's built-in UML component shape; the two-rectangle icon is baked into the shape, no separate icon element needed |
| Provided interface (lollipop), as an edge on a box | `endArrow=oval;endFill=0;endSize=8;html=1;` |
| Required interface (socket), as an edge on a box | `endArrow=halfCircle;endFill=0;endSize=2;html=1;` |
| Required interface, standalone vertex (socket not attached as an edge) | `shape=requiredInterface;html=1;` |
| Dependency arrow (generic, spec default) | `endArrow=open;endSize=12;dashed=1;html=1;` |

**Communication-mechanism edge styles** — the UML spec has no notion of "queue job" or "HTTP call," so the following is this project's own convention, not spec-derived. Pick from draw.io's standard edge vocabulary and stay consistent:

| Mechanism | Boundary? | style |
|---|---|---|
| Direct call (in-process) | No — same monolith process | `endArrow=block;endFill=1;dashed=0;html=1;` (solid line) |
| Queue job (async, dispatched/processed later) | Yes — crosses a process boundary (worker) | `endArrow=block;endFill=1;dashed=1;dashPattern=8 4;html=1;` (long-dash line) |
| HTTP call (external system/integration) | Yes — crosses a network boundary | `endArrow=open;endSize=12;dashed=1;dashPattern=2 2;html=1;` (short-dash, open arrowhead) |

The solid-vs-dashed split carries the "real boundary vs. organizational boundary" distinction called for above: direct calls stay solid (nothing physically stops the call), while both queue and HTTP edges are dashed (something real sits between the two sides) but use different dash patterns so the two boundary-crossing mechanisms remain distinguishable from each other. Always add the text label (`direct call` / `queue job` / `HTTP call`) on the edge as well — don't rely on line style alone, since it's easy to miscount dash lengths at a glance.

Anything not covered above — open the draw.io desktop app's UML shape panel and inspect directly rather than guessing.

## Worked example (generic, not client-specific)

A `Sales` module creates an order and needs a price from `Planning`, then notifies `Finance` after checkout, and also posts a Sales Order to an external accounting system:

- `Sales` → `Planning`: direct call, solid line, labeled `direct call: getPrice()`. Both modules run in the same monolith process, so nothing enforces this boundary except code review.
- `Sales` → `Finance`: queue job, long-dash line, labeled `queue job: OrderCheckedOut`. `Finance` picks the job up asynchronously from a worker — a real process boundary, even though both are still part of this system.
- `Sales` → external accounting system: HTTP call, short-dash line, labeled `HTTP call: POST /sales-orders`. This is the only edge that leaves the system entirely.

Only `Sales`, `Planning`, `Finance` and the external system appear as boxes; nothing below module granularity is drawn. If `Planning`'s `getPrice()` is worth calling out as a named contract, add a provided-interface lollipop on `Planning` and a matching required-interface socket on `Sales` rather than inventing a fourth edge type.
