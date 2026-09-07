# Changelog

All notable changes to `filter_sheetmusic` are documented here.

## [Unreleased]

- Initial development. Nothing released yet.
- Score placeholders now carry the site's display settings as `data-sheetmusic-scale` and
  `data-sheetmusic-play`, from `\local_sheetmusic\local\display`. This is what lets a reader
  play a score and what makes the staff-size setting take effect; the client needs no request of
  its own, which matters because `format_text()` runs in places from which it could make none.
