/**
 * A resolve hook mapping the engine's module name onto the local stub.
 *
 * Registered by loader.spec.js before it imports the module under test. Without it node cannot
 * resolve `local_sheetmusic/render` from inside this repository, and making it resolvable would
 * mean depending on a sibling checkout - which is the coupling this repo is meant not to have.
 *
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Redirect the engine's render module to the stub, and pass everything else through.
 *
 * @param {string} specifier The module being imported.
 * @param {object} context Node's resolution context.
 * @param {Function} next The next hook in the chain.
 * @returns {object} The resolution result.
 */
export const resolve = (specifier, context, next) => {
    if (specifier === 'local_sheetmusic/render') {
        return next(new URL('./render-stub.js', import.meta.url).href, context);
    }
    return next(specifier, context);
};
