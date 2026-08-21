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
     * @return void
     */
    public function test_source_is_escaped(): void {
        $in = '<pre class="sheetmusic sheetmusic-abc">X:1' . "\n" . 'K:G' . "\n" . '% a &amp; b</pre>';
        $out = $this->filter->filter($in);
        $this->assertStringNotContainsString('a & b', $out);
        $this->assertStringContainsString('a &amp; b', $out);
    }
}
