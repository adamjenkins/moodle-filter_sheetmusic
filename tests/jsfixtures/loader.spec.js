/**
 * Unit tests for filter_sheetmusic/loader.
 *
 * The loader is the only client-side code this plugin ships. It does one thing - hand every
 * subtree carrying filtered content to the engine's observer, on load and again whenever Moodle
 * inserts more - and it is the reason a score in a quiz review or an inline forum reply renders
 * at all. The engine's render module is stubbed by resolve-stub.mjs so that this repository does
 * not have to resolve a module belonging to another one.
 *
 * Run with: node tests/jsfixtures/loader.spec.js
 *
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import assert from 'node:assert';
import module from 'node:module';
import './dom.js';

module.register('./resolve-stub.mjs', import.meta.url);

const {observed} = await import('./render-stub.js');
const {init} = await import('filter_sheetmusic/loader');

/**
 * Build a subtree containing one filtered score placeholder.
 *
 * @returns {HTMLElement} The subtree.
 */
const subtree = () => {
    const el = document.createElement('div');
    el.innerHTML = '<div class="sheetmusic-block"><pre class="sheetmusic-source">X:1</pre></div>';
    return el;
};

init();
assert.deepStrictEqual(observed, [document], 'the whole document is watched on init');

// Quiz review, inline forum replies and the drawers all insert already filtered HTML after page
// load. Without this listener those scores stay as raw source, so the event has to be wired.
const inserted = subtree();
document.dispatchEvent(new window.CustomEvent('core_filters/contentUpdated', {
    detail: {nodes: [inserted]},
}));
assert.strictEqual(observed.length, 2, 'inserted content is watched too');
assert.strictEqual(observed[1], inserted, 'and it is the inserted node that gets watched');

// A payload carrying nothing usable must not throw and must not queue work.
document.dispatchEvent(new window.CustomEvent('core_filters/contentUpdated', {detail: {}}));
document.dispatchEvent(new window.CustomEvent('core_filters/contentUpdated', {
    detail: {nodes: [null, {}, 'not a node']},
}));
assert.strictEqual(observed.length, 2, 'a malformed contentUpdated payload is ignored quietly');

const many = [subtree(), subtree()];
document.dispatchEvent(new window.CustomEvent('core_filters/contentUpdated', {detail: {nodes: many}}));
assert.strictEqual(observed.length, 4, 'every inserted node is watched');

// eslint-disable-next-line no-console
console.log('loader.spec.js: all assertions passed');
