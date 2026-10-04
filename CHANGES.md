# Changelog

Release notes for this version of `filter_sheetmusic`.
The full history is in [`changelog.md`](changelog.md).

## [0.2.2] - 2026-10-04

### Fixed

- A score cut short by a summary (the assignment online-text summary shortens a submission before
  filtering it) is no longer engraved as if it were complete: its source is shown with a note to
  open the full text, where the whole score is engraved.

### Changed

- Maturity is now Beta (was Alpha).
- Installing with Composer no longer caps the Moodle version: `composer.json` now requires
  `moodle/moodle` `^4.5 || ^5.0` (was `>=4.5 <5.4`).
- Continuous integration now tests against the released Moodle 5.3 (`MOODLE_503_STABLE`) instead
  of Moodle's development branch.
