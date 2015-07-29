<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test\Compiler;

use Windwalker\Router\Compiler\TrieCompiler;
use Windwalker\Router\RouteHelper;

/**
 * Test class of TrieCompiler
 *
 * @since 2.0
 */
class TrieCompilerTest extends \PHPUnit_Framework_TestCase
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
				'/flower/:id',
				'/flower/(?P<id>\d+)',
				'/flower/5',
				array('id' => 5),
				__LINE__
			),
			array(
				'/flower/caesar/:id/:alias',
				'/flower/caesar/(?P<id>\d+)/(?P<alias>[^/]+)',
				'/flower/caesar/25/othello',
				array('id' => 25, 'alias' => 'othello'),
				__LINE__
			),
			array(
				'/king/john/*tags',
				'/king/john/(?P<tags>.*)',
				'/king/john/troilus/and/cressida',
				array('tags' => array('troilus', 'and', 'cressida')),
				__LINE__
			),
			array(
				'/king/*tags/:alias',
				'/king/(?P<tags>.*)/(?P<alias>[^/]+)',
				'/king/john/troilus/and/cressida',
				array('tags' => array('john', 'troilus', 'and'), 'alias' => 'cressida'),
				__LINE__
			),
			array(
				'/king/*tags/and/:alias',
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
		$regex = TrieCompiler::compile($pattern, array('id' => '\d+'));

		$this->assertEquals(chr(1) . '^' . $expected . '$' . chr(1), $regex, 'Fail at: ' . $line);

		preg_match($regex, $route, $matches);

		$vars = RouteHelper::getVariables($matches);

		$this->assertNotEmpty($matches);

		$this->assertEquals($expectedMatches, $vars);
	}
}
