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
 * Renders stored sheet-music sources wherever format_text() runs.
 *
 * @package    filter_sheetmusic
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_sheetmusic;

use local_sheetmusic\local\formats;
use local_sheetmusic\local\source;

/**
 * Renders stored sheet-music sources wherever format_text() runs.
 *
 * Scores are stored as the text content of a pre element carrying a sheetmusic class token,
 * because HTMLPurifier strips data attributes but preserves pre text byte for byte. See
 * RELATIONS.md section A for the full stored-content contract.
 *
 * This filter deliberately does no engraving. Moodle caches no filtered text, so filter() runs
 * on every page view of every score and must stay cheap; the engraving happens client-side.
 *
 * @package    filter_sheetmusic
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {
    /** @var string The marker that has to appear in the text before any work is done. */
    private const MARKER = 'sheetmusic';

    /** @var bool Whether the loader has already been queued for this request. */
    private static bool $jsqueued = false;

    /**
     * Queue the client-side loader once per request.
     *
     * @param \moodle_page $page The page the filter is running on.
     * @param \context $context The context being filtered.
     * @return void
     */
    public function setup($page, $context) {
        if (self::$jsqueued) {
            return;
        }
        self::$jsqueued = true;
        $page->requires->js_call_amd('filter_sheetmusic/loader', 'init');
    }

    /**
     * Replace stored score sources with render placeholders.
     *
     * @param string $text The text to filter.
     * @param array $options The filter options.
     * @return string The filtered text.
     */
    public function filter($text, array $options = []) {
        // Cheap bail-out: the overwhelming majority of text on a Moodle site has no score in it.
        if (!is_string($text) || $text === '' || strpos($text, self::MARKER) === false) {
            return $text;
        }

        // Split out the regions where a score block is being shown rather than used. The
        // delimiters are captured so they can be reassembled untouched.
        $pattern = '/(<code\b.*?<\/code>|<script\b.*?<\/script>|<textarea\b.*?<\/textarea>)/is';
        $chunks = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($chunks === false) {
            return $text;
        }

        foreach ($chunks as $index => $chunk) {
            // Odd indices are the captured delimiters, which are left exactly as they were.
            if ($index % 2 === 1) {
                continue;
            }
            $chunks[$index] = $this->replace_scores($chunk);
        }

        return implode('', $chunks);
    }

    /**
     * Replace every score block in one chunk of text.
     *
     * @param string $chunk A region of text known not to be a code or textarea sample.
     * @return string The chunk with score blocks replaced.
     */
    private function replace_scores(string $chunk): string {
        if (strpos($chunk, self::MARKER) === false) {
            return $chunk;
        }

        $pattern = '/<pre\b[^>]*\bclass\s*=\s*"([^"]*)"[^>]*>(.*?)<\/pre>/is';
        $result = preg_replace_callback($pattern, function (array $matches): string {
            return $this->render_block($matches[0], $matches[1], $matches[2]);
        }, $chunk);

        return $result ?? $chunk;
    }

    /**
     * Turn one matched pre element into a render placeholder.
     *
     * Anything that is not a recognised score block is returned unchanged, which is also what
     * makes the filter idempotent: the fallback pre it emits carries no format token, so a
     * second pass over already filtered text produces identical output.
     *
     * @param string $original The whole matched element.
     * @param string $classes The value of the element's class attribute.
     * @param string $body The element's inner HTML.
     * @return string Either a placeholder or the original element.
     */
    private function render_block(string $original, string $classes, string $body): string {
        $tokens = preg_split('/\s+/', trim($classes)) ?: [];
        if (!in_array(self::MARKER, $tokens, true)) {
            return $original;
        }

        $format = null;
        foreach ($tokens as $token) {
            if (str_starts_with($token, self::MARKER . '-')) {
                $candidate = substr($token, strlen(self::MARKER) + 1);
                if (formats::is_storable($candidate)) {
                    $format = $candidate;
                    break;
                }
            }
        }
        if ($format === null) {
            return $original;
        }

        $raw = source::normalise(html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (!source::validate($raw, $format)) {
            return $original;
        }

        $attributes = [
            'class' => 'sheetmusic-block',
            'data-sheetmusic-format' => $format,
            'data-sheetmusic-label' => source::describe($raw, $format),
        ];

        // The source stays visible until the client-side renderer replaces it, so a reader
        // without JavaScript, or with the engine unavailable, still gets readable notation.
        $fallback = \html_writer::tag('pre', s($raw), ['class' => 'sheetmusic-source']);

        return '<nolink>' . \html_writer::tag('div', $fallback, $attributes) . '</nolink>';
    }
}
