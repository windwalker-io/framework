<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test\Matcher;

use Windwalker\Router\Matcher\TrieMatcher;
use Windwalker\Router\Route;
use Windwalker\Uri\Uri;

/**
 * Test class of TrieMatcher
 *
 * @since 2.0
 */
class TrieMatcherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var TrieMatcher
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
		// $this->markTestSkipped('Not prepare');

		$this->instance = new TrieMatcher;
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
	 * metchCases
	 *
	 * @return  array
	 */
	public function matchCases()
	{
		return array(
			// @ Same route, but different server params

			// Port 80 with default route
			array(
				'http://windwalker.com/flower/5',
				'flower/:id',
				'GET',
				true,
				__LINE__
			),
			// Port 443(default) with SSL
			array(
				'https://windwalker.com/flower/5',
				'flower/:id',
				'GET',
				false,
				__LINE__
			),
			// Port 137 with SSL
			array(
				'https://windwalker.com:137/flower/5',
				'flower/:id',
				'GET',
				false,
				__LINE__
			),
			// POST method
			array(
				'http://windwalker.com/flower/5',
				'flower/:id',
				'POST',
				false,
				__LINE__
			),
			// PUT method
			array(
				'http://windwalker.com/flower/5',
				'flower/:id',
				'PUT',
				true,
				__LINE__
			),
			// Different host
			array(
				'http://johnnywalker.com/flower/5',
				'flower/:id',
				'GET',
				false,
				__LINE__
			),
			// @ Match different routes

			// Root
			array(
				'http://windwalker.com/',
				'/',
				'GET',
				true,
				__LINE__
			),

			// Basic rules
			array(
				'http://windwalker.com/flower/5',
				'flower/:id/item/:alias',
				'GET',
				false,
				__LINE__
			),

			// Wildcards
			array(
				'http://windwalker.com/flower/foo/bar/baz',
				'flower/*tags',
				'GET',
				true,
				__LINE__
			),
			array(
				'http://windwalker.com/flower',
				'flower/*tags',
				'GET',
				false,
				__LINE__
			),
		);
	}

	/**
	 * Method to test match().
	 *
	 * @return void
	 *
	 * @covers       Windwalker\Router\Route::match
	 */
	public function testMatch()
	{
		$routes = file_get_contents(__DIR__ . '/../fixtures/trie.txt');

		$routes = explode("\n", trim($routes));

		$routes = array_map(
			function ($route)
			{
				$route = trim($route, '/');

				return new Route($route, $route, array('_return' => $route));
			},
			$routes
		);

		$matched = $this->instance->setRoutes($routes)
			->match('/corge/quux/qux');

		$this->assertFalse(!$matched);

		$this->assertEquals('corge/quux/:qux', $matched->getName());

		$this->instance->getCount();
	}

	/**
	 * Method to test match().
	 *
	 * @param string  $url
	 * @param string  $pattern
	 * @param string  $method
	 * @param boolean $expected
	 * @param integer $line
	 *
	 * @return void
	 *
	 * @covers       Windwalker\Router\Route::match
	 *
	 * @dataProvider matchCases
	 */
	public function testMatchRules($url, $pattern, $method, $expected, $line)
	{
		$uri = new Uri($url);

		$host = $uri->getHost();
		$scheme = $uri->getScheme();
		$port = $uri->getPort() ? : 80;

		$config = array(
			'name' => 'flower',
			'pattern' => $pattern,
			'variables' => array(
				'_controller' => 'FlowerController',
				'id' => 1
			),
			'method' => array('GET', 'PUT'),
			'host' => 'windwalker.com',
			'scheme' => 'http',
			'port' => 80,
			'sslPort' => 443,
			'requirements' => array(
				'id' => '\d+'
			)
		);

		$route = new \Windwalker\Router\Route(
			$config['name'],
			$config['pattern'],
			$config['variables'],
			$config['method'],
			$config
		);

		$result = $this->instance
			->setRoutes(array($route))
			->match(
				$uri->getPath(),
				$method,
				array(
					'host' => $host,
					'scheme' => $scheme,
					'port' => $port
				)
			);

		$this->assertEquals($expected, !empty($result), 'Match fail, case on line: ' . $line);
	}
}
