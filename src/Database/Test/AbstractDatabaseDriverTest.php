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
     * Method to test addMiddleware().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\AbstractDatabaseDriver::addMiddleware
     * @TODO   Implement testAddMiddleware().
     */
    public function testAddMiddleware()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getMiddlewares().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\AbstractDatabaseDriver::getMiddlewares
     * @TODO   Implement testGetMiddlewares().
     */
    public function testGetMiddlewares()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test resetMiddlewares().
     *
     * @return void
     *
     * @covers \Windwalker\Database\Driver\AbstractDatabaseDriver::resetMiddlewares
     * @TODO   Implement testResetMiddlewares().
     */
    public function testResetMiddlewares()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * testMiddleware
     *
     * @return  void
     * @throws \ReflectionException
     */
    public function testMiddleware()
    {
        $this->db->addMiddleware(
            function (\stdClass $data, MiddlewareInterface $next) {
                /** @var Query $query */
                $query = $data->query;

                $query->limit(3);

                return $next->execute($data);
            }
        );

        $items = $this->db->setQuery($this->db->getQuery(true)->select('*')->from('#__flower'))->loadAll();

        $this->assertCount(3, $items);
    }

    /**
     * Method to test disconnect().
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testProfilerMiddleware()
    {
        $profiler = [];

        $this->db->addMiddleware(
            new DbProfilerMiddleware(
                function ($db, $data) use (&$profiler) {
                    $profiler['db'] = $db;
                    $profiler['before'] = true;
                },
                function ($db, $data) use (&$profiler) {
                    $profiler['db'] = $db;
                    $profiler['after'] = true;

                    $profiler = array_merge($profiler, (array) $data);
                }
            )
        );

        $this->db->setQuery('SELECT * FROM #__flower')->execute();

        $this->assertSame($this->db, $profiler['db']);

        $this->assertSame('SELECT * FROM ' . static::$dsn['prefix'] . 'flower', (string) $profiler['sql']);

        $this->assertTrue($profiler['before']);
        $this->assertTrue($profiler['after']);
    }
}
