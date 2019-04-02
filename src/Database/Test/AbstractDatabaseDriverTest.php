<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 SMS Taiwan, Inc.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Test;

use Windwalker\Database\Middleware\DbProfilerMiddleware;
use Windwalker\Database\Test\Mysql\AbstractMysqlTestCase;
use Windwalker\Middleware\MiddlewareInterface;
use Windwalker\Query\Query;

/**
 * Test class of AbstractDatabaseDriver
 *
 * @since 3.0
 */
class AbstractDatabaseDriverTest extends AbstractMysqlTestCase
{
    /**
     * Method to test disconnect().
     *
     * @return void
     */
    public function testMonitor()
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }
}
