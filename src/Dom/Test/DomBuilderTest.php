<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom\Test;

use Windwalker\Dom\Builder\DomBuilder;

/**
 * Test class of DomBuilder
 *
 * @since 2.0
 */
class DomBuilderTest extends AbstractDomTestCase
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
                'case1',
                '<field />',
                'field',
                null,
                [],
                false,
            ],
            [
                'case2',
                '<field>Some Data</field>',
                'field',
                'Some Data',
                [],
                false,
            ],
            [
                'case3',
                '<field id="foo" class="bar" />',
                'field',
                null,
                ['id' => 'foo', 'class' => 'bar'],
                false,
            ],
            [
                'case4',
                '<field id="foo" class="bar">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </field>',
                'field',
                DomBuilder::create('option', 'Yes', ['value' => 1])
                . DomBuilder::create('option', 'No', ['value' => 0]),
                ['id' => 'foo', 'class' => 'bar'],
                false,
            ],
            [
                'case5_force_paired',
                '<field></field>',
                'field',
                null,
                [],
                true,
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
     * @covers       Windwalker\Dom\Builder\DomBuilder::create
     *
     * @dataProvider domTestCase
     */
    public function testCreate($name, $expect, $tag, $content, $attribs, $forcePaired)
    {
        $this->assertDomFormatEquals(
            $expect,
            DomBuilder::create($tag, $content, $attribs, $forcePaired),
            'Dom build case fail: ' . $name
        );
    }

    /**
     * Method to test quote().
     *
     * @return void
     *
     * @covers \Windwalker\Dom\Builder\DomBuilder::quote
     */
    public function testQuote()
    {
        $this->assertEquals('"foo"', DomBuilder::quote('foo'));
    }

    /**
     * testPrepareAttributes
     *
     * @return  void
     *
     * @covers \Windwalker\Dom\Builder\DomBuilder::buildAttributes
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

        $this->assertEquals(' foo="bar" data empty="" selected', DomBuilder::buildAttributes($attrs));
    }
}
