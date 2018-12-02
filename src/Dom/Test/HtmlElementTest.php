<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Helper\DomHelper;
use Windwalker\Dom\HtmlElement;

/**
 * Test class of HtmlElement
 *
 * @since 2.0
 */
class HtmlElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * domTestCase
     *
     * @return  array
     */
    public function domTestCase()
    {
        return [
            [
                'case1_anchor_single_tag',
                '<a />',
                'a',
                null,
                [],
                false,
            ],
            [
                'case2_div',
                '<div>Some Data</div>',
                'div',
                'Some Data',
                [],
                false,
            ],
            [
                'case3_div_no_content',
                '<div id="foo" class="bar"></div>',
                'div',
                null,
                ['id' => 'foo', 'class' => 'bar'],
                false,
            ],
            [
                'case4_ul',
                '<ul id="foo" class="bar">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </ul>',
                'ul',
                new HtmlElement('option', 'Yes', ['value' => 1])
                . new HtmlElement('option', 'No', ['value' => 0]),
                ['id' => 'foo', 'class' => 'bar'],
                false,
            ],
            [
                'case5_force_paired',
                '<a></a>',
                'a',
                null,
                [],
                true,
            ],
            [
                'case6_ul',
                '<hr />',
                'hr',
                null,
                [],
                false,
            ],
            [
                'case7_empty_content',
                '<hr></hr>',
                'hr',
                '',
                [],
                false,
            ],
        ];
    }

    /**
     * Method to test create().
     *
     * @param string  $name
     * @param string  $expect
     * @param string  $tag
     * @param string  $content
     * @param array   $attribs
     * @param boolean $forcePaired
     *
     * @return void
     *
     * @covers       Windwalker\Dom\Builder\HtmlBuilder::create
     *
     * @dataProvider domTestCase
     */
    public function testCreate($name, $expect, $tag, $content, $attribs, $forcePaired)
    {
        $element = new HtmlElement($tag, $content, $attribs, $forcePaired);

        $this->assertEquals(
            DomHelper::minify($expect),
            DomHelper::minify($element->toString($forcePaired)),
            'Dom build case fail: ' . $name
        );
    }
}
