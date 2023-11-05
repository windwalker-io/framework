<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\IniFormat;

/**
 * Test class of IniFormat
 *
 * @since 2.0
 */
class IniFormatTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var IniFormat
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
        $this->instance = new IniFormat();
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
     * Method to test objectToString().
     *
     * @return void
     */
    public function testDump()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test stringToObject().
     *
     * @return void
     */
    public function testParse()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
