<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filter\Test;

use Windwalker\Filter\InputFilter;
use Windwalker\Filter\Test\Stub\StubThorCleaner;

/**
 * Test class of InputFilter
 *
 * @since 2.0
 */
class InputFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var InputFilter
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new InputFilter();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Produces the array of test cases common to all test runs.
     *
     * @return  array  Two dimensional array of test cases. Each row consists of three values
     *                 The first is the type of input data, the second is the actual input data,
     *                 the third is the expected result of filtering, and the fourth is
     *                 the failure message identifying the source of the data.
     */
    public function casesGeneric()
    {
        $input = '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`' .
            'abcdefghijklmnopqrstuvwxyz{|}~â‚¬â€šÆ’â€žâ€¦â€ â€¡Ë†â€°Å â€¹Å’Å½â€˜â€™â€œâ' .
            '€�â€¢â€“â€”Ëœâ„¢Å¡â€ºÅ“Å¾Å¸Â¡Â¢Â£Â¤Â¥Â' .
            '¦Â§Â¨Â©ÂªÂ«Â¬Â­Â®Â¯Â°Â±Â²Â³Â´ÂµÂ¶Â·' .
            'Â¸Â¹ÂºÂ»Â¼Â½Â¾Â¿Ã€Ã�Ã‚ÃƒÃ„Ã…Ã†Ã‡ÃˆÃ‰ÃŠÃ‹' .
            'ÃŒÃ�ÃŽÃ�Ã�Ã‘Ã’Ã“Ã”Ã•Ã–Ã—Ã˜Ã™ÃšÃ›ÃœÃ�ÃžÃ' .
            'ŸÃ Ã¡Ã¢Ã£Ã¤Ã¥Ã¦Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã' .
            '°Ã±Ã²Ã³Ã´ÃµÃ¶Ã·Ã¸Ã¹ÃºÃ»Ã¼Ã½Ã¾Ã¿';

        return [
            [
                'int_01',
                'int',
                $input,
                123456789,
                'From generic cases',
            ],
            [
                'integer',
                'int',
                $input,
                123456789,
                'From generic cases',
            ],
            [
                'int_02',
                'int',
                'abc123456789abc123456789',
                123456789,
                'From generic cases',
            ],
            [
                'int_03',
                'int',
                '123456789abc123456789abc',
                123456789,
                'From generic cases',
            ],
            [
                'int_04',
                'int',
                'empty',
                0,
                'From generic cases',
            ],
            [
                'int_05',
                'int',
                'ab-123ab',
                -123,
                'From generic cases',
            ],
            [
                'int_06',
                'int',
                '-ab123ab',
                123,
                'From generic cases',
            ],
            [
                'int_07',
                'int',
                '-ab123.456ab',
                123,
                'From generic cases',
            ],
            [
                'int_08',
                'int',
                '456',
                456,
                'From generic cases',
            ],
            [
                'int_09',
                'int',
                '-789',
                -789,
                'From generic cases',
            ],
            [
                'int_10',
                'int',
                -789,
                -789,
                'From generic cases',
            ],
            [
                'uint_1',
                'UINT',
                -789,
                789,
                'From generic cases',
            ],
            [
                'float_01',
                'float',
                $input,
                123456789,
                'From generic cases',
            ],
            [
                'double',
                'double',
                $input,
                123456789,
                'From generic cases',
            ],
            [
                'float_02',
                'float',
                20.20,
                20.2,
                'From generic cases',
            ],
            [
                'float_03',
                'float',
                '-38.123',
                -38.123,
                'From generic cases',
            ],
            [
                'float_04',
                'float',
                'abc-12.456',
                -12.456,
                'From generic cases',
            ],
            [
                'float_05',
                'float',
                '-abc12.456',
                12.456,
                'From generic cases',
            ],
            [
                'float_06',
                'float',
                'abc-12.456abc',
                -12.456,
                'From generic cases',
            ],
            [
                'float_07',
                'float',
                'abc-12 . 456',
                -12,
                'From generic cases',
            ],
            [
                'float_08',
                'float',
                'abc-12. 456',
                -12,
                'From generic cases',
            ],
            [
                'bool_0',
                'bool',
                $input,
                true,
                'From generic cases',
            ],
            [
                'boolean',
                'boolean',
                $input,
                true,
                'From generic cases',
            ],
            [
                'bool_1',
                'bool',
                true,
                true,
                'From generic cases',
            ],
            [
                'bool_2',
                'bool',
                false,
                false,
                'From generic cases',
            ],
            [
                'bool_3',
                'bool',
                '',
                false,
                'From generic cases',
            ],
            [
                'bool_4',
                'bool',
                0,
                false,
                'From generic cases',
            ],
            [
                'bool_5',
                'bool',
                1,
                true,
                'From generic cases',
            ],
            [
                'bool_6',
                'bool',
                null,
                false,
                'From generic cases',
            ],
            [
                'bool_7',
                'bool',
                'false',
                true,
                'From generic cases',
            ],
            [
                'word_01',
                'word',
                $input,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
                'From generic cases',
            ],
            [
                'word_02',
                'word',
                null,
                '',
                'From generic cases',
            ],
            [
                'word_03',
                'word',
                123456789,
                '',
                'From generic cases',
            ],
            [
                'word_04',
                'word',
                'word123456789',
                'word',
                'From generic cases',
            ],
            [
                'word_05',
                'word',
                '123456789word',
                'word',
                'From generic cases',
            ],
            [
                'word_06',
                'word',
                'w123o4567r89d',
                'word',
                'From generic cases',
            ],
            [
                'alnum_01',
                'alnum',
                $input,
                '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
                'From generic cases',
            ],
            [
                'alnum_02',
                'alnum',
                null,
                '',
                'From generic cases',
            ],
            [
                'alnum_03',
                'alnum',
                '~!@#$%^&*()_+abc',
                'abc',
                'From generic cases',
            ],
            [
                'cmd',
                'cmd',
                $input,
                '-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz',
                'From generic cases',
            ],
            [
                'base64',
                'base64',
                $input,
                '+/0123456789=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
                'From generic cases',
            ],
            [
                'array',
                'array',
                [1, 3, 6],
                [1, 3, 6],
                'From generic cases',
            ],
            [
                'path_01',
                'path',
                'images/system',
                'images/system',
                'From generic cases',
            ],
            [
                'path_02',
                'path',
                'http://www.fred.com/josephus',
                '',
                'From generic cases',
            ],
            [
                'user_01',
                'username',
                '&<f>r%e\'d',
                'fred',
                'From generic cases',
            ],
            [
                'user_02',
                'username',
                'fred',
                'fred',
                'From generic cases',
            ],
            [
                'string_01',
                'string',
                '123.567',
                '123.567',
                'From generic cases',
            ],
            [
                'string_single_quote',
                'string',
                "this is a 'test' of ?",
                "this is a 'test' of ?",
                'From generic cases',
            ],
            [
                'string_double_quote',
                'string',
                'this is a "test" of "double" quotes',
                'this is a "test" of "double" quotes',
                'From generic cases',
            ],
            [
                'string_odd_double_quote',
                'string',
                'this is a "test of "odd number" of quotes',
                'this is a "test of "odd number" of quotes',
                'From generic cases',
            ],
            [
                'string_odd_mixed_quote',
                'string',
                'this is a "test\' of "odd number" of quotes',
                'this is a "test\' of "odd number" of quotes',
                'From generic cases',
            ],
            [
                'email_1',
                'email',
                'foo bar-yoo+f/l\\o\'w"er@gmail.com',
                'foobar-yoo+flo\'wer@gmail.com',
                'From generic cases',
            ],
            [
                'url_1',
                'url',
                'http://foo bar\\c.com/flower/sak ura/?foo=b ar&wind=wal+ker',
                'http://foobar\\c.com/flower/sakura/?foo=bar&wind=wal+ker',
                'From generic cases',
            ],
            [
                'raw_01',
                'raw',
                '<script type="text/javascript">alert("foo");</script>',
                '<script type="text/javascript">alert("foo");</script>',
                'From generic cases',
            ],
            [
                'raw_02',
                'raw',
                '<p>This is a test of a html <b>snippet</b></p>',
                '<p>This is a test of a html <b>snippet</b></p>',
                'From generic cases',
            ],
            [
                'raw_03',
                'raw',
                '0123456789',
                '0123456789',
                'From generic cases',
            ],
            [
                'unknown_01',
                '',
                '123.567',
                '123.567',
                'From generic cases',
            ],
            [
                'unknown_02',
                '',
                [1, 3, 9],
                [1, 3, 9],
                'From generic cases',
            ],
            [
                'unknown_03',
                '',
                ["key" => "Value", "key2" => "This&That", "key2" => "This&amp;That"],
                ["key" => "Value", "key2" => "This&That", "key2" => "This&That"],
                'From generic cases',
            ],
            [
                'unknown_04',
                '',
                12.6,
                12.6,
                'From generic cases',
            ],
            [
                'tag_01',
                '',
                '<em',
                'em',
                'From generic cases',
            ],
            [
                'Kill script',
                '',
                '<img src="javascript:alert();" />',
                '<img />',
                'From generic cases',
            ],
            [
                'Nested tags',
                '',
                '<em><strong>Fred</strong></em>',
                '<em><strong>Fred</strong></em>',
                'From generic cases',
            ],
            [
                'Malformed Nested tags',
                '',
                '<em><strongFred</strong></em>',
                '<em>strongFred</strong></em>',
                'From generic cases',
            ],
            [
                'Unquoted Attribute Without Space',
                '',
                '<img height=300>',
                '<img height="300" />',
                'From generic cases',
            ],
            [
                'Unquoted Attribute',
                '',
                '<img height=300 />',
                '<img height="300" />',
                'From generic cases',
            ],
            [
                'Single quoted Attribute',
                '',
                '<img height=\'300\' />',
                '<img height="300" />',
                'From generic cases',
            ],
            [
                'Attribute is zero',
                '',
                '<img height=0 />',
                '<img height="0" />',
                'From generic cases',
            ],
            [
                'Attribute value missing',
                '',
                '<img height= />',
                '<img height="" />',
                'From generic cases',
            ],
            [
                'Attribute without =',
                '',
                '<img height="300" ismap />',
                '<img height="300" />',
                'From generic cases',
            ],
            [
                'Bad Attribute Name',
                '',
                '<br 3bb />',
                '<br />',
                'From generic cases',
            ],
            [
                'Bad Tag Name',
                '',
                '<300 />',
                '',
                'From generic cases',
            ],
            [
                'tracker9725',
                'string',
                '<img class="one two" />',
                '<img class="one two" />',
                'Test for recursion with single tags - From generic cases',
            ],
            [
                'missing_quote',
                'string',
                '<img height="123 />',
                'img height="123 /&gt;"',
                'From generic cases',
            ],
        ];
    }

    /**
     * Method to test clean().
     *
     * @param   string $id
     * @param   string $type    The type of input
     * @param   string $data    The input
     * @param   string $expect  The expected result for this test.
     * @param   string $message The failure message identifying source of test case.
     *
     * @return void
     *
     * @covers       Windwalker\Filter\InputFilter::clean
     *
     * @dataProvider casesGeneric
     */
    public function testClean($id, $type, $data, $expect, $message)
    {
        $this->assertEquals(
            $expect,
            $this->instance->clean($data, $type),
            $message . ': ' . $id . '. Using filter: ' . $type
        );
    }

    /**
     * Method to test getHandler().
     *
     * @return void
     *
     * @covers \Windwalker\Filter\InputFilter::getHandler
     * @TODO   Implement testGetHandler().
     */
    public function testGetAndSetHandler()
    {
        // Iron Man
        $closure = function ($source) {
            return 'Iron Man';
        };

        $this->instance->setHandler('armor', $closure);

        $return = $this->instance->clean('Tony Stark', 'ARMOR');

        $this->assertEquals('Iron Man', $return);

        $handler = $this->instance->getHandler('armor');

        $this->assertInstanceOf('Closure', $handler);

        // Thor
        $this->instance->setHandler('hammer', new StubThorCleaner());

        $return = $this->instance->clean('Thor', 'hammer');

        $this->assertEquals('God', $return);

        $handler = $this->instance->getHandler('hammer');

        $this->assertInstanceOf('Windwalker\\Filter\\Cleaner\\CleanerInterface', $handler);
    }

    /**
     * Method to test getHtmlCleaner().
     *
     * @return void
     *
     * @covers \Windwalker\Filter\InputFilter::getHtmlCleaner
     * @TODO   Implement testGetHtmlCleaner().
     */
    public function testGetHtmlCleaner()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setHtmlCleaner().
     *
     * @return void
     *
     * @covers \Windwalker\Filter\InputFilter::setHtmlCleaner
     * @TODO   Implement testSetHtmlCleaner().
     */
    public function testSetHtmlCleaner()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getDefaultHandler().
     *
     * @return void
     *
     * @covers \Windwalker\Filter\InputFilter::getDefaultHandler
     * @TODO   Implement testGetDefaultHandler().
     */
    public function testGetDefaultHandler()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setDefaultHandler().
     *
     * @return void
     *
     * @covers \Windwalker\Filter\InputFilter::setDefaultHandler
     * @TODO   Implement testSetDefaultHandler().
     */
    public function testSetDefaultHandler()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
