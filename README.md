# filter_sheetmusic

Renders sheet music wherever Moodle displays text — pages, books, forum posts, assignment
descriptions, quiz questions and feedback, glossary entries.

A score is stored as the text content of a `<pre>` element:

```html
<pre class="sheetmusic sheetmusic-abc">X:1
M:4/4
K:G
|GABc dedB|</pre>
```

With the filter switched off, that source stays on the page as readable notation rather than
disappearing — the content survives the plugin.

## Requirements

Moodle 4.5 or later, and `local_sheetmusic`.

## Design documents

Comments in this plugin refer to `DESIGN.md`, `RELATIONS.md` and the `P0-FINDINGS-*` notes by
bare name. Those are the sheet music suite's development records and are deliberately not shipped
inside the plugin: they cover all three plugins together and change on a different rhythm from the
code. `RELATIONS.md` in particular is the normative contract between the plugins - the stored
`<pre class="sheetmusic sheetmusic-abc">` format, the engine's public module API, and the editor
and filter coupling - and is the document to read before changing either side of a boundary.

They are kept with the suite's development documentation alongside the source repositories rather
than in this one.

## Licence

GPL v3 or later.
