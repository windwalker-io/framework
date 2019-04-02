<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Builder\HtmlBuilder;
use Windwalker\Dom\Helper\DomHelper;

/**
 * Test class of HtmlBuilder
 *
 * @since 2.0
 */
class HtmlBuilderTest extends \PHPUnit\Framework\TestCase
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
                HtmlBuilder::create('option', 'Yes', ['value' => 1])
                . HtmlBuilder::create('option', 'No', ['value' => 0]),
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
            [
                'case8_attr_only_tag',
                '<video controls muted></video>',
                'video',
                '',
                ['controls' => true, 'muted' => true],
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
     * @covers       \Windwalker\Dom\Builder\HtmlBuilder::create
     *
     * @dataProvider domTestCase
     */
    public function testCreate($name, $expect, $tag, $content, $attribs, $forcePaired)
    {
        $this->assertEquals(
            DomHelper::minify($expect),
            DomHelper::minify(HtmlBuilder::create($tag, $content, $attribs, $forcePaired)),
            'Dom build case fail: ' . $name
        );
    }

    /**
     * testPrepareAttributes
     *
     * @return  void
     *
     * @covers \Windwalker\Dom\Builder\HtmlBuilder::buildAttributes
     */
    public function testBuildAttributes()
    {
        $attrs = [
            'foo' => 'bar',
            'data' => true,
            'bar' => false,
            'empty' => '',
            'selected' => true,
            'checked' => false,
        ];

        $this->assertEquals(' foo="bar" data empty="" selected="selected"', HtmlBuilder::buildAttributes($attrs));
    }
}
