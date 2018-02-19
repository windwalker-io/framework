<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\String\Test;

use Windwalker\String\SimpleTemplate;

/**
 * Test class of SimpleTemplate
 *
 * @since 3.0
 */
class SimpleTemplateTest extends \PHPUnit\Framework\TestCase
{
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
     * Method to test render().
     *
     * @return void
     *
     * @covers \Windwalker\String\SimpleTemplate::render
     */
    public function testRender()
    {
        $data['foo']['bar']['baz'] = 'Flower';

        $this->assertEquals('This is Flower', SimpleTemplate::render('This is {{ foo.bar.baz }}', $data));
        $this->assertEquals('This is ', SimpleTemplate::render('This is {{ foo.yoo }}', $data));
        $this->assertEquals('This is Flower', SimpleTemplate::render('This is [ foo.bar.baz ]', $data, ['[', ']']));
    }
}
