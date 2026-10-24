# Changelog

Release notes for this version of `filter_sheetmusic`.
The full history is in [`changelog.md`](changelog.md).

## [0.3.0] - 2026-10-24

### Added

- **A score's own staff size and play-button choice are honoured.** Scores written with
  `tiny_sheetmusic` 0.2.0 can choose a staff size and show or hide their play button. The filter
  reads those choices and falls back to the site settings in `local_sheetmusic` for scores that
  make none.

### Changed

- Requires `local_sheetmusic` 0.3.0. Upgrade the three sheet music plugins together.

Scores stored before this version render as before, apart from the fixed sizing that
`local_sheetmusic` 0.3.0 introduces. No database changes.
