<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test;

use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Response\HtmlResponse;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Test\Stub\StubStreamOutput;
use Windwalker\Http\WebHttpServer;

date_default_timezone_set('UTC');

/**
 * Test class of WebHttpServer
 *
 * @since 3.0
 */
class WebHttpServerTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
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
	 * createServer
	 *
	 * @param   callable          $handler
	 * @param   ResponseInterface $response
	 *
	 * @return WebHttpServer
	 */
	protected function createServer($handler, ResponseInterface $response = null)
	{
		$server = WebHttpServer::create($handler, new ServerRequest, $response);

		$server->setOutput(new StubStreamOutput);

		return $server;
	}

	/**
	 * Method to test listen().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::listen
	 */
	public function testListen()
	{
		$server = $this->createServer(function ($request, ResponseInterface $response)
		{
		    return $response->getBody()->write('Hello');
		}, new HtmlResponse);

		$server->listen();

		$this->assertEquals('Hello', $server->getOutput()->output);

		$this->assertEquals(array('text/html; charset=utf-8'), $server->getOutput()->message->getHeader('Content-Type'));
	}

	/**
	 * Method to test prepareCache().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::prepareCache
	 */
	public function testPrepareCache()
	{
		$server = $this->createServer(function () {});

		// Cachable
		$server->cachable(WebHttpServer::CACHE_ENABLE);

		$response = $server->prepareCache(new Response);

		$headers = $response->getHeaders();

		$this->assertRegExp('/[\w]{3}, [\d]{1,2} [\w]{3} [\d]{4} [0-9]{2}:[0-9]{2}:[0-9]{2} GMT/', $headers['Expires'][0]);

		$date = new \DateTime($headers['Expires'][0]);
		$now = new \DateTime;

		$this->assertTrue($date > $now);

		// Disable
		$server->cachable(WebHttpServer::CACHE_DISABLE);

		$response = $server->prepareCache(new Response);

		$headers = $response->getHeaders();

		$this->assertEquals('Mon, 1 Jan 2001 00:00:00 GMT', $headers['Expires'][0]);
		$this->assertEquals('no-cache', $headers['Pragma'][0]);
		$this->assertEquals('no-store, no-cache, must-revalidate, post-check=0, pre-check=0', $headers['Cache-Control'][0]);
		$this->assertRegExp('/[\w]{3}, [\d]{1,2} [\w]{3} [\d]{4} [0-9]{2}:[0-9]{2}:[0-9]{2} GMT/', $headers['Last-Modified'][0]);

		// Custom Header
		$server->cachable(WebHttpServer::CACHE_CUSTOM_HEADER);

		$response = $server->prepareCache(new Response);

		$headers = $response->getHeaders();

		$this->assertEmpty($headers);
	}

	/**
	 * Method to test getMimeType().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::getContentType
	 * @TODO   Implement testGetMimeType().
	 */
	public function testGetContentType()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setMimeType().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::setContentType
	 */
	public function testSetContentType()
	{
		$server = $this->createServer(function () {});

		$server->setContentType('text/html');
		$server->setCharSet('latin1');
		$server->listen();

		$this->assertEquals(array('text/html; charset=latin1'), $server->getOutput()->message->getHeader('Content-Type'));
	}

	/**
	 * Method to test getCharSet().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::getCharSet
	 * @TODO   Implement testGetCharSet().
	 */
	public function testGetCharSet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setCharSet().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::setCharSet
	 * @TODO   Implement testSetCharSet().
	 */
	public function testSetCharSet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getModifiedDate().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::getModifiedDate
	 * @TODO   Implement testGetModifiedDate().
	 */
	public function testGetModifiedDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setModifiedDate().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::setModifiedDate
	 * @TODO   Implement testSetModifiedDate().
	 */
	public function testSetModifiedDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getUriData().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::getUriData
	 */
	public function testGetUriData()
	{
		$server = new WebHttpServer(function () {}, ServerRequestFactory::createFromGlobals(array(
			'HTTPS' => 'off',
			'SERVER_NAME' => 'example.com',
			'SERVER_PORT' => '8080',
			'QUERY_STRING' => '?a=b&c=d',
			'REQUEST_URI' => '/flower/sakura/index.php/foo/bar?a=wrong',
			'SCRIPT_NAME' => '/flower/sakura/index.php'
		)));
		
		$uri = $server->getUriData();
		
		$this->assertEquals('http://example.com:8080/flower/sakura/index.php/foo/bar?a=b&c=d', $uri->full);
		$this->assertEquals('http://example.com:8080/flower/sakura/index.php/foo/bar', $uri->current);
		$this->assertEquals('index.php', $uri->script);
		$this->assertEquals('http://example.com:8080/flower/sakura', $uri->root);
		$this->assertEquals('foo/bar', $uri->route);
		$this->assertEquals('http://example.com:8080', $uri->host);
		$this->assertEquals('/flower/sakura', $uri->path);

		$server = new WebHttpServer(function () {}, new ServerRequest(array('SCRIPT_NAME' => '/flower/sakura/index.php'), array(), 'http://example.com:8080/flower/sakura/index.php/foo/bar?a=b&c=d', 'GET'));

		$this->assertEquals($uri, $server->getUriData());
	}

	/**
	 * Method to test setUriData().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::setUriData
	 * @TODO   Implement testSetUriData().
	 */
	public function testSetUriData()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test __get().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::__get
	 * @TODO   Implement test__get().
	 */
	public function test__get()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCompressor().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::getCompressor
	 * @TODO   Implement testGetCompressor().
	 */
	public function testGetCompressor()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setCompressor().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::setCompressor
	 * @TODO   Implement testSetCompressor().
	 */
	public function testSetCompressor()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCachable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::getCachable
	 * @TODO   Implement testGetCachable().
	 */
	public function testGetCachable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test cachable().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::cachable
	 * @TODO   Implement testCachable().
	 */
	public function testCachable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test createHttpCompressor().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\WebHttpServer::createHttpCompressor
	 * @TODO   Implement testCreateHttpCompressor().
	 */
	public function testCreateHttpCompressor()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
