<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language\Test;

use Windwalker\Language\LanguageNormalize;

/**
 * Test class of LanguageNormalize
 *
 * @since {DEPLOY_VERSION}
 */
class LanguageNormalizeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * getToTagCases
	 *
	 * @return  array
	 */
	public function getToTagCases()
	{
		return array(
			array(
				'foo_bar',
				'foo.bar'
			),
			array(
				'flower-sakura-flower',
				'flower.sakura.flower'
			),
			array(
				'FLOWER_SAKURA_FLOWER',
				'flower.sakura.flower'
			),
			array(
				'Lorem ipsum dolor sit amet, consectetur.',
				'lorem.ipsum.dolor.sit.amet.consectetur'
			),
			array(
				'--test-foo.bar/yoo\\go{play}test[fly]--',
				'test.foo.bar.yoo.go.play.test.fly'
			),
			array(
				'雲彩裡，許是懺悔 THe B612 只有用心靈，一個人才能看得很清楚',
				'the.b612'
			)
		);
	}

	/**
	 * Method to test toLanguageTag().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\LanguageNormalize::toLanguageTag
	 */
	public function testToLanguageTag()
	{
		$this->assertEquals('en-GB', LanguageNormalize::toLanguageTag('en_gb'));
		$this->assertEquals('en-GB', LanguageNormalize::toLanguageTag('EN_GB'));
		$this->assertEquals('en-GB', LanguageNormalize::toLanguageTag('en-gb'));
		$this->assertEquals('en-GB', LanguageNormalize::toLanguageTag('EN-gB'));
	}

	/**
	 * Method to test getLocaliseClassPrefix().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\LanguageNormalize::getLocaliseClassPrefix
	 */
	public function testGetLocaliseClassPrefix()
	{
		$this->assertEquals('EnGB', LanguageNormalize::getLocaliseClassPrefix('en_gb'));
		$this->assertEquals('EnGB', LanguageNormalize::getLocaliseClassPrefix('EN_GB'));
		$this->assertEquals('EnGB', LanguageNormalize::getLocaliseClassPrefix('en-gb'));
		$this->assertEquals('EnGB', LanguageNormalize::getLocaliseClassPrefix('EN-gB'));
	}

	/**
	 * Method to test toLanguageKey().
	 *
	 * @param string $origin
	 * @param string $expected
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\LanguageNormalize::toLanguageKey
	 *
	 * @dataProvider getToTagCases
	 */
	public function testToLanguageKey($origin, $expected)
	{
		$this->assertEquals($expected, LanguageNormalize::toLanguageKey($origin));
	}
}
