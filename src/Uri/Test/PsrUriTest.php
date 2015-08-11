<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Uri\Test;

use Windwalker\Uri\PsrUri;

/**
 * Test class of PsrUri
 *
 * @since {DEPLOY_VERSION}
 */
class PsrUriTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * testConstruct
	 *
	 * @return  void
	 */
	public function testConstruct()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

		$this->assertEquals('https', $uri->getScheme());
		$this->assertEquals('user:pass', $uri->getUserInfo());
		$this->assertEquals('local.example.com', $uri->getHost());
		$this->assertEquals(3001, $uri->getPort());
		$this->assertEquals('user:pass@local.example.com:3001', $uri->getAuthority());
		$this->assertEquals('/foo', $uri->getPath());
		$this->assertEquals('bar=baz', $uri->getQuery());
		$this->assertEquals('quz', $uri->getFragment());
	}

	/**
	 * testToString
	 *
	 * @return  void
	 */
	public function testToString()
	{
		$url = 'https://user:pass@local.example.com:3001/foo?bar=baz#quz';
		$uri = new PsrUri($url);
		$this->assertEquals($url, (string) $uri);
	}

	/**
	 * testWithScheme
	 *
	 * @return  void
	 */
	public function testWithScheme()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withScheme('http');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('http', $new->getScheme());
		$this->assertEquals('http://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);

		$new = $uri->withScheme('https://');
		$this->assertEquals('https', $new->getScheme());
	}

	/**
	 * testWithUserInfo
	 *
	 * @return  void
	 */
	public function testWithUserInfo()
	{
		// User
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withUserInfo('flower');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('flower', $new->getUserInfo());
		$this->assertEquals('https://flower@local.example.com:3001/foo?bar=baz#quz', (string) $new);

		// User & Password
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withUserInfo('flower', 'sakura');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('flower:sakura', $new->getUserInfo());
		$this->assertEquals('https://flower:sakura@local.example.com:3001/foo?bar=baz#quz', (string) $new);
	}

	/**
	 * testWithHost
	 *
	 * @return  void
	 */
	public function testWithHost()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withHost('windwalker.io');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('windwalker.io', $new->getHost());
		$this->assertEquals('https://user:pass@windwalker.io:3001/foo?bar=baz#quz', (string) $new);
	}

	/**
	 * testWithPort
	 *
	 * @return  void
	 */
	public function testWithPort()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withPort(3000);
		$this->assertNotSame($uri, $new);
		$this->assertEquals(3000, $new->getPort());
		$this->assertEquals(
			sprintf('https://user:pass@local.example.com:%d/foo?bar=baz#quz', 3000),
			(string) $new
		);
	}

	/**
	 * testWithPath
	 *
	 * @return  void
	 */
	public function testWithPath()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withPath('/bar/baz');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('/bar/baz', $new->getPath());
		$this->assertEquals('https://user:pass@local.example.com:3001/bar/baz?bar=baz#quz', (string) $new);

		$uri = new PsrUri('http://example.com');
		$new = $uri->withPath('foo/bar');
		$this->assertEquals('foo/bar', $new->getPath());

		$uri = new PsrUri('http://example.com');
		$new = $uri->withPath('foo/bar');
		$this->assertEquals('http://example.com/foo/bar', $new->__toString());

		// Encoded
		$uri = $uri->withPath('/foo^bar');
		$expected = '/foo%5Ebar';
		$this->assertEquals($expected, $uri->getPath());

		// Not double encoded
		$uri = $uri->withPath('/foo%5Ebar');
		$expected = '/foo%5Ebar';
		$this->assertEquals($expected, $uri->getPath());
	}

	/**
	 * testWithQuery
	 *
	 * @return  void
	 */
	public function testWithQuery()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withQuery('baz=bat');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('baz=bat', $new->getQuery());
		$this->assertEquals('https://user:pass@local.example.com:3001/foo?baz=bat#quz', (string) $new);

		// Strip query symbol
		$uri = new PsrUri('http://example.com');
		$new = $uri->withQuery('?foo=bar');
		$this->assertEquals('foo=bar', $new->getQuery());
	}

	/**
	 * testWithFragment
	 *
	 * @return  void
	 */
	public function testWithFragment()
	{
		$uri = new PsrUri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
		$new = $uri->withFragment('qat');
		$this->assertNotSame($uri, $new);
		$this->assertEquals('qat', $new->getFragment());
		$this->assertEquals('https://user:pass@local.example.com:3001/foo?bar=baz#qat', (string) $new);

		$uri = new PsrUri('http://example.com');
		$new = $uri->withFragment('#/foo/bar');
		$this->assertEquals('/foo/bar', $new->getFragment());
	}

	/**
	 * authorityProvider
	 *
	 * @return  array
	 */
	public function authorityProvider()
	{
		return array(
			'host-only'      => array('http://foo.com/bar',         'foo.com'),
			'host-port'      => array('http://foo.com:3000/bar',    'foo.com:3000'),
			'user-host'      => array('http://me@foo.com/bar',      'me@foo.com'),
			'user-host-port' => array('http://me@foo.com:3000/bar', 'me@foo.com:3000'),
		);
	}

	/**
	 * testAuthority
	 *
	 * @dataProvider authorityProvider
	 *
	 * @param string $url
	 * @param string $expected
	 */
	public function testAuthority($url, $expected)
	{
		$uri = new PsrUri($url);
		$this->assertEquals($expected, $uri->getAuthority());
	}

	public function queryStringsForEncoding()
	{
		return array(
			'key-only' => array('k^ey', 'k%5Eey'),
			'key-value' => array('k^ey=valu`', 'k%5Eey=valu%60'),
			'array-key-only' => array('key[]', 'key%5B%5D'),
			'array-key-value' => array('key[]=valu`', 'key%5B%5D=valu%60'),
			'complex' => array('k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'),
		);
	}

	/**
	 * @dataProvider queryStringsForEncoding
	 *
	 * @param string $query
	 * @param string $expected
	 */
	public function testQueryEncoded($query, $expected)
	{
		$uri = new PsrUri;
		$uri = $uri->withQuery($query);
		$this->assertEquals($expected, $uri->getQuery());

		// No double encoded
		$uri = $uri->withQuery($expected);
		$this->assertEquals($expected, $uri->getQuery());
	}

	/**
	 * testFragmentEncoded
	 *
	 * @return  void
	 */
	public function testFragmentEncoded()
	{
		$uri = new PsrUri;
		$uri = $uri->withFragment('/p^th?key^=`bar#b@z');
		$expected = '/p%5Eth?key%5E=%60bar%23b@z';
		$this->assertEquals($expected, $uri->getFragment());

		// No double encoded
		$expected = '/p%5Eth?key%5E=%60bar%23b@z';
		$uri = $uri->withFragment($expected);
		$this->assertEquals($expected, $uri->getFragment());
	}

	/**
	 * seedInvalidArguments
	 *
	 * @return  array
	 */
	public function seedInvalidArguments()
	{
		$methods = array(
			'withScheme',
			'withUserInfo',
			'withHost',
			'withPath',
			'withQuery',
			'withFragment',
		);

		$values = array(
			'null'       => null,
			'true'       => true,
			'false'      => false,
			'zero'       => 0,
			'int'        => 1,
			'zero-float' => 0.0,
			'float'      => 1.1,
			'array'      => array('value'),
			'object'     => (object) array('value' => 'value'),
		);

		$combinations = array();

		foreach ($methods as $method)
		{
			foreach ($values as $type => $value)
			{
				$key = sprintf('%s-%s', $method, $type);

				$combinations[$key] = array($method, $value);
			}
		}

		return $combinations;
	}

	/**
	 * testPassingInvalidValueToWithMethodRaisesException
	 *
	 * @param $method
	 * @param $value
	 *
	 * @return  void
	 *
	 * @dataProvider seedInvalidArguments
	 */
	public function testInvalidArguments($method, $value)
	{
		$uri = new PsrUri('https://example.com/');
		$this->setExpectedException('InvalidArgumentException');
		$uri->$method($value);
	}
}
