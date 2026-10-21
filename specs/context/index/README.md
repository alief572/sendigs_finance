# The Index — three files, three questions

The index exists so that a session **greps instead of explores**. Exploration
costs a dozen file reads and still misses things; a grep against a file designed
to be grepped costs one and does not.

| Question | File | Maintained |
|---|---|---|
| Where does X live, and what does it touch? | `inventory.md` | **Generated** |
| The ticket says "{business term}" — what is that here? | `vocabulary.md` | By hand |
| Who owns this behaviour, and why is it like that? | `map.md` | By hand |

The split is not tidiness. **Only one of the three can be generated**, and
pretending otherwise is how index files rot:

- A scanner can list every class, file or entity and the edges between them. It
  cannot know that users call it one thing and the code another, and it cannot
  know *why* a boundary sits where it does.
- Conversely, a hand-maintained inventory is stale within a month of the project
  moving fast, and nobody notices, because nobody reads an inventory end to end.

So: generate the inventory, hand-write the two things a generator cannot know,
and never duplicate one file's content into another.

## `inventory.md` — generated

The contract a generator has to meet (one line per thing, grepped not read, edges from the
source, refuses on the unclassified, one definition of "module") is in
`context/guide/building-the-layers.md`.

## `vocabulary.md` — business language → our names

Read it before searching for anything by its business name. Its most valuable
section is the **traps** table: terms where the obvious guess is wrong, where
the plural is a different concept from the singular, or where one word means
three things. Those are exactly the cases where grep returns nothing and a
session concludes the feature does not exist.

A term goes in the first time somebody has to ask what it maps to. That question
is the signal, and it will be asked again.

## `map.md` — ownership and the why

Prose pointers: what owns a behaviour, what a diagram is authoritative for, and
the reasoning a scanner cannot produce. Deliberately **not machine-parsed** —
see the warning at the top of the file, which exists because the original
`.yaml` extension made readers expect a config file.

Add a row when something gains a fact worth recording, not because a file was
created. `map.md` is not an inventory.
