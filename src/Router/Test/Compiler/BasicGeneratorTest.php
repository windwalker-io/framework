<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test\Compiler;

use Windwalker\Router\Compiler\BasicGenerator;

/**
 * Test class of BasicGenerator
 *
 * @since 2.0
 */
class BasicGeneratorTest extends \PHPUnit_Framework_TestCase
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
				'flower/(id)',
				array('id' => 25),
				'flower/25',
				__LINE__
			),
			array(
				'flower/(id)/(alias)',
				array('id' => 25, 'alias' => 'sakura'),
				'flower/25/sakura',
				__LINE__
			),
			array(
				'flower/(id)/(alias)',
				array('alias' => 'sakura'),
				'flower/(id)/sakura',
				__LINE__
			),
			array(
				'flower/(id)-(alias)',
				array('id' => 25, 'alias' => 'sakura'),
				'flower/25-sakura',
				__LINE__
			),
			array(
				'flower(/id)',
				array('id' => 25, 'alias' => 'sakura'),
				'flower/25?alias=sakura',
				__LINE__
			),
			array(
				'flower(/id)',
				array('alias' => 'sakura'),
				'flower?alias=sakura',
				__LINE__
			),
			array(
				'flower(/id,alias)',
				array('id' => 25, 'alias' => 'sakura'),
				'flower/25/sakura',
				__LINE__
			),
			array(
				'flower(/foo,bar,baz)',
				array('foo' => 2014, 'bar' => 9, 'baz' => 27),
				'flower/2014/9/27',
				__LINE__
			),
			array(
				'flower/(*tags)',
				array('id' => 25, 'tags' => array('sakura', 'rose', 'olive')),
				'flower/sakura/rose/olive?id=25',
				__LINE__
			),
			array(
				'flower/(*tags)/(alias)',
				array('id' => 25, 'alias' => 'wind', 'tags' => array('sakura', 'rose', 'olive')),
				'flower/sakura/rose/olive/wind?id=25',
				__LINE__
			),
		);
	}

	/**
	 * Method to test generate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Router\Compiler\BasicGenerator::generate
	 *
	 * @dataProvider  regexList
	 */
	public function testGenerate($pattern, $data, $expected, $line)
	{
		$this->assertEquals($expected, BasicGenerator::generate($pattern, $data), 'Fail at: ' . $line);
	}
}
