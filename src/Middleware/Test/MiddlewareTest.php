<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Middleware\Test;

use Windwalker\Middleware\EndMiddleware;
use Windwalker\Middleware\Test\Stub\StubCaesarMiddleware;
use Windwalker\Middleware\Test\Stub\StubDataMiddleware;
use Windwalker\Middleware\Test\Stub\StubOthelloMiddleware;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * Test class of Middleware
 *
 * @since 2.0
 */
class MiddlewareTest extends AbstractBaseTestCase
{
    /**
     * Test instance.
     *
     * @var StubCaesarMiddleware
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
        $this->instance = new StubCaesarMiddleware();

        $this->instance->setNext(new StubOthelloMiddleware());
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
     * Method to test getNext().
     *
     * @return void
     *
     * @covers \Windwalker\Middleware\AbstractMiddleware::getNext
     */
    public function testGetNext()
    {
        $this->assertInstanceOf('Windwalker\Middleware\Test\Stub\StubOthelloMiddleware', $this->instance->getNext());
    }

    /**
     * Method to test setNext().
     *
     * @return void
     *
     * @covers \Windwalker\Middleware\AbstractMiddleware::setNext
     */
    public function testSetNext()
    {
        $othello = $this->instance->getNext();

        $othello->setNext(new EndMiddleware());

        $expected = <<<EOF
>>> Caesar
>>> Othello
<<< Othello
<<< Caesar
EOF;

        $this->assertStringDataEquals($expected, $this->instance->execute());
    }

    /**
     * testExecuteWithData
     *
     * @return  void
     */
    public function testExecuteWithData()
    {
        $othello = $this->instance->getNext();

        $othello->setNext($dm = new StubDataMiddleware());
        $dm->setNext(new EndMiddleware());

        $expected = <<<EOF
>>> Caesar
>>> Othello
>>> Hamlet
<<< Hamlet
<<< Othello
<<< Caesar
EOF;

        $this->assertStringSafeEquals($expected, $this->instance->execute((object) ['title' => 'Hamlet']));
    }
}
