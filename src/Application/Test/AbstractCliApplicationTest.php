<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Test\Stub\StubCli;
use Windwalker\Test\TestHelper;

/**
 * Test class of AbstractCliApplication
 *
 * @since 2.0
 */
class AbstractCliApplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var StubCli
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new StubCli();
    }

    /**
     * test__construct
     *
     * @return  void
     * @throws \ReflectionException
     */
    public function test__construct()
    {
        $this->assertInstanceOf(
            'Windwalker\\IO\\Cli\\IO',
            $this->instance->io,
            'Input property wrong type'
        );

        $this->assertInstanceOf(
            'Windwalker\\Structure\\Structure',
            TestHelper::getValue($this->instance, 'config'),
            'Config property wrong type'
        );
    }
}
