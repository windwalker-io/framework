<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Language\Test;

use Windwalker\Language\Language;

/**
 * Test class of Language
 *
 * @since 2.0
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Language
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
		$this->instance = new Language;

		$this->instance->load(__DIR__ . '/fixtures/ini/en-GB.ini', 'ini')
			->load(__DIR__ . '/fixtures/ini/zh-TW.ini', 'ini');
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
	 * Method to test load().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::load
	 * @TODO   Implement testLoad().
	 */
	public function testLoad()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test translate().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::translate
	 */
	public function testTranslate()
	{
		$this->assertEquals('花', $this->instance->translate('WINDWALKER_LANGUAGE_TEST_FLOWER'));
		$this->assertEquals('Olive', $this->instance->translate('WINDWALKER_LANGUAGE_TEST_Olive'));
		$this->assertEquals('Sunflower', $this->instance->translate('Windwalker Language Test Sunflower'));

		$this->assertEquals('A key not exists', $this->instance->translate('A key not exists'));

		$this->instance->setDebug(true);

		$this->assertEquals('**Sunflower**', $this->instance->translate('Windwalker Language Test Sunflower'));
		$this->assertEquals('??A key not exists??', $this->instance->translate('A key not exists'));
	}

	/**
	 * Method to test plural().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::plural
	 */
	public function testPlural()
	{
		$this->assertEquals('No Sunflower', $this->instance->plural('Windwalker Language Test Sunflower', 0));
		$this->assertEquals('Sunflower', $this->instance->plural('Windwalker Language Test Sunflower', 1));
		$this->assertEquals('Sunflowers', $this->instance->plural('Windwalker Language Test Sunflower', 2));

		$this->instance->setLocale('zh-TW');

		$this->assertEquals('沒有花', $this->instance->plural('Windwalker Language Test flower', 0));
		$this->assertEquals('花', $this->instance->plural('Windwalker Language Test flower', 1));
		$this->assertEquals('花', $this->instance->plural('Windwalker Language Test flower', 2));
	}

	/**
	 * Method to test sprintf().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::sprintf
	 */
	public function testSprintf()
	{
		$this->assertEquals('The Sakura is beautiful~~~!!!', $this->instance->sprintf('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sakura'));
		$this->assertEquals('The Sunflower is beautiful~~~!!!', $this->instance->sprintf('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sunflower'));
	}

	/**
	 * Method to test exists().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::exists
	 */
	public function testExists()
	{
		$this->assertTrue($this->instance->exists('Windwalker Language Test flower'));
		$this->assertFalse($this->instance->exists('Windwalker Language Test rose'));
	}

	/**
	 * Method to test addString().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::addString
	 */
	public function testAddString()
	{
		$this->instance->addString('windwalker language test rose', 'Rose');

		$this->assertTrue($this->instance->exists('Windwalker Language Test rose'));

		$this->assertEquals('Rose', $this->instance->translate('WINDWALKER_LANGUAGE_TEST_ROSE'));
	}

	/**
	 * Method to test addStrings().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::addStrings
	 * @TODO   Implement testAddStrings().
	 */
	public function testAddStrings()
	{
		$strings = array(
			'foo' => 'bar',
			'wind' => 'walker'
		);

		$this->instance->addStrings($strings);

		$this->assertEquals('bar', $this->instance->translate('foo'));
		$this->assertEquals('walker', $this->instance->translate('wind'));
	}

	/**
	 * Method to test setDebug().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setDebug
	 */
	public function testSetDebug()
	{
		$this->instance->setDebug(true);

		$this->assertEquals('**Sunflower**', $this->instance->translate('Windwalker Language Test Sunflower'));
		$this->assertEquals('??A key not exists??', $this->instance->translate('A key not exists'));
	}

	/**
	 * Method to test getLoader().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::getLoader
	 */
	public function testGetLoader()
	{
		$this->assertInstanceOf('Windwalker\Language\Loader\FileLoader', $this->instance->getLoader('file'));
		$this->assertInstanceOf('Windwalker\Language\Loader\PhpLoader', $this->instance->getLoader('php'));
	}

	/**
	 * Method to test setLoader().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setLoader
	 * @TODO   Implement testSetLoader().
	 */
	public function testSetLoader()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setLoaders().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setLoaders
	 * @TODO   Implement testSetLoaders().
	 */
	public function testSetLoaders()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getFormat().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::getFormat
	 */
	public function testGetFormat()
	{
		$this->assertInstanceOf('Windwalker\Language\Format\IniFormat', $this->instance->getFormat('ini'));
		$this->assertInstanceOf('Windwalker\Language\Format\YamlFormat', $this->instance->getFormat('yaml'));
	}

	/**
	 * Method to test setFormat().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setFormat
	 * @TODO   Implement testSetFormat().
	 */
	public function testSetFormat()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setFormats().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setFormats
	 * @TODO   Implement testSetFormats().
	 */
	public function testSetFormats()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getOrphans().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::getOrphans
	 */
	public function testGetOrphans()
	{
		$this->instance->setDebug(true);

		// Exists
		$this->instance->translate('Windwalker Language Test Sakura');

		// Not exists
		$this->instance->translate('Windwalker Language Test No exists flower');
		$this->instance->translate('A key not exists');

		$orphans = $this->instance->getOrphans();

		$this->assertEquals(array('windwalker.language.test.no.exists.flower', 'a.key.not.exists'), $orphans);
	}

	/**
	 * Method to test getUsed().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::getUsed
	 */
	public function testGetUsed()
	{
		// Exists
		$this->instance->translate('Windwalker Language Test Sakura');

		// Not exists
		$this->instance->translate('Windwalker Language Test No exists flower');
		$this->instance->translate('A key not exists');

		$used = $this->instance->getUsed();

		$this->assertEquals(array('windwalker.language.test.sakura'), $used);
	}

	/**
	 * Method to test getLocale().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::getLocale
	 */
	public function testGetAndSetLocale()
	{
		$this->assertEquals('en-GB', $this->instance->getLocale());

		$this->instance->setLocale('zh_tw');

		$this->assertEquals('zh-TW', $this->instance->getLocale());
	}

	/**
	 * Method to test setLocalise().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setLocalise
	 * @TODO   Implement testSetLocalise().
	 */
	public function testSetLocalise()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test normalize().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::normalize
	 */
	public function testNormalize()
	{
		$this->assertEquals('windwalker.is.good', $this->instance->normalize('Windwalker is good ~~~!!!'));

		$this->instance->setNormalizeHandler(
			function($value)
			{
				return 'WINDWALKER-ROCKS';
			}
		);

		$this->assertEquals('WINDWALKER-ROCKS', $this->instance->normalize('Windwalker is good ~~~!!!'));
	}

	/**
	 * Method to test getNormalizeHandler().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::getNormalizeHandler
	 * @TODO   Implement testGetNormalizeHandler().
	 */
	public function testGetNormalizeHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setNormalizeHandler().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Language\Language::setNormalizeHandler
	 * @TODO   Implement testSetNormalizeHandler().
	 */
	public function testSetNormalizeHandler()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
