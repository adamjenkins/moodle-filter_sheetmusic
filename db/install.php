<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Install-time setup for filter_sheetmusic.
 *
 * @package    filter_sheetmusic
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Enable the sheet music filter site-wide on install.
 *
 * @return void
 */
function xmldb_filter_sheetmusic_install() {
    global $DB;

    filter_set_global_state('sheetmusic', TEXTFILTER_ON, 0);

    // Run before every other filter.
    //
    // Score sources are plain text, and core's phrase filters (activitynames, glossary,
    // urltolink) do not treat <pre> as a region to leave alone — their ignore list covers
    // <nolink>, <script>, <textarea>, <select> and <a>, but not <pre> (lib/filterlib.php,
    // filter_phrases()). A phrase filter running first would rewrite text inside a stored
    // score and corrupt the notation. Going first means this filter's output, which is
    // wrapped in <nolink>, is already protected by the time they run.
    $guard = 0;
    while ($DB->get_field('filter_active', 'sortorder', ['filter' => 'sheetmusic', 'contextid' => 1]) > 1) {
        filter_set_global_state('sheetmusic', TEXTFILTER_ON, -1);
        if (++$guard > 100) {
            break;
        }
    }
}
