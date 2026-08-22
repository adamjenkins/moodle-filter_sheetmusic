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
 * Finds score placeholders on the page and hands them to the shared engine.
 *
 * @module     filter_sheetmusic/loader
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {observe} from 'local_sheetmusic/render';

/**
 * Watch every score on the page, now and whenever Moodle inserts more filtered content.
 *
 * Deferred rather than immediate: engraving is synchronous WebAssembly costing hundreds of
 * milliseconds a score, and a page routinely carries more scores than fit on a screen, so
 * the work waits until the reader actually reaches one.
 *
 * Quiz review, inline forum replies and the drawers all insert already filtered HTML after
 * page load, so listening for contentUpdated is not optional: without it those scores stay
 * as raw source.
 *
 * @returns {void}
 */
export const init = () => {
    observe(document);

    document.addEventListener('core_filters/contentUpdated', (event) => {
        const nodes = event.detail && event.detail.nodes ? event.detail.nodes : [];
        [...nodes].forEach((node) => {
            if (node && node.querySelectorAll) {
                observe(node);
            }
        });
    });
};
