<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Test\Stub\StubWeb;
use Windwalker\Environment\WebEnvironment;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Output\NoHeaderOutput;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Test\Mock\MockOutput;
use Windwalker\Http\WebHttpServer;

/**
 * Test class of AbstractWebApplication
 *
 * @since 2.0
 */
class AbstractWebApplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var StubWeb
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
        $server['HTTP_HOST'] = 'foo.com';
        $server['HTTP_USER_AGENT'] = 'Mozilla/5.0';
        $server['REQUEST_URI'] = '/index.php';
        $server['SCRIPT_NAME'] = '/index.php';

        $this->instance = new StubWeb(ServerRequestFactory::createFromGlobals($server));
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
     * test__construct
     *
     * @return  void
     */
    public function test__construct()
    {
        $this->assertInstanceOf(
            'Windwalker\Structure\Structure',
            $this->instance->config,
            'Config property wrong type'
        );

        $this->assertInstanceOf(
            'Windwalker\Environment\WebEnvironment',
            $this->instance->environment,
            'Environment property wrong type'
        );

        $this->assertInstanceOf('Windwalker\Http\WebHttpServer', $this->instance->server);

        $this->assertInstanceOf('Windwalker\Http\Request\ServerRequest', $this->instance->request);

        $this->assertInstanceOf('Windwalker\Uri\UriData', $this->instance->uri);
    }

    /**
     * Method to test execute().
     *
     * @return void
     *
     * @covers \Windwalker\Application\AbstractWebApplication::execute
     */
    public function testExecute()
    {
        $this->instance->server->setOutput(new MockOutput());

        ob_start();
        $this->instance->execute();
        ob_end_clean();

        $this->assertEquals('Hello World', $this->instance->server->getOutput()->output);

        $this->assertContains(
            'text/html; charset=utf-8',
            $this->instance->server->getOutput()->message->getHeader('Content-Type')
        );
    }

    /**
     * Method to test __toString().
     *
     * @return void
     *
     * @covers \Windwalker\Application\AbstractWebApplication::__toString
     */
    public function test__toString()
    {
        $this->instance->server->setOutput(new NoHeaderOutput());

        $this->assertEquals('Hello World', (string) $this->instance);
    }

    /**
     * Method to test redirect().
     *
     * @return void
     *
     * @covers \Windwalker\Application\AbstractWebApplication::redirect
     */
    public function testRedirect()
    {
        $this->instance->server->setOutput(new MockOutput());

        $this->instance->redirect('/foo');

        $headers = $this->instance->server->getOutput()->message->getHeaders();

        $array = [
            'Location: http://foo.com/foo',
            'Content-Length: 0',
        ];

        $this->assertEquals($array, HeaderHelper::toHeaderLine($headers));
        $this->assertEquals('HTTP/1.1 303 See Other', $this->instance->server->getOutput()->status);

        // Code
        $this->instance->server->setOutput(new MockOutput());

        $this->instance->redirect('/foo', 307);

        $this->assertEquals('HTTP/1.1 307 Temporary Redirect', $this->instance->server->getOutput()->status);
    }

    /**
     * testGetAndSetServer
     *
     * @return  void
     */
    public function testGetAndSetServer()
    {
        $server = $this->instance->getServer();

        $this->instance->setServer($server2 = WebHttpServer::createFromGlobals('trim'));

        $this->assertNotSame($server, $this->instance->getServer());
        $this->assertSame($server2, $this->instance->getServer());

        $this->assertSame($this->instance->request, $this->instance->getServer()->getRequest());
        $this->assertSame($this->instance->uri, $this->instance->getServer()->getUriData());
    }

    /**
     * testGetAndSetEnvironment
     *
     * @return  void
     */
    public function testGetAndSetEnvironment()
    {
        $this->instance->setEnvironment($env = new WebEnvironment());

        $this->assertSame($this->instance->browser, $this->instance->environment->getBrowser());
        $this->assertSame($this->instance->platform, $this->instance->getEnvironment()->getPlatform());
    }
}
