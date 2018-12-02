<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Helper\ApplicationHelper;

/**
 * Test class of ApplicationHelper
 *
 * @since 2.0
 */
class ApplicationHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Method to test isAscii().
     *
     * @return void
     *
     * @covers \Windwalker\Application\Helper\ApplicationHelper::isAscii
     */
    public function testIsAscii()
    {
        $this->assertTrue(ApplicationHelper::isAscii('Shakespeare'));
        $this->assertFalse(ApplicationHelper::isAscii('莎士比亞 Shakespeare'));
    }
}
