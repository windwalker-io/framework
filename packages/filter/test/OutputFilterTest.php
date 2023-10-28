<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\OutputFilter;
use Windwalker\Test\Traits\DOMTestTrait;

class OutputFilterTest extends TestCase
{
    use DOMTestTrait;

    /**
     * @see  OutputFilter::safeHTML
     */
    #[DataProvider('safeHtmlProvider')]
    public function testSafeHTML(int $flags, string $expected): void
    {
        $html = <<<HTML
<p>A</p>
<img src="foo.jpg" alt="foo">
<p>B</p>
<iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
<p>C</p>
<script>const foo = 123;</script>
<p>D</p>
<style>.foo { color: red; }</style>
<p>E</p>
<link rel="stylesheet" href="foo.css"/>
<p>F</p>
HTML;

        self::assertHtmlFormatEquals(
            $expected,
            OutputFilter::safeHTML($html, $flags)
        );
    }

    public static function safeHtmlProvider(): array
    {
        return [
            [
                OutputFilter::KEEP_IMAGES,
                <<<HTML
                <p>A</p>
                <img src="foo.jpg" alt="foo">
                <p>B</p>
                <p>C</p>
                <p>D</p>
                <p>E</p>
                <p>F</p>
                HTML
            ],
            [
                OutputFilter::KEEP_IFRAMES,
                <<<HTML
                <p>A</p>
                <p>B</p>
                <iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                <p>C</p>
                <p>D</p>
                <p>E</p>
                <p>F</p>
                HTML
            ],
            [
                OutputFilter::KEEP_STYLES | OutputFilter::KEEP_SCRIPTS | OutputFilter::KEEP_LINKS,
                <<<HTML
                <p>A</p>
                <p>B</p>
                <p>C</p>
                <script>const foo = 123;</script>
                <p>D</p>
                <style>.foo { color: red; }</style>
                <p>E</p>
                <link rel="stylesheet" href="foo.css"/>
                <p>F</p>
                HTML
            ],
        ];
    }
}
