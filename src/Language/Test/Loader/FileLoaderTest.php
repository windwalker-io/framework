<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Test\Loader;

use Windwalker\Language\Loader\FileLoader;

/**
 * Test class of FileLoader
 *
 * @since 2.0
 */
class FileLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var FileLoader
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
        $this->instance = new FileLoader();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Method to test load().
     *
     * @return void
     *
     * @covers \Windwalker\Language\Loader\FileLoader::load
     */
    public function testLoad()
    {
        $data = <<<DATA
{
    "windwalker" : {
        "language-test" : {
            "sakura" : "Sakura",
            "olive" : "Olive"
        }
    }
}
DATA;

        $this->assertJsonStringEqualsJsonString($this->instance->load(__DIR__ . '/../fixtures/json/en-GB.json'), $data);
    }
}
