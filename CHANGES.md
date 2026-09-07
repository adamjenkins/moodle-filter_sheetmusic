# Changelog

All notable changes to `filter_sheetmusic` are documented here.
The full history is in [`changelog.md`](changelog.md).

## [0.2.0] - 2026-09-07

### Added

- Score placeholders now carry the site's display settings, so a reader can play a score and so
  the staff-size setting takes effect. The values ride on the placeholder rather than being
  fetched by the client, because the suite has no web service and `format_text()` runs in places
  from which the client could make no request of its own.

### Requirements

- Now requires `local_sheetmusic` 0.2.0 or later.

## [0.1.0] - 2026-08-22

Initial release: renders stored sheet music wherever Moodle displays text, and leaves the source
readable on the page when the filter is off or JavaScript is unavailable.
