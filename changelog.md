# Changelog

All notable changes to `filter_sheetmusic` are documented here.
This project adheres to [Semantic Versioning](https://semver.org/).

## [0.2.2] - 2026-10-04

### Fixed

- A score cut short by a summary (the assignment online-text summary shortens a submission before
  filtering it) is no longer engraved as if it were complete: its source is shown with a note to
  open the full text, where the whole score is engraved.

### Changed

- Maturity raised from `MATURITY_ALPHA` to `MATURITY_BETA`.
- `composer.json` uses a caret constraint for `moodle/moodle` (`^4.5 || ^5.0`, was `>=4.5 <5.4`),
  so new Moodle 5.x releases are not excluded.
- CI tests `MOODLE_503_STABLE` (blocking) instead of moodle.git `main`, now that Moodle 5.3 is
  released.

## [0.2.1] - 2026-10-04

### Added

- `composer.json` (package `adamjenkins/moodle-filter_sheetmusic`, type `moodle-filter`),
  requiring `moodle/moodle` `>=4.5 <5.4` and `adamjenkins/moodle-local_sheetmusic` `>=0.2 <1.0`,
  matching `$plugin->supported` and the `local_sheetmusic` dependency in `version.php`.

### Changed

- Declare Moodle 5.3 support.

## [0.2.0] - 2026-09-07

### Added

- Score placeholders carry the site's display settings (`data-sheetmusic-play`,
  `data-sheetmusic-scale`), which is what lets a reader play a score and what makes the
  staff-size setting take effect. They ride on the placeholder because the suite has no web
  service and `format_text()` runs where the client could make no request of its own.

### Changed

- Requires `local_sheetmusic` 0.2.0 or later.

## [0.1.0] - 2026-08-22

### Added

- Initial release. Renders stored sheet music wherever `format_text()` runs — pages, books,
  labels, forum posts, assignment descriptions, quiz questions and feedback, glossary entries.
- Scores are stored as the text content of a `<pre class="sheetmusic sheetmusic-abc">` element,
  which survives HTMLPurifier, the TinyMCE round trip, and backup and restore, byte for byte.
- The filter does no engraving and makes no database query: Moodle caches no filtered text, so it
  runs on every view of every score and stays a string operation. Code, script and textarea
  regions are excluded, and the filter is idempotent.
- Scores are engraved as the reader scrolls to them rather than all at once, and an oversized
  source is refused rather than handed to the engine.
- With the filter off, or with JavaScript unavailable, the score source stays on the page as
  readable notation instead of disappearing — the content survives the plugin.
