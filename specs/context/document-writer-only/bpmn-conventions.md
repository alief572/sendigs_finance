# BPMN Conventions

Working reference for hand-drafting BPMN process diagrams (`docs/diagrams/bpmn-as-is.drawio`, `bpmn-to-be.drawio`) from client elicitation notes. Distilled from OMG BPMN 2.0 (`formal-11-01-03.pdf`); own words, spec sections cited inline for verification. Not a copy of the spec — read the spec directly for edge cases.

## Diagram types actually used here

BPMN defines Process (orchestration), Collaboration, and Choreography diagrams (§7). This project only needs the first two:

- **Process diagram** — one actor's internal flow, private and either non-executable (documenting) or executable (§7, "Private Business Processes"). This is what a single-pool, no-swimlane diagram is.
- **Collaboration diagram** — two or more Participants (Pools) exchanging Message Flows (§7, "Collaborations"). Use this whenever the process crosses an organizational boundary (client ↔ vendor, department ↔ department).

Choreography and Conversation diagrams are out of scope for this project — skip them.

## Diagram levels (this project's 3-level scheme)

"Process levels" isn't a BPMN spec concept — it's an informal process-architecture convention, and sources disagree on the exact count and boundaries. Per this project's [terminology rule](../general-rules.md#terminology-rule), the underlying concepts below are cited to two recognized practitioner sources rather than invented — but **the specific 3-level scheme itself, and the choice to use BPMN notation at every level, is this project's own deliberate simplification**, not a claim that Signavio or Oracle define it this way. Say so explicitly if this is ever presented to a reviewer.

Terminology note: earlier drafts of this doc called these "tiers." The project has since standardized on **"Level"** (Level 1 / Level 2 / Level 3, matching common BPMN-practitioner usage) — use that word in diagram page names and conversation, not "tier."

**What's cited:**
- Signavio: Level 0 is a landscape/overview of an organization's management, core, and support processes — **not itself a chain of sequential steps** ([Signavio, "Business Process Mapping Levels"](https://www.signavio.com/wiki/process-discovery/business-process-mapping-levels/)).
- Signavio + Oracle agree: the *chain* (a sequence of major end-to-end stages, e.g. order-to-cash) is **Level 1**, not Level 0 (Signavio, as above; Oracle: "a value chain is a high-level model that categorizes the generic value-adding activities of an organization" — [Oracle, "Process Modeling using BPMN 2.0"](https://www.oracle.com/ocom/groups/public/@otn/documents/webcontent/172298.pdf), discussed in [AVIO Consulting, "Business Architecture and Hierarchical Process Modeling"](https://avioconsulting.com/blog/business-architecture-and-hierarchical-process-modeling/)).
- Both sources also agree BPMN notation is meant to start appearing only at deeper levels (Signavio: "starts to appear" around Level 2; Oracle: BPMN applies from Level 3 onward) — **not** at the landscape/chain level.
- Michael Porter's Value Chain (the ancestor concept both sources' core/support split derives from) is a legitimate alternate lens but **not a drop-in relabeling** of this scheme: Porter has only two tiers (Primary/Support, no separate "Managerial" category — staff/HR work is Support, not its own tier), and critically, **Porter classifies Procurement as a Support activity**, not Primary — the opposite of where this project's Main/value-chain lane puts it. Adopting Porter's vocabulary would mean restructuring lanes, not just renaming them; don't do this without a specific reason (e.g. a stakeholder who knows Porter's model specifically).

**What's this project's own simplification (deliberately diverging from both sources above, for a small team that doesn't benefit from maintaining two separate notations):**

- **Level 1 — Landscape + Chain (merges Signavio/Oracle's top two levels).** One diagram: a single Pool for the organization, with Lanes categorizing processes as **Managerial / Main / Supporting** (Signavio's management/core/support split). The **Main** lane holds the value chain — the major end-to-end stages, positioned left-to-right in sequence. Every box is drawn as a **collapsed BPMN sub-process** (the small `+` marker), even though the cited sources say plain boxes are more typical here — this project uses BPMN notation uniformly across all levels instead of switching notations partway through. **No connecting arrows between the chain boxes** — position implies order, this level doesn't carry formal Sequence Flow semantics. A box's `+` marker means "this has (or will have) its own Level 2 diagram," not that it's already been decomposed.
- **Level 2 — Workflow areas.** A **separate diagram per Level-1 box**, decomposing that one box only — not a single diagram covering the whole chain. Per Signavio's own wording, a Level 2 map shows what's "inside *a* larger end-to-end process" (singular) — so if Level 1 has, say, six Main-lane boxes plus a Managerial box and a Supporting box, that's up to eight independent candidate Level 2 diagrams, each scoped to one box. This level is where Pools/Message Flow start to matter (a Level-1 box that looked like one atomic step often turns out, on decomposition, to be a multi-party interaction — see the worked example below). Still built from collapsed sub-processes internally where a piece is complex enough to warrant its own further breakdown; plain tasks where it isn't.
- **Level 3 (and beyond) — Detailed process maps.** Full operational BPMN: individual tasks, gateways, events, lanes for actual roles. **This is what `docs/diagrams/bpmn-as-is.drawio` and `bpmn-to-be.drawio` are**, for a process simple enough not to need a Level 2 at all. When a Level 2 diagram does exist, Level 3 follows the **same strict rule as every level above it: one diagram decomposes exactly one box from its parent, never more.** A four-box Level 2 diagram does not get a single Level 3 diagram covering all four — only the specific box(es) that are actually worth decomposing get their own Level 3 page, and that page's end event hands off back into the parent diagram's flow (e.g. into the next sibling box) rather than re-showing the rest of the parent. This is not a Signavio rule — Signavio's own wording is looser ("*a* Level 2 map may show... inside *a* larger end-to-end process," describing typical content, not mandating strict 1:1 nesting) — it's this project's own tightening, validated by actually building it out (see `context/document-writer-only/examples/retail-store-bpmn.drawio`, this project's worked reference example — a simple retail store modeled through Level 1 → four Level 2 pages → two Level 3 pages). The nesting goes as deep as the real process needs — a Level 4, Level 5, etc. box is the same pattern recursing further, there's no fixed ceiling.

**Don't decompose until it's worth it** — same principle as `class-diagram-conventions.md`'s "small modules can stay undocumented": a trivial box at any level doesn't need a child diagram just because the `+` marker exists as a *convention*; only build the next level down when there's enough real internal complexity to justify a reader needing it. Most boxes at any given level should stay leaves — only the ones actually worth drilling into get decomposed. Worked example: in `context/document-writer-only/examples/retail-store-bpmn.drawio`, "Store Maintenance" and "Finance & Admin" (Supporting lane) and "Store Management" (Managerial lane) were deliberately left as undecomposed leaves — they're plausible but not necessary to prove out for a demo — while "Staff Management" got a Level 2 specifically to show what a single-actor, Timer-triggered Managerial-lane flow looks like in contrast to the Main-chain's multi-party ones.

**Navigating between levels**: when multiple levels of the same process live together in one `.drawio` file (one page/tab per diagram), give a box that has a child diagram a distinguishing fill (e.g. light blue, `fillColor=#dae8fc;strokeColor=#6c8ebf;`) and a draw.io internal page link to that page, plus a "back to parent" link on the child page — see [draw.io shape mapping](#drawio-shape-mapping) for the exact link syntax. Leaf boxes stay unlinked and undecorated; the fill color is the only signal for "this has more detail," so don't apply it to a box that doesn't.

**Rollup diagrams — an optional alternative artifact for small processes.** Strict per-box recursion (above) is the *structural* rule and stays the default — it's what keeps individual diagrams legible as a process grows complex. But for a chain small enough to read legibly on one page (a handful of leaf tasks total), it's also fine to produce a **rollup**: a single diagram showing every leaf task expanded inline, generated by literally laying out the fully-decomposed detail in one place rather than clicking through levels. This is *not* a replacement for the leveled pages — it's a second, additional artifact for when a reader wants the whole small process at a glance instead of navigating three pages to see four tasks. Two rules keep this from undermining the leveled structure:

- A rollup is only worth producing once the leveled decomposition is actually done — don't skip straight to a rollup as a shortcut past building the real levels, or the recursive structure never gets built and this becomes the same "merge everything into one diagram" problem the strict rule exists to prevent.
- Label it explicitly as a rollup (e.g. a page/diagram named "Full detail (rollup)") so nobody mistakes it for a fourth level in the hierarchy — it's a rendering of levels already built, not a new level.

## Task types

All tasks share the rounded-rectangle shape; the icon in the top-left corner is what distinguishes them (§10.2.3.1). Pick based on who/what actually does the work. draw.io renders every task as `shape=mxgraph.bpmn.task2` with a `taskMarker=` attribute selecting the icon (verified against the installed draw.io Desktop's shape library, see [draw.io shape mapping](#drawio-shape-mapping) below):

- **User task** — a human performs the task at a screen/form, tracked by a system task list (§10.2.3.1, "User Task"). Use for anything done inside the ERP UI: filling a form, approving a record, reviewing a screen. `taskMarker=user`.
- **Manual task** — a human does the work with no system involvement at all, not tracked by any engine (§10.2.3.1, "Manual Task"). Use for physical/offline steps: signing a paper, physically inspecting goods, a phone call with no logging. `taskMarker=manual`.
- **Service task** — an automated system/service does the work, no human involved (§10.2.3.1, "Service Task"). Use for automated jobs, integrations, calculations the system does on its own. `taskMarker=service`.
- **Script task** — like a service task but specifically "a script the engine executes" (§10.2.3.1, "Script Task"). In practice, treat as a Service task unless the client explicitly distinguishes "the system runs a script" from "the system calls a service" — usually not worth the distinction in elicitation-level diagrams. `taskMarker=script`.
- **Business rule task** — the process hands off to a rules engine/decision table and gets a decision back (§10.2.3.1, "Business Rule"). Use when a business calls out an explicit rule set (e.g., "the system decides which approval tier applies based on amount"). `taskMarker=businessRule`.
- **Send task** — fires a message to another participant and immediately completes (§10.2.3.1, "Send Task"). Use for one-way notifications: send a Sales Order to the finance API, email a confirmation. `taskMarker=send`.
- **Receive task** — waits for a message to arrive from another participant before completing (§10.2.3.1, "Receive Task"). Use when the process is blocked waiting on external input; a Receive task with no incoming Sequence Flow can start the process (see Events below). `taskMarker=receive`.
- **Abstract/plain task** (no icon) — use when the type genuinely isn't known yet during elicitation, or doesn't matter for the diagram's purpose. Don't leave this as a lazy default — pick a real type once it's known. `taskMarker=abstract`.

If unsure between User and Service: ask "does a person look at a screen for this step?" Yes → User task. No → Service task.

Sub-process / call activity / transaction are the same base task shape with a structural attribute instead of a `taskMarker`: `bpmnShapeType=subprocess` (embedded sub-process, collapsed or expanded via `isLoopSub`), `bpmnShapeType=call` (Call Activity — invokes a reusable global process), `bpmnShapeType=transaction` (Transaction sub-process, §10.6). Rare in elicitation-level diagrams — reach for these only when the client explicitly describes a reusable sub-flow or a multi-step unit that must all succeed or all roll back.

## Gateway types

Gateways are diamonds; they control how Sequence Flow tokens converge and diverge (§10.5). Four kinds matter here — see [draw.io shape mapping](#drawio-shape-mapping) below for the exact, verified style strings (`gwType=`, not `symbol=exclusiveGw`/`parallelGw`/etc. as this section used to say — those attribute values don't exist in the installed shape library):

- **Exclusive (XOR)** — diverging: exactly one outgoing path is taken, decided by a condition (§10.5.2). Converging: any arriving token passes through, no wait. This is an if/else decision. Renders as a diamond with an X.
- **Parallel (AND)** — diverging: all outgoing paths are taken simultaneously. Converging: waits for *all* incoming paths before continuing (§10.5.4). Use for "these things happen at the same time" / "these things must all finish before continuing" — a genuine fork/join, not a decision. Renders as a diamond with a `+`.
- **Inclusive (OR)** — diverging: one or more outgoing paths are taken, each independently evaluated by its own condition (§10.5.3). Converging: waits for all *currently active* branches to arrive (more complex synchronization than Exclusive or Parallel). Use only when a step can trigger multiple simultaneous but conditional branches (e.g., "notify by email AND/OR SMS depending on preferences, could be both, could be one"). Rare in practice — reach for Exclusive or Parallel first; only use Inclusive when the process genuinely has this "zero or more of these branches" shape. Renders as a diamond with an unfilled circle.
- **Event-based** — diverging only: the branch taken is decided by *which event happens first* (a message arrives, a timer fires), not by evaluating data (§10.5.6). Each outgoing path must lead to an Intermediate Event or Receive Task, not a Task. Use for "we wait to see what happens next" scenarios — e.g., "either the customer responds within 3 days (message) or the request times out (timer)." Not yet verified in this project's palette — inspect the installed app's BPMN panel directly before using.

**Every diverging gateway must be labeled with its decision question** (e.g. "In Stock?", "Item Sellable?") — never leave a diverging gateway's label empty. A converging gateway (multiple paths merging back into one, no decision happening) may stay unlabeled. See [Sizing and spacing](#drawio-shape-mapping) for the full labeling/sizing rule.

Decision heuristic: business rule/condition on data → Exclusive. Things literally happen together → Parallel. Racing external triggers decide the path → Event-based. Multiple independent yes/no conditions can co-fire → Inclusive (last resort).

Complex gateway (§10.5.5, arbitrary activation conditions) exists in the spec and in this project's verified palette (`gwType=complex`) but essentially never shows up in a business-elicitation diagram; omit it from the toolkit unless a process genuinely can't be expressed with the four above.

### No implicit merge — every convergence gets a gateway

**Never let more than one Sequence Flow land on the same Task or Event.** Two or more incoming Sequence Flows on an activity is an **implicit merge** (the spec's wider term for flow not routed through a gateway is *uncontrolled flow*, §13) — always insert an explicit converging gateway instead. This is the single most-cited rule in Bruce Silver's *BPMN Method and Style*, and per this project's [terminology rule](../general-rules.md#terminology-rule) that's the practitioner source we follow for it.

The spec permits implicit merge, so it will never fail validation — that's exactly why it's dangerous. Its defined semantics are that **each arriving token fires the activity independently**, i.e. it silently behaves as **XOR**, with no waiting. So a modeller who meant "wait for both branches" (AND-join) gets a diagram that looks right and means the opposite, and nothing flags it. Making the merge explicit forces the author to state which of the three they meant, which is the whole reason the symbol carries information:

| Converging gateway | Semantics |
|---|---|
| Exclusive (XOR) | first token passes straight through, no waiting — exactly one path was live |
| Parallel (AND) | **blocks** until every incoming path has arrived |
| Inclusive (OR) | blocks until all *currently active* branches arrive |

**Retry/loop-backs are the most common source** — a loop returning to a step that already has an inbound flow creates the merge without anyone noticing. Send the loop into a converging Exclusive gateway placed just before that step, and route the normal inbound flow through the same gateway. Both loops in `context/document-writer-only/examples/movie-booking demo/movie-booking-bpmn.drawio` were built this way after an initial version landed the retry directly on a User task and a catch event.

A converging gateway stays **unlabeled** (no decision is being made there) — see the labelling rule under [Sizing and spacing](#drawio-shape-mapping).

**Message Flow does not count.** The rule is about Sequence Flow only; a catch event with one incoming Sequence Flow plus one incoming Message Flow is normal and correct. Check with:

```bash
python - <<'EOF'
import xml.etree.ElementTree as ET
from collections import Counter
t = ET.parse('diagram.drawio'); seq = Counter(); ids = {}
for c in t.iter('mxCell'):
    if c.get('vertex') == '1': ids[c.get('id')] = c.get('style','')
    if c.get('edge') == '1' and c.get('target') and 'startArrow=oval' not in c.get('style',''):
        seq[c.get('target')] += 1
print([(k,v) for k,v in seq.items() if v > 1 and 'gateway2' not in ids.get(k,'')] or "NONE")
EOF
```

`startArrow=oval` is what distinguishes a Message Flow from a Sequence Flow in this project's style strings (see the connector table below). Anything this prints is an implicit merge that needs a gateway.

## Event types

Events are circles; border thickness marks Start (thin) vs Intermediate (double) vs End (thick) (§10.4). The icon inside marks the trigger/result. draw.io renders every event as `shape=mxgraph.bpmn.event` with two attributes doing the work: `outline=` picks the border style (which position/interrupt-behavior it is) and `symbol=` picks the trigger icon:

- **Start event** — where the process instance begins; no incoming Sequence Flow (§10.4.2). `outline=standard` (thin single border).
  - **None** (no icon) — process starts by some unmodeled/manual trigger ("someone begins the process"). Default when the trigger isn't a message or timer. `symbol=none`.
  - **Message** (envelope icon) — process is kicked off by an incoming message/request (§10.4.2, Table 10.84). Use when a process starts because another participant sends something — a customer submits a request, another pool's Send task targets this one. `symbol=message`.
  - **Timer** (clock icon) — process starts on a schedule or at a specific time/date (§10.4.2, Table 10.84). Use for periodic/scheduled processes (e.g., "every Monday, generate the weekly report"). `symbol=timer`.
- **End event** — where a path of the process terminates; no outgoing Sequence Flow (§10.4.3). `outline=end` (thick single border).
  - **None** (no icon) — plain completion, nothing further happens. `symbol=none`.
  - **Message** (filled envelope) — sends a message as the process concludes (§10.4.3, Table 10.88). Use when finishing the process implies notifying someone. `symbol=message`.
  - **Error** (lightning-bolt icon) — process ends abnormally with a named error, caught by a matching boundary Error event elsewhere (§10.4.3, Table 10.88). Use to mark abnormal/failure termination paths. `symbol=error`.
  - **Terminate** (filled black circle) — immediately ends the *entire* process instance, cancelling all other active paths, not just this one (§10.4.3). Use sparingly — only for a genuine "abort everything now" ending, not a normal completion — a normal successful end should use None (`symbol=general` or `symbol=none`, both confirmed to render a plain unmarked circle), not Terminate. `symbol=terminate` — this project's palette also has a second, visually distinct `symbol=terminate2` icon; both are legitimate, pick whichever the team prefers for that specific abort semantic, but don't default to either for an ordinary end.
- **Intermediate event** — something happens mid-process without starting or ending it (§10.4.4). Two placements: inline in normal flow (catch or throw — `outline=catching` / `outline=throwing`, both double-border), or attached to a task/sub-process boundary (catch only — represents exception handling — `outline=boundInt` interrupting / `outline=boundNonint` non-interrupting, both double-border but interrupting is solid and non-interrupting is dashed).
  - **Message** — inline: waiting to receive, or sending, a message mid-process. On a boundary: interrupts or doesn't interrupt the attached activity if a message arrives while it's running (§10.4.4, Tables 10.89–10.90). Use for "waiting on a reply" or "an update arrives while this step is in progress." `symbol=message`.
  - **Timer** — inline: a wait/delay before continuing. On a boundary: a timeout on the attached activity (e.g., "if not approved within 3 days, escalate") (§10.4.4, Tables 10.89–10.90). `symbol=timer`.
  - **Error** — boundary only, always interrupting (`outline=boundInt`) — catches a named error thrown elsewhere in the attached activity and reroutes the flow to an exception path (§10.4.4, Table 10.90, "Error"). This is the standard "if something goes wrong in this step, branch to error handling" pattern. Not valid inline in normal flow. `symbol=error`.

For elicitation-level diagrams, None/Message/Timer/Error(/Terminate for end) cover nearly everything. Skip Escalation, Signal, Conditional, Link, Compensation, Cancel unless a specific requirement clearly needs one — they exist in both the spec (§10.4.5) and draw.io's palette (`symbol=escalation`, `symbol=signal`, `symbol=conditional`, `symbol=link`, `symbol=compensation`, `symbol=cancel`) but rarely surface in business-process elicitation.

## Pools and lanes

- **Pool = one Participant** — a distinct organization, system, or external party in the process (§9.2). One pool per company/department/external system that has its own process. A pool MAY be a "black box" (no internal detail, just a boundary) when the other side's internals aren't known or relevant — common for as-is diagrams of external parties.
- **Lane = sub-partition within a pool's process** — typically one lane per role, sub-department, or system component inside that participant (§9.2.2, §10.7). Lanes never cross pool boundaries; a Sequence Flow can cross lanes within a pool but never cross into another pool.
- **Sequence Flow** stays inside a single pool (connects activities within the same participant's process). **Message Flow** (§9.3) is the only connector allowed *between* pools — it represents a message/handoff crossing the organizational boundary.
- Rule of thumb when drafting: if two steps are done by the same organization but different people/roles, use lanes in one pool. If they're done by different organizations/systems, use separate pools connected by Message Flow.
- **Lightweight black-box alternative** (not spec-standard, but validated repeatedly in this project's worked example — Supplier/Customer/Payment Gate/Staff all use it): when an external participant has zero internal detail worth showing and isn't itself getting decomposed, it's acceptable to draw it as a **plain rectangle** (`style="whiteSpace=wrap;html=1;"`, just a label, no Pool/Lane structure) instead of a full black-box Pool, connected by the normal dashed connector style. This trades spec purity for less visual weight when the external party is genuinely just "the other end of a message," not a participant whose own process matters to the diagram. Use a real black-box Pool instead when the external party's role needs to look structurally equal to the internal Pool (e.g. a Collaboration diagram between two real organizations where both sides matter), or when it might later get its own Level 2.
- In draw.io, use the **generic Pool container** (`style="swimlane;html=1;childLayout=stackLayout;resizeParent=1;resizeParentMax=0;horizontal=1;startSize=20;horizontalStack=0;whiteSpace=wrap;"`, `value="<Participant name>"`), not the BPMN-specific `mxgraph.bpmn.swimlane` stencil — `childLayout=stackLayout` auto-stacks child Lanes top-to-bottom and auto-resizes the Pool to fit them, which avoids hand-computing each lane's `y` offset and pool height (a real source of overlap bugs when authoring by hand — hit and fixed multiple times while building `context/document-writer-only/examples/retail-store-bpmn.drawio`). **`horizontal=1` on the Pool itself is required** (title bar spans the top; `horizontal=0` on the Pool was an earlier mistake in this doc that produced broken/overlapping lanes — confirmed by testing, not theory). Lanes are children of the Pool using the plain `style="swimlane;html=1;startSize=20;horizontal=0;"`, `value="<Lane name>"`, each given a `height` (matching the Pool's width) but no `y` (the parent's stack layout positions them) — Lanes stay `horizontal=0` regardless of the Pool's own setting, which is what actually produces the horizontal-band, left-to-right-reading layout. The nesting (Lane inside Pool) is what distinguishes Pool from Lane, not the stencil — both still use the generic `swimlane` style, just with `childLayout=stackLayout` only on the outer Pool.

**Coordinate-space caution**: a Lane's children (tasks, events) have `x`/`y` *relative to the Lane*, which is itself relative to the Pool. A connector between something inside a Lane and something at the diagram's top level (e.g. a Message Flow to a lightweight black-box participant per above) needs its `entryX`/`exitX` fraction computed against the **top-level element's absolute position**, which means adding the Pool's own `x` offset — forgetting this produces a connector that's off by exactly the Pool's `x` (a real bug hit while authoring `context/document-writer-only/examples/retail-store-bpmn.drawio`'s Staff Management page: three message flows were each ~40px off because the Pool's `x=40` wasn't accounted for). When pinning `entryX`/`exitX` on a cross-level connector, always sanity-check the fraction against actual absolute coordinates, not just the child's local `x`.

## As-is vs to-be

Same notation, same element vocabulary for both — the difference is process, not syntax:

- **As-is**: drawn directly from raw elicitation notes/interviews, warts and all — captures what actually happens today, including undocumented workarounds, manual steps, and inconsistencies between what different stakeholders describe. Don't "clean up" contradictions found in elicitation; flag them for follow-up instead of silently resolving them in the diagram.
- **To-be**: the converged, redesigned version after gaps/inefficiencies in the as-is are discussed and resolved with the client. Only diagram what's been confirmed — don't design speculative process improvements into the to-be diagram without client sign-off.

## draw.io shape mapping

This project's `drawio` skill (`.claude/skills/drawio/SKILL.md`) authors BPMN as hand-written draw.io XML (Mermaid has no native BPMN diagram type). The style strings below were corrected during hands-on authoring of `context/document-writer-only/examples/retail-store-bpmn.drawio` — an earlier version of this section was written from `app.asar` inspection without opening the actual app, and several attributes it listed either don't render or render wrong (flagged inline below). The current strings are verified against actual renders in the installed draw.io Desktop and against `context/document-writer-only/examples/elements.drawio`, a project-authored reference file holding one of every shape this project uses, kept as a living palette. Re-verify against the installed app if draw.io Desktop is ever updated to a version with a different shape library.

**Sizing and spacing** — apply uniformly across every level that uses these shapes:

- Tasks: **120×80**.
- Events and Gateways: **40×40** (same size as each other, smaller than a task). Label renders **below** the shape (`verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;`), not inside it.
- Gap between adjacent elements: **40px minimum**, increasing in **+20px increments** (40 → 60 → 80…) only as needed to clear an overlap (e.g. a branch task that needs room for its own outgoing label) — not a rigid constant.
- **Gateway labeling**: every **diverging** gateway must be labeled with its decision question (e.g. "In Stock?"). A **converging** gateway (multiple flows merging back into one) may stay unlabeled — no decision is being made there. Never leave a *diverging* gateway's `value=` empty.
- Align branch elements (the target of a gateway's "No"/secondary path) to the **gateway's own x-center**, not the "Yes" branch's task column — this keeps the branch's straight vertical drop from crossing the happy-path's cross-lane connectors. Confirmed the fix for a real overlap bug during authoring.

**Tasks** — `shape=mxgraph.bpmn.task2;whiteSpace=wrap;rectStyle=rounded;size=10;html=1;container=1;expand=0;collapsible=0;taskMarker=<marker>;` plus the clip-path `points=[[0.25,0,0],[0.5,0,0],[0.75,0,0],[1,0.25,0],[1,0.5,0],[1,0.75,0],[0.75,1,0],[0.5,1,0],[0.25,1,0],[0,0.75,0],[0,0.5,0],[0,0.25,0]];` — this is the actual palette shape (an octagon-cut-corner outline); the earlier `shape=mxgraph.bpmn.task2;rounded=1;taskMarker=<marker>;` (no `points=`, no `container=1`) is a simplified substitute that renders acceptably for plain tasks but should be replaced with the full string above for consistency.

| BPMN task type | `taskMarker=` |
|---|---|
| User | `user` |
| Manual | `manual` |
| Service | `service` |
| Script | `script` |
| Business Rule | `businessRule` |
| Send | `send` |
| Receive | `receive` |
| Abstract / plain | `abstract` |

**Loop marker** (Standard Loop, §10.2.3, "a task that repeats until a condition"): add `isLoopStandard=1` to any task's style (e.g. `taskMarker=abstract;isLoopStandard=1;` for a plain repeating task) — renders a small circular-arrow icon bottom-center. This is the spec-correct way to show "repeat this task per item/until done"; don't fake it with an external annotation shape + dashed line pointing at the task.

**Collapsed sub-process** (the Level 1/Level 2 building block): `taskMarker=abstract;isLoopSub=1;` on the same base task style — renders the small `+` marker bottom-center. Combine with `isLoopStandard=1` (`taskMarker=abstract;isLoopStandard=1;isLoopSub=1;`) for a **looping** collapsed sub-process (both markers render together). Sub-process/call/transaction can alternatively use `bpmnShapeType=subprocess` / `call` / `transaction` instead of `taskMarker`, but the verified `isLoopSub=1` form above is what this project actually uses and has confirmed renders correctly (solid border, small `+`).

**Gateways** — `shape=mxgraph.bpmn.gateway2;html=1;verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;perimeter=rhombusPerimeter;outlineConnect=0;` plus the clip-path `points=[[0.25,0.25,0],[0.5,0,0],[0.75,0.25,0],[1,0.5,0],[0.75,0.75,0],[0.5,1,0],[0.25,0.75,0],[0,0.5,0]];` (an octagon approximating the diamond) and a `gwType=` attribute — **not** `symbol=exclusiveGw` as an earlier version of this doc said; that attribute doesn't exist in the installed library and silently produces a bare/wrong diamond. The `verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;` part is easy to drop by accident (it's not needed for the diamond to render, only for the label) — omitting it renders the label crammed inside/on top of the diamond instead of cleanly below it. Same rule as Events below.

| Gateway type | style attributes | notes |
|---|---|---|
| Exclusive | `gwType=exclusive;outline=none;symbol=none;` | renders an X in the diamond |
| Parallel | `gwType=parallel;outline=none;symbol=none;` | renders a `+` in the diamond |
| Inclusive | `outline=end;symbol=general;` (no `gwType`) | renders an unfilled circle in the diamond — the one gateway type that is *not* driven by `gwType=` |
| Complex | `gwType=complex;outline=none;symbol=none;` | renders an asterisk in the diamond; out of scope for elicitation-level diagrams, kept here only because it exists in the reference palette |
| Event-based | not yet verified in this project's palette | if needed, inspect the installed app's BPMN panel directly rather than guessing |

**Events** — `shape=mxgraph.bpmn.event;html=1;verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;perimeter=ellipsePerimeter;outlineConnect=0;aspect=fixed;outline=<outline>;symbol=<symbol>;` plus the clip-path `points=[[0.145,0.145,0],[0.5,0,0],[0.855,0.145,0],[1,0.5,0],[0.855,0.855,0],[0.5,1,0],[0.145,0.855,0],[0,0.5,0]];` (an octagon approximating the circle) — the earlier simpler string (`perimeter=ellipsePerimeter;outline=…;symbol=…;` with no `points=`) still renders correctly for basic circles; use the full string above for consistency with the verified reference palette.

`outline=` sets position/border:

| Position | `outline=` |
|---|---|
| Start | `standard` |
| End | `end` |
| Intermediate, inline, catching | `catching` |
| Intermediate, inline, throwing | `throwing` |
| Boundary, interrupting | `boundInt` |
| Boundary, non-interrupting | `boundNonint` |

`symbol=` sets the trigger icon — confirmed working values: `general` (renders a bare/unmarked circle — this **is** correct for a plain None start/end, despite the name; not a mistake), `message`, `timer`. For a normal (non-abort) completion, use `outline=end;symbol=general;` (or plain `symbol=none`, also confirmed blank/plain) — do **not** default to `terminate`/`terminate2` for an ordinary successful end. Both `terminate` and `terminate2` are legitimate, visually distinct icons in this palette (confirmed two different terminate-style glyphs exist) reserved for genuine "abort everything" semantics — pick whichever the client/team prefers when that specific meaning is intended, but don't use either as a stand-in for a normal end. Skip-by-default (rare in elicitation diagrams): `error`, `escalation`, `signal`, `conditional`, `link`, `compensation`, `cancel`, `multiple`, `parallelMultiple`.

**Pools/Lanes** — Pool: `swimlane;html=1;childLayout=stackLayout;resizeParent=1;resizeParentMax=0;horizontal=1;startSize=20;horizontalStack=0;whiteSpace=wrap;` — note **`horizontal=1`** on the Pool itself (title bar on top, spanning full width); the earlier `horizontal=0` on the Pool was wrong and produced broken/overlapping lanes. `childLayout=stackLayout` auto-stacks Lane children top-to-bottom; only give Lanes a `height` (and matching `width`), never hand-position their `y`. Lane (child of Pool): `swimlane;html=1;startSize=20;horizontal=0;` — Lanes stay `horizontal=0` (horizontal band, rotated label on the left). Not the BPMN-specific `mxgraph.bpmn.swimlane` stencil — `childLayout=stackLayout` auto-stacks and auto-resizes, avoiding manual `y`-offset math.

**Data objects** — what they're *for* (this was previously undocumented beyond the raw style string): a Data Object represents information/a document/an artifact a task reads or produces — a Purchase Order, a Receipt, a Refund Record — **not** a participant and **not** a flow of control. It connects to a Task via an **Association** (dashed, no directional Sequence/Message Flow semantics), never via Sequence Flow or Message Flow. The three variants: `bpmnTransferType=none` (plain — the process uses/produces it, direction not emphasized), `=input` (this task needs it before it can run), `=output` (this task produces it as a result). Use sparingly — only when a specific document/record matters enough to the process that a reader needs to see it explicitly, not for every task's implicit paperwork. **Different from Message Flow**: Message Flow crosses a Pool boundary and represents communication between participants; a Data Object stays associated with a task *within* a process and represents the document/record itself, independent of who else sees it — a task can produce a Data Object (the record exists) and separately have a Message Flow send that information elsewhere (the record gets communicated) as two distinct facts in the same diagram.

Style: `shape=mxgraph.bpmn.data2;labelPosition=center;verticalLabelPosition=bottom;align=center;verticalAlign=top;size=15;html=1;bpmnTransferType=none|input|output;` at roughly `width=40;height=60` — label renders **below** the shape (`verticalLabelPosition=bottom`), matching the events/gateways rule above; this is a hard requirement in this project, not a suggestion — a Data Object label inside/overlapping the shape has been flagged as wrong every time it happened. `isCollection=1` adds a multi-instance/collection marker.

**Flows/connectors**:

| Connector | style |
|---|---|
| Sequence Flow | `edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;endArrow=blockThin;endFill=1;` |
| Conditional Sequence Flow | Sequence Flow + `startArrow=diamondThin;startFill=0;` (open diamond at source) |
| Default Sequence Flow | Sequence Flow + `startArrow=dash;` (slash mark at source) |
| Message Flow | `edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;dashed=1;startArrow=oval;startFill=0;` — **no `dashPattern=` override, no `endArrow=` override** (default dash spacing, default classic arrowhead). An earlier version of this doc specified `dashPattern=8 4;endArrow=blockThin;endFill=1;` — that renders fine but doesn't match what's actually used throughout `context/document-writer-only/examples/retail-store-bpmn.drawio`; matched the real convention here instead of the spec-plausible-but-unused variant. |
| Association | `dashed=1;dashPattern=1 4;endArrow=none;startArrow=none;` (or `endArrow=open` for a directed association, e.g. a Data Object produced by a task) |

**Straight vs. routed connectors**: `edgeStyle=orthogonalEdgeStyle;...;jettySize=auto;` is the default and usually right, but it can insert small jetty jogs even on a connector whose endpoints are already pinned to the same vertical/horizontal line (confirmed while straightening the Staff Management message flows) — if a connector should be perfectly straight, drop `edgeStyle=` entirely (a bare style with no `edgeStyle=` key renders a direct straight line between the pinned `exitX`/`entryX` points) rather than fighting the orthogonal router. Conversely, for a short hop between two adjacent, already-close shapes where a full orthogonal elbow would look cramped/kinked, a single manually-placed diagonal waypoint (`<Array as="points"><mxPoint x="…" y="…"/></Array>` inside the edge's `<mxGeometry>`) reads cleaner than forcing multiple 90°-only segments into a tight space — a legitimate, deliberately-used technique in this project's Process Return page, not a routing mistake to "fix."

**Prefer x-alignment over dodge-waypoints for avoiding Message Flow crossings**: when a Collaboration diagram has multiple Message Flows crossing the same gap between two Pools, the tempting fix for a crossing is to compute waypoints that route one line around the other — but that only pushes the crossing somewhere else if the two connectors' endpoint x-ranges still overlap (a real case worked through on `context/document-writer-only/examples/food-stand-bpmn.drawio`: an "Order + Payment" flow spanning x=100–360 and a "Sold Out Notice" flow nested inside that range at x=200–280 will cross *somewhere* in the gap no matter where either one's horizontal jog is placed, because one of Sold Out Notice's vertical segments is topologically forced to cross Order + Payment's horizontal segment). The stronger fix is **structural, not routing**: reposition the two connected elements (by reordering rows within a Lane, e.g. putting a decline-branch event in a row above the happy path instead of below it) so their x-coordinates coincide exactly. A Message Flow between two x-aligned elements renders as a single straight vertical line with zero bends, which can't cross anything else by construction — no waypoint math needed, and no risk of a future edit reintroducing the crossing. Reach for this before reaching for waypoints whenever the diagram's layout has any freedom to move the source/target elements at all; save manual waypoints for cases where the elements' positions are otherwise fixed (e.g. by the gateway-branch alignment rule above).

**Route loop-back Sequence Flows on the lane side *away* from the Message Flows.** In a Collaboration diagram every Message Flow attaches on the side of a Pool that faces the other Pool — for a two-Pool stack that means the upper Pool's message endpoints are all on its elements' *bottoms* and the lower Pool's are all on its elements' *tops*. So the upper Pool's lane is completely free **above** its element row, and the lower Pool's is free **below** its row. Put each Pool's retry/loop-back corridor in its own free band (upper Pool loops above, lower Pool loops below) and a loop can never cross a Message Flow. Getting this backwards is not a cosmetic problem — a loop-back routed into the message band crosses every vertical Message Flow it spans (validated while building `context/document-writer-only/examples/movie-booking demo/movie-booking-bpmn.drawio`, where the customer's "choose other seats" loop spans four of them). Leave ~60px between the element row's labels and the loop corridor.

**A gateway's branch connector and the gateway's own label fight for the same side.** The label sits below the diamond by default (see Sizing and spacing), so a branch flow that also *leaves* downward runs its vertical segment straight through that label — and equally, a branch that lands on an event whose label is on the connector's side will strike through that event's label instead. Two verified fixes, in order of preference: (1) send the branch out of the opposite side and flip only that gateway's label with `verticalLabelPosition=top;verticalAlign=bottom;` (the rest of the string unchanged); (2) if the branch target must stay put, offset the target off the gateway's centre axis and pin the route with `exitX`/`exitY` + `entryX`/`entryY` **plus** an explicit waypoint. Fix (2) alone is not enough — leaving the router to choose with only the target moved sent the flow straight through an adjacent Task in a real render before the waypoint was added.

**Modelling a request/response cycle that can retry, without an Event-based gateway.** The natural BPMN for "submit, get one of two answers back, retry on failure" is an Event-based gateway on the requester's side — but that shape is unverified in this project's palette (see Gateway types). The verified substitute: have the responding Pool send **one** result message from a single throwing Intermediate Message event, then place an Exclusive gateway *after* the throw on each side — the responder's gateway routes its own continuation (wait for the next step, or loop back to its catch event), the requester's gateway routes on the result content. Both sides loop back to the same pair of events, so the retry cycle stays symmetric and every Message Flow keeps a 1:1 source→target mapping. Two throw events feeding one catch event is the tempting alternative and should be avoided: it forces at least one Message Flow off the vertical and makes the converging edges liable to merge visually (see the converging-edge gotcha in `drawio-general-guide.md`).

Anything not covered above (Text Annotation is just a plain `text;` shape with no BPMN-specific stencil; Group is a generic dashed-rectangle container) — open the draw.io desktop app's "BPMN" shape panel and inspect directly, or check `context/document-writer-only/examples/elements.drawio`, rather than guessing.

**Elements reference file**: `context/document-writer-only/examples/elements.drawio` holds one labeled instance of every shape/marker combination this project uses (task types, gateway types, event start/end/interrupt variants, Pool/Lane pair, Data Object) — kept as a standalone reference file rather than a page bundled inside a specific worked example, since it's general-purpose, not tied to any one diagram's content. Treat it as the living source of truth for exact style strings: when this doc and `elements.drawio` disagree, re-derive this doc from the file (that's literally how the corrections in this section were found — by rendering it and reading back the XML), not the other way around. Update `elements.drawio` whenever a new shape variant gets used for the first time.

**Inter-page links** (for multi-level files, see "Navigating between levels" above) — verified against draw.io's own XSD schema and source strings, not guessed: a cell is only clickable-to-another-page if it's wrapped in a `<UserObject>`, not a plain `<mxCell>` — `mxCell` has no `link` attribute in the schema. Use **`label=`** on the `<UserObject>` for the visible text, not `value=` — `value=` on a `UserObject` is silently ignored by the renderer (confirmed: a box linked this way renders with a blank/empty label even though the XML looks reasonable) — this was a real bug hit while linking Level 1 boxes to their Level 2 pages.

```xml
<UserObject id="SomeId" label="Box Label" link="data:page/id,TARGET_PAGE_ID">
  <mxCell style="..." vertex="1" parent="...">
    <mxGeometry .../>
  </mxCell>
</UserObject>
```

`TARGET_PAGE_ID` is the `id` attribute of the target `<diagram>` element (give each page tab an explicit, memorable `id` when authoring, e.g. `id="level2procurement"`, rather than relying on an auto-generated one). A static PNG/SVG export cannot prove the link actually works — page navigation is only testable by opening the file in draw.io itself.
