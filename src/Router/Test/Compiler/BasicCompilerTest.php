<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test\Compiler;

use Windwalker\Router\Compiler\BasicCompiler;
use Windwalker\Router\Matcher\SequentialMatcher;
use Windwalker\Router\Route;
use Windwalker\Router\RouteHelper;

/**
 * Test class of BasicCompiler
 *
 * @since 2.0
 */
class BasicCompilerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * regexList
	 *
	 * @return  array
	 */
	public function regexList()
	{
		return array(
			array(
				'/flower/(id)',
				'/flower/(?P<id>\d+)',
				'/flower/25',
				array('id' => 25),
				__LINE__
			),
			array(
				'/flower/caesar/(id)/(alias)',
				'/flower/caesar/(?P<id>\d+)/(?P<alias>[^/]+)',
				'/flower/caesar/25/othello',
				array('id' => 25, 'alias' => 'othello'),
				__LINE__
			),
			array(
				'/flower/caesar/(id)-(alias)',
				'/flower/caesar/(?P<id>\d+)-(?P<alias>[^/]+)',
				'/flower/caesar/25-othello',
				array('id' => 25, 'alias' => 'othello'),
				__LINE__
			),
			array(
				'/flower(/id)',
				'/flower(/(?P<id>\d+))?',
				'/flower/33',
				array('id' => 33),
				__LINE__
			),
			array(
				'/flower(/id)',
				'/flower(/(?P<id>\d+))?',
				'/flower',
				array(),
				__LINE__
			),
			array(
				'/flower/caesar(/id,alias)',
				'/flower/caesar(/(?P<id>\d+)(/(?P<alias>[^/]+))?)?',
				'/flower/caesar/25/othello',
				array('id' => 25, 'alias' => 'othello'),
				__LINE__
			),
			array(
				'/flower/caesar(/id,alias)',
				'/flower/caesar(/(?P<id>\d+)(/(?P<alias>[^/]+))?)?',
				'/flower/caesar/25',
				array('id' => 25),
				__LINE__
			),
			array(
				'/flower/caesar(/id,alias)',
				'/flower/caesar(/(?P<id>\d+)(/(?P<alias>[^/]+))?)?',
				'/flower/caesar',
				array(),
				__LINE__
			),
			array(
				'/king(/foo,bar,baz,yoo)',
				'/king(/(?P<foo>[^/]+)(/(?P<bar>[^/]+)(/(?P<baz>[^/]+)(/(?P<yoo>[^/]+))?)?)?)?',
				'/king/john/troilus/and/cressida',
				array('foo' => 'john', 'bar' => 'troilus', 'baz' => 'and', 'yoo' => 'cressida'),
				__LINE__
			),
			array(
				'/king/(*tags)',
				'/king/(?P<tags>.*)',
				'/king/john/troilus/and/cressida',
				array('tags' => array('john', 'troilus', 'and', 'cressida')),
				__LINE__
			),
			array(
				'/king/(*tags)/and/(alias)',
				'/king/(?P<tags>.*)/and/(?P<alias>[^/]+)',
				'/king/john/troilus/and/cressida',
				array('tags' => array('john', 'troilus'), 'alias' => 'cressida'),
				__LINE__
			),
		);
	}

	/**
	 * Method to test compile().
	 *
	 * @param string $pattern
	 * @param string $expected
	 * @param int    $line
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Compiler\BasicCompiler::compile
	 *
	 * @dataProvider  regexList
	 */
	public function testCompile($pattern, $expected, $route, $expectedMatches, $line)
	{
		$regex = BasicCompiler::compile($pattern, array('id' => '\d+'));

		$this->assertEquals(chr(1) . '^' . $expected . '$' . chr(1), $regex, 'Fail at: ' . $line);

		preg_match($regex, $route, $matches);

		$this->assertNotEmpty($matches);

		$vars = RouteHelper::getVariables($matches);

		$this->assertEquals($expectedMatches, $vars);
	}
}
