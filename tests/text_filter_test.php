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
 * Tests for the sheet music text filter.
 *
 * @package    filter_sheetmusic
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_sheetmusic;

/**
 * Tests for the sheet music text filter.
 *
 * @package    filter_sheetmusic
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_sheetmusic\text_filter
 */
final class text_filter_test extends \advanced_testcase {
    /** @var text_filter The filter under test. */
    private text_filter $filter;

    /**
     * Create a filter bound to the system context.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->filter = new text_filter(\context_system::instance(), []);
    }

    /**
     * Build a stored score block the way the editor writes one.
     *
     * @param string $format The score format token.
     * @return string The stored HTML.
     */
    private function stored(string $format = 'abc'): string {
        $body = match ($format) {
            'musicxml' => s('<score-partwise version="4.0"><part-list/></score-partwise>'),
            default => 'X:1' . "\n" . 'M:4/4' . "\n" . 'K:G' . "\n" . '|GABc dedB|',
        };
        return '<pre class="sheetmusic sheetmusic-' . $format . '">' . $body . '</pre>';
    }

    /**
     * A stored score becomes a render placeholder that still carries its source.
     *
     * @return void
     */
    public function test_score_is_replaced(): void {
        $out = $this->filter->filter($this->stored());
        $this->assertStringContainsString('sheetmusic-block', $out);
        $this->assertStringContainsString('data-sheetmusic-format="abc"', $out);
        $this->assertStringContainsString('<nolink>', $out);
        $this->assertStringContainsString('X:1', $out);
        $this->assertStringContainsString('K:G', $out);
    }

    /**
     * The placeholder carries an accessible label describing the score.
     *
     * @return void
     */
    public function test_placeholder_has_accessible_label(): void {
        $out = $this->filter->filter($this->stored());
        $this->assertMatchesRegularExpression('/data-sheetmusic-label="[^"]+"/', $out);
    }

    /**
     * Filtering already filtered text must not render twice.
     *
     * @return void
     */
    public function test_filter_is_idempotent(): void {
        $once = $this->filter->filter($this->stored());
        $this->assertSame($once, $this->filter->filter($once));
    }

    /**
     * Text with no marker is returned byte for byte.
     *
     * @return void
     */
    public function test_no_marker_is_untouched(): void {
        $in = '<p>Just some prose about music, mentioning no scores at all.</p>';
        $this->assertSame($in, $this->filter->filter($in));
    }

    /**
     * A score shown as a code sample must be left alone.
     *
     * @return void
     */
    public function test_code_regions_are_excluded(): void {
        $in = '<code>' . $this->stored() . '</code>';
        $this->assertStringNotContainsString('sheetmusic-block', $this->filter->filter($in));
    }

    /**
     * A score inside a textarea must be left alone.
     *
     * @return void
     */
    public function test_textarea_regions_are_excluded(): void {
        $in = '<textarea>' . $this->stored() . '</textarea>';
        $this->assertStringNotContainsString('sheetmusic-block', $this->filter->filter($in));
    }

    /**
     * MusicXML is recognised from the outset, so imported scores need no migration later.
     *
     * @return void
     */
    public function test_musicxml_format_is_recognised(): void {
        $out = $this->filter->filter($this->stored('musicxml'));
        $this->assertStringContainsString('data-sheetmusic-format="musicxml"', $out);
    }

    /**
     * An unknown format token is ignored rather than rendered.
     *
     * @return void
     */
    public function test_unknown_format_is_ignored(): void {
        $out = $this->filter->filter($this->stored('klingon'));
        $this->assertStringNotContainsString('sheetmusic-block', $out);
    }

    /**
     * Several scores in one page are all rendered.
     *
     * @return void
     */
    public function test_multiple_scores(): void {
        $in = $this->stored() . '<p>and then</p>' . $this->stored();
        $this->assertSame(2, substr_count($this->filter->filter($in), 'sheetmusic-block'));
    }

    /**
     * The source is HTML escaped on the way out.
     *
     * The body is written the way HTMLPurifier stores it - the author typed the characters and
     * the purifier encoded them - so the filter's job is to decode once for the engine and hand
     * the text back encoded, not live.
     *
     * @return void
     */
    public function test_source_is_escaped(): void {
        $body = 'X:1' . "\n" . 'K:G' . "\n"
            . '% &lt;script&gt;alert(1)&lt;/script&gt; &amp; "quoted" 3 &lt; 4' . "\n" . '|GABc|';
        $out = $this->filter->filter('<pre class="sheetmusic sheetmusic-abc">' . $body . '</pre>');

        // Proving the block was rendered is what stops the rest of this test being vacuous: the
        // input is already encoded, so every escaping assertion below is equally satisfied by a
        // filter that returns its input untouched.
        $this->assertStringContainsString('sheetmusic-block', $out);

        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringContainsString('&lt;script&gt;', $out);
        $this->assertStringContainsString('3 &lt; 4', $out);
        $this->assertStringContainsString('&amp;', $out);
        // A literal quote survives purification as a quote, and has to leave here encoded. This
        // is the one assertion the identity function cannot satisfy on its own.
        $this->assertStringContainsString('&quot;quoted&quot;', $out);
    }

    /**
     * Escaping is not something a filter that does nothing can be credited with.
     *
     * A previous version of test_source_is_escaped() used an already-encoded fixture and asserted
     * only on that same encoded form. Because every refusal path returns the original element,
     * a filter that did nothing at all passed it. This pins that hole shut directly.
     *
     * @return void
     */
    public function test_doing_nothing_does_not_count_as_escaping(): void {
        $in = '<pre class="sheetmusic sheetmusic-abc">X:1' . "\n" . 'K:G' . "\n" . '% a &amp; b</pre>';
        $out = $this->filter->filter($in);

        $this->assertNotSame($in, $out, 'the filter must actually transform a valid score');
        $this->assertStringContainsString('sheetmusic-block', $out);
        $this->assertStringContainsString('sheetmusic-source', $out);
    }

    /**
     * A numeric character reference in the source survives the round trip unchanged.
     *
     * Moodle's s() hands numeric references back un-escaped, which would turn a source containing
     * the literal characters "&#60;" into a live reference decoding to "<" before the engine ever
     * saw it. The filter escapes with htmlspecialchars() precisely to avoid that.
     *
     * @return void
     */
    public function test_numeric_character_references_survive_the_round_trip(): void {
        // Stored the way an author who literally typed "&#60;" has it stored after purification.
        $body = 'X:1' . "\n" . 'K:G' . "\n" . '% &amp;#60;not a tag&amp;#62;' . "\n" . '|GABc|';
        $out = $this->filter->filter('<pre class="sheetmusic sheetmusic-abc">' . $body . '</pre>');

        $this->assertStringContainsString('sheetmusic-block', $out);
        $this->assertStringContainsString('&amp;#60;', $out);
        $this->assertStringNotContainsString('&#60;n', $out, 'the reference must not be re-armed');
    }

    /**
     * A pre element that is not a score is handed back byte for byte.
     *
     * @return void
     */
    public function test_non_score_pre_is_untouched(): void {
        $in = '<pre class="language-php">$x = 1; // mentions sheetmusic in passing</pre>';
        $this->assertSame($in, $this->filter->filter($in));
    }

    /**
     * A source past the inline size limit is refused rather than rendered.
     *
     * The limit is not cosmetic: engraving cost is linear in source length and is paid by every
     * reader of the page, on the browser's main thread.
     *
     * @return void
     */
    public function test_oversized_source_is_refused(): void {
        $body = 'X:1' . "\n" . 'K:G' . "\n"
            . str_repeat('|GABc dedB', (int) (\local_sheetmusic\local\source::MAX_INLINE_BYTES / 10) + 64);
        $in = '<pre class="sheetmusic sheetmusic-abc">' . $body . '</pre>';

        $this->assertGreaterThan(\local_sheetmusic\local\source::MAX_INLINE_BYTES, strlen($body));
        $out = $this->filter->filter($in);
        $this->assertStringNotContainsString('sheetmusic-block', $out);
        $this->assertSame($in, $out, 'an oversized block is left exactly as it was found');
    }

    /**
     * The limit applies to the decoded source, not to the stored bytes.
     *
     * A body made of entities is several times longer stored than it is once decoded, and it is
     * the decoded string the engine receives and pays for. Measuring the stored length would
     * refuse scores that are well inside the bound.
     *
     * @return void
     */
    public function test_limit_measures_the_decoded_source(): void {
        // Each "&amp;" is five stored bytes decoding to one, so at 15000 repetitions the body is
        // 75KB as stored and 15KB once decoded: over the limit on the measure that would be wrong
        // and comfortably under it on the measure that is right.
        $body = 'X:1' . "\n" . 'K:G' . "\n" . '% ' . str_repeat('&amp;', 15000) . "\n" . '|GABc|';

        $this->assertGreaterThan(\local_sheetmusic\local\source::MAX_INLINE_BYTES, strlen($body));
        $out = $this->filter->filter('<pre class="sheetmusic sheetmusic-abc">' . $body . '</pre>');
        $this->assertStringContainsString('sheetmusic-block', $out, 'judged on the decoded length');
    }

    /**
     * A source just inside the limit still renders, so the bound is a ceiling and not a cliff.
     *
     * @return void
     */
    public function test_source_just_inside_the_limit_is_rendered(): void {
        $header = 'X:1' . "\n" . 'K:G' . "\n";
        $fill = \local_sheetmusic\local\source::MAX_INLINE_BYTES - strlen($header) - 16;
        $body = $header . str_repeat('|GABc dedB', (int) ($fill / 10));

        $this->assertLessThanOrEqual(\local_sheetmusic\local\source::MAX_INLINE_BYTES, strlen($body));
        $out = $this->filter->filter('<pre class="sheetmusic sheetmusic-abc">' . $body . '</pre>');
        $this->assertStringContainsString('sheetmusic-block', $out);
    }
}
