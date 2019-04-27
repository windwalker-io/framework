<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Test\Format;

use Windwalker\Language\Format\PhpFormat;

/**
 * Test class of PhpFormat
 *
 * @since 2.0
 */
class PhpFormatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var PhpFormat
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
        $this->instance = new PhpFormat();
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
     * Method to test parse().
     *
     * @return void
     *
     * @covers \Windwalker\Language\Format\PhpFormat::parse
     */
    public function testParse()
    {
        $data = include __DIR__ . '/../fixtures/php/en-GB.php';

        $this->assertArrayHasKey('WINDWALKER_LANGUAGE_TEST_SAKURA', $this->instance->parse($data));
    }
}
