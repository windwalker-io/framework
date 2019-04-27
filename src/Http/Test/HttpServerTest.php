<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 SMS Taiwan, Inc.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\HttpServer;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Test\Stub\StubOutput;

/**
 * Test class of Server
 *
 * @since 3.0
 */
class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var HttpServer
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
     * createServer
     *
     * @param  callable $handler
     *
     * @return HttpServer
     */
    protected function createServerFromGlobals($handler)
    {
        return HttpServer::createFromGlobals(
            $handler,
            // server
            [
                'foo' => 'bar',
            ],
            // query
            [
                'flower' => 'sakura',
            ],
            // post
            [
                'name' => 'value',
            ],
            // cookies
            [
                'hello' => 'world',
            ],
            // files
            []
        );
    }

    /**
     * Method to test createFromGlobals().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::createFromGlobals
     */
    public function testCreateFromGlobals()
    {
        $server = $this->createServerFromGlobals(
            function () {
            }
        );

        $this->assertEquals(['foo' => 'bar'], $server->getRequest()->getServerParams());
        $this->assertEquals(['flower' => 'sakura'], $server->getRequest()->getQueryParams());
        $this->assertEquals(['name' => 'value'], $server->getRequest()->getParsedBody());
        $this->assertEquals(['hello' => 'world'], $server->getRequest()->getCookieParams());
    }

    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::create
     */
    public function testCreate()
    {
        $case = $this;

        $server = HttpServer::create(
            function (ServerRequestInterface $request, ResponseInterface $response, $finalHandler) use ($case) {
            },
            new ServerRequest(),
            new Response()
        );

        $this->assertTrue($server instanceof HttpServer);
    }

    /**
     * Method to test listen().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::listen
     */
    public function testListen()
    {
        $handler = function (ServerRequestInterface $request, ResponseInterface $response, $finalHandler) {
            return $response->getBody()->write('Flower');
        };

        $server = $this->createServerFromGlobals($handler);
        $server->setOutput(new StubOutput());

        $this->expectOutputString('Flower');

        $server->listen();
    }

    /**
     * testListenWithFinalHandler
     *
     * @return  void
     */
    public function testListenWithFinalHandler()
    {
        $handler = function (ServerRequestInterface $request, ResponseInterface $response, $finalHandler) {
            return $finalHandler(new \Exception('Hello'), $request, $response);
        };

        $server = $this->createServerFromGlobals($handler);
        $server->setOutput(new StubOutput());

        $this->expectOutputString('Exception: Hello');

        $server->listen(
            function (\Exception $e, $request, ResponseInterface $response) {
                $response->getBody()->rewind();

                return $response->getBody()->write(get_class($e) . ': ' . $e->getMessage());
            }
        );
    }

    /**
     * Method to test getHandler().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::getHandler
     */
    public function testGetHandler()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setHandler().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::setHandler
     * @TODO   Implement testSetHandler().
     */
    public function testSetHandler()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getRequest().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::getRequest
     * @TODO   Implement testGetRequest().
     */
    public function testGetRequest()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setRequest().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::setRequest
     * @TODO   Implement testSetRequest().
     */
    public function testSetRequest()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getOutput().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::getOutput
     * @TODO   Implement testGetOutput().
     */
    public function testGetOutput()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setOutput().
     *
     * @return void
     *
     * @covers \Windwalker\Http\HttpServer::setOutput
     * @TODO   Implement testSetOutput().
     */
    public function testSetOutput()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
