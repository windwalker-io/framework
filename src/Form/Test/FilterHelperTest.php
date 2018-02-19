<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Test;

use Windwalker\Form\FilterHelper;

/**
 * Test class of FilterHelper
 *
 * @since 2.0
 */
class FilterHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * setUp
     *
     * @return  void
     */
    protected function setUp()
    {
        FilterHelper::reset();
    }

    /**
     * tearDown
     *
     * @return  void
     */
    protected function tearDown()
    {
        FilterHelper::reset();
    }

    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Form\FilterHelper::create
     */
    public function testCreate()
    {
        $filter = FilterHelper::create('mock');

        $this->assertInstanceOf('Windwalker\\Form\\Filter\\MockFilter', $filter);

        $filter = FilterHelper::create('email');

        $this->assertInstanceOf('Windwalker\\Form\\Filter\\DefaultFilter', $filter);

        FilterHelper::addNamespace('Windwalker\\Form\\Test\\Stub');

        $filter = FilterHelper::create('stub');

        $this->assertInstanceOf('Windwalker\\Form\\Test\\Stub\\StubFilter', $filter);
    }

    /**
     * testCreateByClassName
     *
     * @return  void
     */
    public function testCreateByClassName()
    {
        $filter = FilterHelper::create('Windwalker\\Form\\Test\\Stub\\StubFilter');

        $this->assertInstanceOf('Windwalker\\Form\\Test\\Stub\\StubFilter', $filter);
    }
}
