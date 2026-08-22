/**
 * Minimal DOM bootstrap so the loader can be unit tested under node.
 *
 * Importing this module installs document and window as globals, matching what the module sees
 * in a browser.
 *
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {JSDOM} from 'jsdom';

const dom = new JSDOM('<!doctype html><html><body></body></html>');

global.window = dom.window;
global.document = dom.window.document;
global.Node = dom.window.Node;
global.CustomEvent = dom.window.CustomEvent;

export default dom;
