# Vocabulary — business language to our names

Hand-maintained, and it has to be: this file covers the gap no scanner can
close — **the words the business uses are not the words in the artifacts.**
Somebody told "the client wants the kartu stok screen changed" has to know which
diagram page, which entity, and later which class that is, and grepping the
business term with a space in it finds none of them.

Read this before searching for anything by its business name. Terms are listed
in **the client's language first**, because that is what tickets, elicitation
notes and users actually say — see the domain-language rule in
`context/general-rules.md`.

## Traps — where the obvious guess is wrong

The highest-value section. Fill it with the terms that have already caused a
wrong search, not with everything.

| You hear | It is | Not |
|---|---|---|
| _{business term}_ | _{the artifact and/or code name, with the file it lives in}_ | _{the plausible guess that returns nothing}_ |

Four trap shapes worth watching for, all of which produce a confident wrong
answer rather than an empty result:

- **The name was translated once and not again.** The entity is `Regulation`,
  the table is `regulasi`, and no class is called `Regulasi`.
- **The singular and the plural are different concepts.** One is the type, the
  other is the physical instance, and both exist.
- **One word means three things.** The same term names a model, a screen, and a
  schedule, and only context distinguishes them.
- **It is not an entity at all.** The word everyone says is a column on
  something else, or a derived view, and searching for a table by that name
  finds nothing because there is nothing to find.

## By area

Group by the same areas the workbook's Modules sheet uses, so the vocabulary,
the ERD, and the class diagram cannot disagree about what a module is.

### {Module}

- **{business term}** — {name here}; {where it is defined}
- **{business term}** — {name here}

_Empty. Add a term the first time someone has to ask what it maps to._
