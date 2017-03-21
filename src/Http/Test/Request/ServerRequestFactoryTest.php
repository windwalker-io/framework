<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Request;

use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Stream\Stream;
use Windwalker\Http\UploadedFile;

/**
 * Test class of ServerRequestFactory
 *
 * @since 3.0
 */
class ServerRequestFactoryTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::createFromGlobals
	 */
	public function testCreate()
	{
		$request = ServerRequestFactory::createFromGlobals();

		$this->assertTrue($request instanceof ServerRequest);
	}

	/**
	 * Method to test prepareServers().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareServers
	 */
	public function testPrepareServers()
	{
		$bak = ServerRequestFactory::$apacheRequestHeaders;

		ServerRequestFactory::$apacheRequestHeaders = array($this, 'apacheRequestHeaders');

		$server = array();
		
		$server = ServerRequestFactory::prepareServers($server);

		$this->assertEquals('foo', $server['HTTP_AUTHORIZATION']);

		// Test no auth
		ServerRequestFactory::$apacheRequestHeaders = array($this, 'apacheRequestHeadersEmpty');

		$server = array();

		$server = ServerRequestFactory::prepareServers($server);

		$this->assertTrue(empty($server['HTTP_AUTHORIZATION']));

		ServerRequestFactory::$apacheRequestHeaders = $bak;
	}

	/**
	 * apacheRequestHeaders
	 *
	 * @return  array
	 */
	public function apacheRequestHeaders()
	{
		return array(
			'authorization' => 'foo'
		);
	}

	/**
	 * apacheRequestHeadersEmpty
	 *
	 * @return  array
	 */
	public function apacheRequestHeadersEmpty()
	{
		return array();
	}

	/**
	 * Method to test prepareFiles().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareFiles
	 */
	public function testPrepareFiles()
	{
		$files = array(
			array(
				'tmp_name' => 'php://temp',
				'size' => 123,
				'error' => 0,
				'name' => 'foo_name',
				'type' => 'foo_type'
			)
		);

		$files = ServerRequestFactory::prepareFiles($files);

		$this->assertTrue($files[0] instanceof UploadedFile);
		$this->assertTrue($files[0]->getStream() instanceof Stream);
		$this->assertEquals('foo_name', $files[0]->getClientFilename());
		$this->assertEquals(0, $files[0]->getError());

		$files = array(
			'first' => array(
				'tmp_name' => array(
					'foo' => 'php://temp',
					'bar' => 'php://temp',
				),
				'size' => array(
					'foo' => 123,
					'bar' => 321
				),
				'error' => array(
					'foo' => 1,
					'bar' => 2
				),
				'name' => array(
					'foo' => 'foo_name',
					'bar' => 'bar_name'
				),
				'type' => array(
					'foo' => 'foo_type',
					'bar' => 'bar_type'
				)
			),
			'second' => array(
				'tmp_name' => 'php://temp',
				'size' => 456,
				'error' => 0,
				'name' => 'second_name',
				'type' => 'second_type'
			)
		);

		$files = ServerRequestFactory::prepareFiles($files);

		$this->assertTrue($files['first']['foo'] instanceof UploadedFile);
		$this->assertTrue($files['first']['bar'] instanceof UploadedFile);
		$this->assertTrue($files['second'] instanceof UploadedFile);
	}

	/**
	 * Method to test prepareHeaders().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareHeaders
	 */
	public function testPrepareHeaders()
	{
		$headers = array(
			'HTTP_X_FOO' => 'foo',
			'HTTP_X_BAR' => 'bar',
			'HTTP_X_FLOWER' => 'Sakura',
			'CONTENT_YOO' => 'baz',
			'CONTENT_BIRD' => 'fly',
		);

		$expected = array(
			'x-foo' => 'foo',
			'x-bar' => 'bar',
			'x-flower' => 'Sakura',
			'content-yoo' => 'baz',
			'content-bird' => 'fly',
		);

		$this->assertEquals($expected, ServerRequestFactory::prepareHeaders($headers));
	}

	/**
	 * Method to test prepareUri().
	 *
	 * @param array  $servers
	 * @param array  $headers
	 * @param string $expected
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::prepareUri
	 *
	 * @dataProvider prepareUri_Provider
	 */
	public function testPrepareUri($servers, $headers, $expected)
	{
		$uri = ServerRequestFactory::prepareUri($servers, $headers);

		$this->assertEquals($expected, $uri->__toString());
	}

	/**
	 * prepareUri_Provider
	 *
	 * @return  array
	 */
	public function prepareUri_Provider()
	{
		return array(
			'#apache-normal' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong'
				),
				array(),
				'http://example.com:8080/foo/bar?a=b&c=d',
				__LINE__,
			),
			'#apache-fragment' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar#test?a=b&c=d'
				),
				array(),
				'http://example.com:8080/foo/bar?a=b&c=d#test',
				__LINE__,
			),
			'#apache-https' => array(
				array(
					'HTTPS' => 'on',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong'
				),
				array(),
				'https://example.com:8080/foo/bar?a=b&c=d',
				__LINE__,
			),
			'#apache-x-forwarded' => array(
				array(
					'HTTPS' => '',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong'
				),
				array(
					'x-forwarded-proto' => 'https'
				),
				'https://example.com:8080/foo/bar?a=b&c=d',
				__LINE__,
			),
			'#apache-header-host' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => '',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong'
				),
				array(
					'host' => 'example.com'
				),
				// Will never get port because host in header is a cache
				'http://example.com/foo/bar?a=b&c=d',
				__LINE__,
			),
			'#apache-ipv6' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => '[2001:db8:a0b:12f0::1]',
					'SERVER_ADDR' => '2001:db8:a0b:12f0::1',
					'SERVER_PORT' => 8080,
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong'
				),
				array(),
				'http://[2001:db8:a0b:12f0::1]:8080/foo/bar?a=b&c=d',
				__LINE__,
			),
			'#iis-rewritten' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'IIS_WasUrlRewritten' => '1',
					'UNENCODED_URL' => 'flower/sakura',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong'
				),
				array(),
				'http://example.com:8080/flower/sakura?a=b&c=d',
				__LINE__,
			),
			'#iis-x-rewrite' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong',
					'HTTP_X_REWRITE_URL' => '/flower/sakura?a=wrong',
				),
				array(),
				'http://example.com:8080/flower/sakura?a=b&c=d',
				__LINE__,
			),
			'#iis-origin-url' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'REQUEST_URI' => '/foo/bar?a=wrong',
					'HTTP_X_REWRITE_URL' => '/flower/sakura?a=wrong',
					'HTTP_X_ORIGINAL_URL' => '/flower/olive?a=wrong'
				),
				array(),
				'http://example.com:8080/flower/olive?a=b&c=d',
				__LINE__,
			),
			'#orig-path-info' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
					'ORIG_PATH_INFO' => '/flower/rose'
				),
				array(),
				'http://example.com:8080/flower/rose?a=b&c=d',
				__LINE__,
			),
			'#no-path' => array(
				array(
					'HTTPS' => 'off',
					'SERVER_NAME' => 'example.com',
					'SERVER_PORT' => '8080',
					'QUERY_STRING' => '?a=b&c=d',
				),
				array(),
				'http://example.com:8080/?a=b&c=d',
				__LINE__,
			),
		);
	}

	/**
	 * Method to test getHostAndPortFromHeaders().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::getHostAndPortFromHeaders
	 * @TODO   Implement testGetHostAndPortFromHeaders().
	 */
	public function testGetHostAndPortFromHeaders()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getRequestUri().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::getRequestUri
	 * @TODO   Implement testGetRequestUri().
	 */
	public function testGetRequestUri()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test stripQueryString().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Http\Request\ServerRequestFactory::stripQueryString
	 * @TODO   Implement testStripQueryString().
	 */
	public function testStripQueryString()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
