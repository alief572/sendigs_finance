# State Diagram Conventions

Working reference for hand-drafting the state diagram of a stateful business entity (an order, an invoice, a stock movement — generic examples, not tied to any specific client). Distilled from OMG UML 2.5.1 (`context/files/formal-17-12-05.pdf`), Clause 14 "StateMachines"; own words, spec sections cited inline for verification. Not a copy of the spec — read the spec directly for edge cases.

## What this diagram is, and isn't

**Grain: one diagram per stateful entity**, not per module and not system-wide. Contrast with the other two diagram conventions in this project:

- [`class-diagram-conventions.md`](class-diagram-conventions.md) — one diagram per module, at class granularity (real classes and their dependencies, no fixed architectural pattern assumed).
- [`guide/component-conventions.md`](../guide/component-conventions.md) — one diagram, system-wide, at module granularity.

A state diagram sits below both: it belongs to a single entity's lifecycle (an order's status, an invoice's status, a stock movement's status), independent of which module or class implements it. A module with three stateful entities gets three state diagrams, not one.

This project only draws **behavior state machines** (UML's term for "an ordinary state diagram describing how one thing's lifecycle unfolds") — not protocol state machines (§14.4, which specify legal call sequences on an operation's interface, not business status). Out of scope.

## Notation

### States

A simple state is a rounded rectangle containing its name (§14.2.4.4). "Simple" means it has no internal states of its own — the normal case for a business status like `draft`, `submitted`, `approved`, `cancelled`.

### Initial and final

- **Initial pseudostate** — a small solid filled circle, with one outgoing transition into the entity's first real status (§14.2.4.6). Marks where the lifecycle begins; not itself a status value.
- **Final state** — a circle surrounding a smaller solid filled circle, i.e. a filled dot with a ring around it (§14.2.4.5). Marks that the lifecycle has run to completion — nothing more can happen to this instance. Not every entity needs one: some statuses (e.g. an order sitting at "delivered") are simply where the entity stays forever without a formal "closed" concept. Only add a final state when the client actually describes an end-of-life event (archived, closed, voided) that finishes the entity's story.

### Transitions

A transition is an arrow from a source state to a target state, optionally labeled `event [guard] / effect` (§14.2.4.8, using the spec's textual notation for Transitions). In plain terms:

- **event** — what causes the move: a user action ("user clicks Approve"), a system trigger ("payment webhook received"), a schedule ("30 days after due date"). Maps loosely to a UC or a BPMN task/event already documented elsewhere.
- **guard** — a condition that must also hold for the transition to fire, written in brackets, e.g. `[stock available]` or `[total > 0]`. Optional — most business transitions have none.
- **effect** — a side effect the transition performs as it fires, written after a slash, e.g. `/ notify warehouse`. Also optional.

None of the three parts is mandatory on every arrow (§14.2.3.8 confirms a Transition "may have an associated effect Behavior," not must, and a transition without a guard "is treated as if it has a guard that is always true"). An unconditional transition with a bare event and no guard or effect — just `event` as the label, or even unlabeled if the source state has only one way out — is completely normal and should not be padded with an invented guard/effect just to look complete.

## Composite (nested) states: not used here

The spec devotes a large part of Clause 14 to composite states — states that contain their own regions, sub-vertices, entry/exit/do behaviors, history pseudostates, and so on (§14.2.3.4.1–14.2.3.4.7). Reading through that material to actually weigh it: composite states exist to model a state that has interesting *internal* behavior of its own — e.g. a "Studying" state that internally cycles through Lab1 → Lab2, or a phone's "Active" state that plays a ringing tone while active (§14.2.5, Figure 14.36). The nesting buys you shared entry/exit behavior and reusable sub-flows across an otherwise complex internal process.

**Conclusion: flat states are sufficient for SME business-entity lifecycles, and composite states should not be used in this project's diagrams.** Reasoning: a business status like an order or invoice status is a label on a database column, not a running process with its own internal sub-behavior — there's nothing "inside" a `pending_approval` status that itself needs a mini state machine. Anything that composite states would model as internal sub-states (e.g. "pending approval, and within that, waiting on manager vs. waiting on finance") is solved just as well, and far more legibly for a client review, by adding a couple more flat states (`pending_manager_approval`, `pending_finance_approval`) at the same level. Flat states also map 1:1 onto a single status column's enum values, which is exactly the shape `docs/statuses.md` needs (see below) — nesting would break that mapping for no benefit at this complexity level.

Skip composite states, submachine states, and history pseudostates (shallow/deep) entirely in this project's diagrams.

**Explicitly out of scope, one line each, not detailed further here:** concurrent/orthogonal regions (parallel sub-states within a composite state, §14.2.3.2) and history pseudostates (shallow/deep, §14.2.3.4.4) — both exist in the spec but are unlikely to ever be needed at SME-ERP status-tracking complexity; skip them if they surface in the spec while reading, don't reach for them here.

## The hard rule: every transition must trace to a stated business rule

**A transition only belongs on the diagram if it is traceable to something the client actually said during elicitation, or to an explicit regulatory/operational constraint — never invented at diagram-drafting time.** This is the single most important convention in this file.

Concretely: before drawing an arrow from status A to status B, there must be a specific elicitation note, an explicit UC requirement, or a named regulation/operational rule that says "this can move from A to B, triggered by X." If the only justification is "well, logically it should be possible to go from A to B," it does not go on the diagram — flag it as an open question for the client instead. This mirrors the as-is/to-be confirmation gate in [`bpmn-conventions.md`](bpmn-conventions.md#as-is-vs-to-be): don't silently resolve an ambiguity by drawing what seems reasonable, and don't design speculative lifecycle shortcuts into the diagram without client sign-off.

## Pairs with `docs/statuses.md`

`docs/statuses.md` is the canonical list of legal status **values** per entity (the vocabulary). This diagram is the visual form of the other half of that same information: which **transitions** between those values are actually legal. The two files must stay in sync — every state on the diagram must appear in `statuses.md`'s value list for that entity, and vice versa.

Critically, **not every pair of valid statuses has a legal transition between them.** `statuses.md` alone doesn't tell you that; only the diagram (and the business rule behind each arrow) does. For example, an invoice might have valid statuses `draft`, `sent`, `paid`, `void` — but "paid → draft" is very unlikely to be a real transition even though both are valid values. Don't assume a transition exists just because both endpoints are legal values; it still needs its own confirmed business rule per the hard rule above.

## Confirmed during elicitation

Same confirmation gate as the BPMN to-be diagram (see [`bpmn-conventions.md`](bpmn-conventions.md#as-is-vs-to-be)): don't diagram a transition that hasn't actually been confirmed with the client. If two elicitation sessions describe the same entity's lifecycle differently (e.g. one stakeholder says cancellation is only possible from `draft`, another says it's possible from `submitted` too), don't quietly pick one — flag the contradiction and get it resolved before it goes on the diagram. State diagrams drawn during elicitation are inherently to-be-flavored (there usually isn't a meaningful "as-is state diagram" for a process that previously ran on paper/spreadsheets), so treat every transition as needing the same sign-off a to-be BPMN transition would need.

## draw.io shapes

Verified against the installed draw.io Desktop app's bundled UML shape library (`app.asar`) — not from `search_shapes` (see the note in [`bpmn-conventions.md`](bpmn-conventions.md#drawio-shape-mapping) for why). Re-verify if draw.io Desktop is ever updated to a version with a different shape library.

| Element | style |
|---|---|
| Simple state (rounded rectangle) | `shape=umlState;rounded=1;whiteSpace=wrap;html=1;` — draw.io's real UML state shape |
| Initial pseudostate (filled black circle) | `ellipse;fillColor=strokeColor;html=1;` — draw.io's palette labels this "Initial pseudostate / node" |
| Final state (filled circle with a ring) | `ellipse;html=1;shape=endState;fillColor=strokeColor;` — draw.io's palette labels this "Final state / node" |
| Transition arrow | plain edge, open arrowhead, `endArrow=open;endSize=8;html=1;` — set the edge's label text to the transition's `event [guard] / effect` string |

Anything not covered above — open the draw.io desktop app's UML shape panel and inspect directly rather than guessing.
