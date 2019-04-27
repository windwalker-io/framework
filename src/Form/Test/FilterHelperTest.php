<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
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
    protected function setUp(): void
    {
        FilterHelper::reset();
    }

    /**
     * tearDown
     *
     * @return  void
     */
    protected function tearDown(): void
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
