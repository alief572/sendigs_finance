# Draw.io General Guide

Cross-cutting authoring discipline for **every** `.drawio` file in this project — BPMN, ERD,
class, state, component, whatever comes next. This is not a replacement for the `drawio` skill
(`.claude/skills/drawio/SKILL.md`), which covers the CLI mechanics (Mermaid vs XML, export
flags, ELK layout, URL generation) at a tool level. This file covers what the skill doesn't:
this project's own verified conventions, gotchas, and workflow — the things that have actually
gone wrong while authoring real diagrams here and would go wrong again without a record.

For diagram-type-specific shape/style strings, go to the matching conventions doc instead —
this file stays about mechanics that apply regardless of diagram type:

| Diagram type | Conventions doc |
|---|---|
| BPMN (as-is/to-be, Level 1/2/3) | `bpmn-conventions.md` |
| ERD | `erd-conventions.md` |
| Class (architecture, per-module) | `class-diagram-conventions.md` |
| State (per stateful entity) | `state-conventions.md` |
| Component (system-wide, module grain) | `../guide/component-conventions.md` |

## Environment (this project's actual Windows dev setup)

- draw.io Desktop executable: `C:\Program Files\draw.io\draw.io.exe`. Confirmed present; if a
  future session can't find it there, check `where draw.io` / `where draw.io.exe` before
  assuming it isn't installed.
- CLI export commonly prints `Unable to move the cache` / `Gpu Cache Creation failed` lines to
  stderr — these are harmless Electron/Chromium GPU-cache warnings, not export failures. Check
  the actual exit and the presence of the output file, not the presence of these lines (pipe
  through `grep -v "ERROR:"` if the noise is distracting, but don't treat it as a signal).
- LibreOffice-based tooling (e.g. a spreadsheet recalculation script) is **not** usable in this
  environment (`AF_UNIX` missing on this Windows Python build) — irrelevant to `.drawio` work
  directly, but don't assume LibreOffice is available for any adjacent tooling either.

## The mandatory workflow — every `.drawio` change, no exceptions

1. **Author or edit the XML.**
2. **Validate it's well-formed**: `python -c "import xml.dom.minidom as m; m.parse('PATH')"` (or
   the Windows Python path, e.g. `/c/Python314/python`, if `python` isn't on PATH).
2a. **Grep for XML comments before trusting step 2**: `grep -c '<!--' diagram.drawio`. Well-formed
    XML happily contains comments, so validation alone won't catch them — and simply *knowing* the
    "never use XML comments" rule has not been enough to prevent writing them: two diagrams in this
    project (a BPMN collaboration diagram and a full class diagram) both shipped with comments
    despite the rule already being documented, caught only on a later unrelated pass. Make the
    check a command, not a memory.
3. **Export to PNG** and **actually look at the rendered image** — read it as an image, don't
   trust the XML on its own:
   ```bash
   "C:\Program Files\draw.io\draw.io.exe" -x -f png -o "diagram.png" "diagram.drawio"
   ```
4. **Check specifically for**: overlapping shapes, a label sitting on top of a shape or another
   connector, gateway/decision labels not colliding with anything, lines crossing more than
   necessary, pool/lane/group labels not clipped, and the two convergence gotchas below.
5. **When something looks crowded or you're not sure**, crop the specific region tighter and
   re-inspect — see [Cropped close-up verification](#cropped-close-up-verification). Multiple
   real overlaps in this project's history were invisible at full-diagram zoom and only showed
   up once cropped in.
6. **Fix and re-export until the render is actually clean.** Do not report a diagram finished on
   the strength of the XML alone — every non-trivial diagram built in this project so far needed
   at least one render-driven fix that wasn't visible from reading the XML.
7. **Delete throwaway export PNGs when done** — this project keeps only the `.drawio` source
   under version control in `context/document-writer-only/examples/` or `docs/diagrams/`. Debug crop images are always throwaway;
   delete them as soon as you've used them, don't leave them sitting in the repo.

## Cropped close-up verification

When a region has several connectors converging, or you suspect two lines are close enough to
merge visually, crop it instead of eyeballing the full-diagram export. PowerShell recipe (adjust
the source rectangle to the region in question):

```powershell
Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile("path\to\diagram.png")
$bmp = New-Object System.Drawing.Bitmap 300,250
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.DrawImage($img, (New-Object System.Drawing.Rectangle 0,0,300,250), (New-Object System.Drawing.Rectangle <srcX>,<srcY>,300,250), [System.Drawing.GraphicsUnit]::Pixel)
$bmp.Save("path\to\_crop.png")
```

Then read the crop as an image. Delete it once you've confirmed the fix (or confirmed there's
nothing wrong) — see step 7 above.

## Structural gotchas that apply across diagram types

- **Z-order follows document order** — a cell drawn *later* in the XML renders *on top* of one
  drawn earlier. A background rectangle meant to sit behind other shapes (a module-grouping box,
  a highlight region) must appear **before** those shapes in the file, not after.
- **Coordinate-space offset in nested containers**: any child whose `x`/`y` is relative to a
  parent (a Lane inside a Pool, a table row inside a table) needs `entryX`/`exitX` fractions on
  cross-container connectors computed against the **absolute** position — remember to add every
  ancestor's own `x`/`y` offset. This has caused real off-by-exactly-that-offset connector bugs
  in both BPMN Pool/Lane work and would recur in any similarly nested structure.
- **Two edges converging on the same target's default entry point render as one merged symbol**,
  silently hiding one relationship — happened with two different ERD relationships both
  defaulting to a shared lookup table's top-center. Give converging edges distinct
  `entryX`/`exitX` fractions (e.g. `0.5` and `0.75`) whenever more than one connector lands on
  the same side of a table/shape.
- **Two connector jogs sharing an open routing corridor at nearly the same coordinate (within
  ~15–20px) visually merge into what looks like a single overlapping line** — happened with two
  unrelated ERD relationships both jogging through the same inter-group gap at close y-values.
  Keep parallel jogs sharing a corridor at least ~30–40px apart, whichever axis the corridor
  runs along.
- **A connector waypoint placed within ~20px of a container's own boundary (a lane's height, a
  group box's edge) visually merges with that boundary line** — same failure mode as the two
  gotchas above, different cause. Route backward/loop-back connectors through the actual open
  gap *between* structural elements, not hugging near a container's own edge.
- **A crossing that can't be designed away gets a line jump, not a bare intersection.** Two
  connectors meeting at a plain right-angle intersection are ambiguous — a reader can't tell
  whether the lines cross or join, and on a dense diagram that reads as a wrong relationship.
  Add `jumpStyle=arc;` to the edge that should hop over the other; draw.io then renders a small
  semicircular hop at every point where that edge crosses another. Rules that make it work:
  - **Declare the jump on one consistent side of each crossing pair**, not both — two edges that
    each declare `jumpStyle` will both hop at the same intersection and produce a double arc.
    Pick an orientation and keep it (e.g. the horizontal runner hops over the verticals).
  - It is a **last resort, not a substitute for layout.** Structural fixes come first: reposition
    the endpoints so the connectors don't cross at all (see the BPMN x-alignment technique in
    `bpmn-conventions.md`). Reach for a jump only where the crossing is topologically forced —
    which on a module-grouped ERD is common, since cross-module relationships have to traverse
    the same gaps.
  - **Line jumps do not fix overlap; spacing does.** A jump makes a *crossing* legible. Two
    connectors running *parallel* too close together still merge into what looks like one line,
    and no jump style helps. Give each long run its own corridor.

- **Long cross-group connectors belong in dedicated parallel corridors.** When several
  connectors have to traverse the same empty band, don't let the router pick each one's path
  independently — assign each its own horizontal corridor and its own vertical drop lane, via
  explicit waypoints. Worked example, the bolt-manufacturing ERD's four bottom-spanning
  relationships: horizontal corridors at y = 930 / 950 / 980 / 1020 and vertical lanes at
  x = 340 / 360 / 380 / 410 — roughly 20–40px apart, enough to read as separate lines at 1×
  export. Compare with the 30–40px figure in the parallel-jog gotcha above: that one concerns
  two *jogs* squeezed into one narrow corridor, where less separation merges them; a set of long
  straight runs in open space tolerates the tighter 20px end of the range.

- **Never use XML comments (`<!-- -->`) in `.drawio` output** — already called out in the
  `drawio` skill, repeated here because it's easy to reach for out of habit; they can cause parse
  errors and serve no purpose in diagram XML this project ships.

## When a convention doc and the installed shape library disagree

Don't silently guess or silently deviate. This project's shape-mapping sections have repeatedly
diverged from spec-plausible attribute names (see `bpmn-conventions.md`'s note on why
`search_shapes`/general knowledge isn't trustworthy here) — when a real render disagrees with
what a conventions doc says, fix the doc as part of finishing the task, with a note on what was
wrong and how it was verified. The conventions docs are living records of hands-on findings, not
fixed reference material.
