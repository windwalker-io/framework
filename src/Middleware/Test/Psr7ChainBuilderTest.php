<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Middleware\Test;

use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Response\Response;
use Windwalker\Middleware\Chain\ChainBuilder;
use Windwalker\Middleware\Chain\Psr7ChainBuilder;
use Windwalker\Middleware\Psr7InvokableInterface;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * Test class of Psr7ChainBuilder
 *
 * @since 3.0
 */
class Psr7ChainBuilderTest extends AbstractBaseTestCase
{
    /**
     * Test instance.
     *
     * @var Psr7ChainBuilder
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
        $this->instance = new ChainBuilder();
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
     * getHandler
     *
     * @param   string $string
     *
     * @return  \Closure
     */
    protected function getHandler($string)
    {
        return function ($req, ResponseInterface $res, $next) use ($string) {
            $res->getBody()->write(">>> $string\n");

            /** @var Psr7InvokableInterface $next */
            $res = call_user_func($next, $req, $res);

            $res->getBody()->write("<<< $string\n");

            return $res;
        };
    }

    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Middleware\Chain\Psr7ChainBuilder::execute
     */
    public function testExecute()
    {
        $chain = new Psr7ChainBuilder();
        $chain->add($this->getHandler('Othello'));
        $chain->add($this->getHandler('Caesar'));

        $res = $chain->execute(new ServerRequest(), new Response());

        $data = "
>>> Caesar
>>> Othello
<<< Othello
<<< Caesar";

        $this->assertStringSafeEquals($data, $res->getBody()->__toString());

        // Test add by array, will be DESC sorting
        $chain = new Psr7ChainBuilder(
            [
                $this->getHandler('Othello'),
                $this->getHandler('Caesar'),
            ], Psr7ChainBuilder::SORT_DESC
        );

        $res = $chain->execute(new ServerRequest(), new Response());

        $data = "
>>> Othello
>>> Caesar
<<< Caesar
<<< Othello";

        $this->assertStringSafeEquals($data, $res->getBody()->__toString());
    }
}
