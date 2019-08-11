<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Router\Test\Matcher;

use Windwalker\Router\Matcher\BinaryMatcher;
use Windwalker\Router\Route;

/**
 * Test class of BinaryMatcher
 *
 * @since 2.0
 */
class BinaryMatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var BinaryMatcher
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
        $this->instance = new BinaryMatcher();
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
     * Method to test match().
     *
     * @return void
     *
     * @covers \Windwalker\Router\Matcher\BinaryMatcher::match
     */
    public function testMatch()
    {
        $routes = file_get_contents(__DIR__ . '/../fixtures/routes.txt');

        $routes = explode("\n", trim($routes));

        $routes = array_map(
            function ($route) {
                return new Route(trim($route), trim($route), ['_return' => $route]);
            },
            $routes
        );

        $matched = $this->instance->setRoutes($routes)
            ->match('/corge/quux/qux');

        $this->assertEquals('/corge/quux/qux', $matched->getName());

        $this->instance->getCount();
    }
}
