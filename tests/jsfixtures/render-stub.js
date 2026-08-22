/**
 * Stands in for local_sheetmusic/render so the loader can be tested on its own.
 *
 * The filter repo must stay independently cloneable, so it cannot resolve a module belonging to
 * the engine repo. The loader's whole contract is which function it calls and when, which is
 * exactly what a stub can check.
 *
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @type {ParentNode[]} Every root observe() has been handed, in order. */
export const observed = [];

/**
 * Record a call.
 *
 * @param {ParentNode} root The subtree the loader asked to have watched.
 * @returns {Promise<void>}
 */
export const observe = (root) => {
    observed.push(root);
    return Promise.resolve();
};
