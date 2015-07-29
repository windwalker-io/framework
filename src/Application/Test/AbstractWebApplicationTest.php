<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Application\Test;

use Windwalker\Application\Test\Mock\MockResponse;
use Windwalker\Application\Test\Stub\StubWeb;
use Windwalker\Test\TestHelper;

/**
 * Test class of AbstractWebApplication
 *
 * @since 2.0
 */
class AbstractWebApplicationTest extends \PHPUnit_Framework_TestCase
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
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'foo.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->instance = new StubWeb;
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
	 * test__construct
	 *
	 * @return  void
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'Windwalker\\IO\\Input',
			$this->instance->input,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'Windwalker\\Registry\\Registry',
			TestHelper::getValue($this->instance, 'config'),
			'Config property wrong type'
		);

		$this->assertInstanceOf(
			'Windwalker\\Application\\Web\\Response',
			$this->instance->getResponse(),
			'Response property wrong type'
		);

		$this->assertInstanceOf(
			'Windwalker\\Environment\\Web\\WebEnvironment',
			$this->instance->getEnvironment(),
			'Environment property wrong type'
		);
	}

	/**
	 * Method to test execute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::execute
	 */
	public function testExecute()
	{
		$this->instance->setResponse(new MockResponse);

		ob_start();
		$this->instance->execute();
		ob_end_clean();

		$this->assertEquals('Hello World', $this->instance->getBody());

		$this->assertContains(
			'Content-Type: text/html; charset=utf-8',
			$this->instance->getResponse()->sentHeaders[0]
		);
	}

	/**
	 * Method to test respond().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::respond
	 */
	public function testRespond()
	{
		$this->instance->setResponse(new MockResponse);

		$this->instance->setBody('Hello World');

		$this->assertEquals('Hello World', $this->instance->respond(true));

		ob_start();

		$this->instance->respond();

		$return = ob_get_contents();

		ob_end_clean();

		$this->assertEquals('Hello World', $return);
	}

	/**
	 * Method to test __toString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::__toString
	 */
	public function test__toString()
	{
		$this->instance->setResponse(new MockResponse);

		$this->instance->setBody('Hello World');

		$this->assertEquals('Hello World', (string) $this->instance);
	}

	/**
	 * Method to test redirect().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::redirect
	 */
	public function testRedirect()
	{
		$this->instance->setResponse(new MockResponse);

		$this->instance->redirect('/foo');

		$headers = $this->instance->getResponse()->sentHeaders;

		$array = array(
			'HTTP/1.1 303 See other',
			'Location: http://foo.com/foo',
			'Content-Type: text/html; charset=utf-8'
		);

		$this->assertEquals($array, $headers);
	}

	/**
	 * Method to test setHeader().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::setHeader
	 */
	public function testSetHeader()
	{
		$this->instance->setResponse(new MockResponse);

		$this->instance->setHeader('Ethnic', 'We are borg.');

		$this->assertEquals('We are borg.', $this->instance->getResponse()->headers[0]['value']);
	}

	/**
	 * Method to test setBody().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::setBody
	 */
	public function testGetAndSetBody()
	{
		$this->instance->setBody('Flying bird.');

		$this->assertEquals('Flying bird.', $this->instance->getBody());
	}

	/**
	 * Method to test getResponse().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Application\AbstractWebApplication::getResponse
	 */
	public function testGetAndSetResponse()
	{
		$this->instance->setResponse(new MockResponse);

		$this->assertInstanceOf('Windwalker\\Application\\Test\\Mock\\MockResponse', $this->instance->getResponse());
	}

	/**
	 * testLoadSystemUris
	 *
	 * @param string $host
	 * @param string $self
	 * @param string $uri
	 * @param string $script
	 * @param array  $tests
	 *
	 * @dataProvider getServerData
	 *
	 * @return  void
	 */
	public function testLoadSystemUris($host, $self, $uri, $script, $tests)
	{
		$_SERVER['HTTP_HOST'] = $host;
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['PHP_SELF'] = $self;
		$_SERVER['REQUEST_URI'] = $uri;
		$_SERVER['SCRIPT_NAME'] = $script;

		$app = new StubWeb(null, null, null, new MockResponse);

		foreach ($tests as $name => $value)
		{
			$this->assertEquals($value, $app->get($name), $name . ' not matched.');
		}
	}

	/**
	 * getServerData
	 *
	 * @return  array
	 */
	public function getServerData()
	{
		return array(
			array(
				'foo.com',
				'/index.php',
				'/index.php',
				'/index.php',
				array(
					'uri.current'    => 'http://foo.com/index.php',
					'uri.base.full'  => 'http://foo.com/',
					'uri.base.host'  => 'http://foo.com',
					'uri.base.path'  => '/',
					'uri.route'      => '',
					'uri.media.full' => 'http://foo.com/media/',
					'uri.media.path' => '/media/'
				)
			),
			array(
				'foo.com',
				'/www/index.php',
				'/www/index.php',
				'/www/index.php',
				array(
					'uri.current'    => 'http://foo.com/www/index.php',
					'uri.base.full'  => 'http://foo.com/www/',
					'uri.base.host'  => 'http://foo.com',
					'uri.base.path'  => '/www/',
					'uri.route'      => '',
					'uri.media.full' => 'http://foo.com/www/media/',
					'uri.media.path' => '/www/media/'
				)
			),
			array(
				'foo.com',
				'/www/index.php/foo/bar',
				'/www/index.php/foo/bar',
				'/www/index.php',
				array(
					'uri.current'    => 'http://foo.com/www/index.php/foo/bar',
					'uri.base.full'  => 'http://foo.com/www/',
					'uri.base.host'  => 'http://foo.com',
					'uri.base.path'  => '/www/',
					'uri.route'      => 'foo/bar',
					'uri.media.full' => 'http://foo.com/www/media/',
					'uri.media.path' => '/www/media/'
				)
			),
			array(
				'foo.com',
				'/www/index.php',
				'/www/foo/bar',
				'/www/index.php',
				array(
					'uri.current'    => 'http://foo.com/www/foo/bar',
					'uri.base.full'  => 'http://foo.com/www/',
					'uri.base.host'  => 'http://foo.com',
					'uri.base.path'  => '/www/',
					'uri.route'      => 'foo/bar',
					'uri.media.full' => 'http://foo.com/www/media/',
					'uri.media.path' => '/www/media/'
				)
			)
		);
	}
}
