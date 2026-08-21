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

## Licence

GPL v3 or later.
