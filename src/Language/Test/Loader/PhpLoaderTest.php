<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Test\Loader;

use Windwalker\Language\Loader\PhpLoader;

/**
 * Test class of PhpLoader
 *
 * @since 2.0
 */
class PhpLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var PhpLoader
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
        $this->instance = new PhpLoader();
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
     * Method to test load().
     *
     * @return void
     *
     * @covers \Windwalker\Language\Loader\PhpLoader::load
     */
    public function testLoad()
    {
        $data = $this->instance->load(__DIR__ . '/../fixtures/php/en-GB.php');

        $this->assertArrayHasKey('WINDWALKER_LANGUAGE_TEST_FLOWER', $data);
    }
}
