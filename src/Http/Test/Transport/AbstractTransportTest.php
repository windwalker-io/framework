<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Http\Test\Transport;

use Windwalker\Http\Request;
use Windwalker\Http\Stream\StringStream;
use Windwalker\Http\Transport\AbstractTransport;
use Windwalker\Uri\PsrUri;

/**
 * Test class of CurlTransport
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractTransportTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array(
		'options' => array()
	);

	/**
	 * Test instance.
	 *
	 * @var AbstractTransport
	 */
	protected $instance;

	/**
	 * setUpBeforeClass
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		if (!defined('WINDWALKER_TEST_HTTP_URL'))
		{
			static::markTestSkipped('No WINDWALKER_TEST_HTTP_URL provided');
		}
	}

	/**
	 * createRequest
	 *
	 * @return  Request
	 */
	protected function createRequest()
	{
		return new Request(new StringStream);
	}

	/**
	 * testRequestGet
	 *
	 * @return  void
	 */
	public function testRequestGet()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(WINDWALKER_TEST_HTTP_URL))
			->withMethod('GET');

		$response = $this->instance->request($request);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getBody()->getContents());

		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(WINDWALKER_TEST_HTTP_URL . '?foo=bar&baz[3]=yoo'))
			->withMethod('GET');

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);
		$this->assertEquals(array('foo' => 'bar', 'baz' => array(3 => 'yoo')), $data['_GET']);
	}

	/**
	 * testBadDomainGet
	 *
	 * @return  void
	 *
	 * @expectedException  \RuntimeException
	 */
	public function testBadDomainGet()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri('http://not.exists.url/flower.sakura'))
			->withMethod('GET');

		$this->instance->request($request);
	}

	/**
	 * testBadPathGet
	 *
	 * @return  void
	 */
	public function testBadPathGet()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(dirname(WINDWALKER_TEST_HTTP_URL) . '/wrong.php'))
			->withMethod('POST');

		$request->getBody()->write(json_encode(array('foo' => 'bar')));

		$response = $this->instance->request($request);

		$this->assertEquals(404, $response->getStatusCode());
		$this->assertEquals('Not Found', $response->getReasonPhrase());
	}

	/**
	 * testRequestPost
	 *
	 * @return  void
	 */
	public function testRequestPost()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(WINDWALKER_TEST_HTTP_URL))
			->withMethod('POST');

		$request->getBody()->write(json_encode(array('foo' => 'bar')));

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals(array('foo' => 'bar'), $data['_POST']);
	}

	/**
	 * testRequestPut
	 *
	 * @return  void
	 */
	public function testRequestPut()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(WINDWALKER_TEST_HTTP_URL))
			->withMethod('PUT');

		$request->getBody()->write(json_encode(array('foo' => 'bar')));

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals(array('foo' => 'bar'), $data['data']);
		$this->assertEquals('PUT', $data['_SERVER']['REQUEST_METHOD']);
	}

	/**
	 * testRequestCredentials
	 *
	 * @return  void
	 */
	public function testRequestCredentials()
	{
		$request = $this->createRequest();

		$uri = new PsrUri(WINDWALKER_TEST_HTTP_URL);
		$uri = $uri->withUserInfo('username', 'pass1234');

		$request = $request->withUri($uri)
			->withMethod('GET');

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals('username', $data['_SERVER']['PHP_AUTH_USER']);
		$this->assertEquals('pass1234', $data['_SERVER']['PHP_AUTH_PW']);
	}

	/**
	 * testRequestPostScalar
	 *
	 * @return  void
	 */
	public function testRequestPostScalar()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(WINDWALKER_TEST_HTTP_URL . '?foo=bar'))
			->withMethod('POST');

		$request->getBody()->write(json_encode('flower=sakura'));

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals(array('foo' => 'bar'), $data['_GET']);
		$this->assertEquals(array('flower' => 'sakura'), $data['_POST']);
	}
}
