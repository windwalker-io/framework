<?php
/**
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Source Matters, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\String\Tests;

use Windwalker\String\StringInflector;
use Windwalker\Test\TestHelper;

/**
 * Test for the StringInflector class.
 *
 * @link   http://en.wikipedia.org/wiki/English_plural
 * @since  2.0
 */
class InflectorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    StringInflector
	 * @since  2.0
	 */
	protected $StringInflector;

	/**
	 * Method to seed data to testIsCountable.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedIsCountable()
	{
		return array(
			array('id', true),
			array('title', false),
		);
	}

	/**
	 * Method to seed data to testToPlural.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedSinglePlural()
	{
		return array(
			// Regular plurals
			array('bus', 'buses'),
			array('notify', 'notifies'),
			array('click', 'clicks'),

			// Almost regular plurals.
			array('photo', 'photos'),
			array('zero', 'zeros'),

			// Irregular identicals
			array('salmon', 'salmon'),

			// Irregular plurals
			array('ox', 'oxen'),
			array('quiz', 'quizes'),
			array('status', 'statuses'),
			array('matrix', 'matrices'),
			array('index', 'indices'),
			array('vertex', 'vertices'),
			array('hive', 'hives'),

			// Ablaut plurals
			array('foot', 'feet'),
			array('goose', 'geese'),
			array('louse', 'lice'),
			array('man', 'men'),
			array('mouse', 'mice'),
			array('tooth', 'teeth'),
			array('woman', 'women'),
		);
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->StringInflector = StringInflector::getInstance(true);
	}

	/**
	 * Method to test StringInflector::addRule().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::addRule
	 * @since   2.0
	 */
	public function testAddRule()
	{
		// Case 1
		TestHelper::invoke($this->StringInflector, 'addRule', '/foo/', 'singular');

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertContains(
			'/foo/',
			$rules['singular'],
			'Checks if the singular rule was added correctly.'
		);

		// Case 2
		TestHelper::invoke($this->StringInflector, 'addRule', '/bar/', 'plural');

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertContains(
			'/bar/',
			$rules['plural'],
			'Checks if the plural rule was added correctly.'
		);

		// Case 3
		TestHelper::invoke($this->StringInflector, 'addRule', array('/goo/', '/car/'), 'singular');

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertContains(
			'/goo/',
			$rules['singular'],
			'Checks if an array of rules was added correctly (1).'
		);

		$this->assertContains(
			'/car/',
			$rules['singular'],
			'Checks if an array of rules was added correctly (2).'
		);
	}

	/**
	 * Method to test StringInflector::addRule().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::addRule
	 * @expectedException  InvalidArgumentException
	 * @since   2.0
	 */
	public function testAddRuleException()
	{
		TestHelper::invoke($this->StringInflector, 'addRule', new \stdClass, 'singular');
	}

	/**
	 * Method to test StringInflector::getCachedPlural().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::getCachedPlural
	 * @since   2.0
	 */
	public function testGetCachedPlural()
	{
		// Reset the cache.
		TestHelper::setValue($this->StringInflector, 'cache', array('foo' => 'bar'));

		$this->assertFalse(
			TestHelper::invoke($this->StringInflector, 'getCachedPlural', 'bar'),
			'Checks for an uncached plural.'
		);

		$this->assertEquals(
			'bar',
			TestHelper::invoke($this->StringInflector, 'getCachedPlural', 'foo'),
			'Checks for a cached plural word.'
		);
	}

	/**
	 * Method to test StringInflector::getCachedSingular().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::getCachedSingular
	 * @since   2.0
	 */
	public function testGetCachedSingular()
	{
		// Reset the cache.
		TestHelper::setValue($this->StringInflector, 'cache', array('foo' => 'bar'));

		$this->assertFalse(
			TestHelper::invoke($this->StringInflector, 'getCachedSingular', 'foo'),
			'Checks for an uncached singular.'
		);

		$this->assertThat(
			TestHelper::invoke($this->StringInflector, 'getCachedSingular', 'bar'),
			$this->equalTo('foo'),
			'Checks for a cached singular word.'
		);
	}

	/**
	 * Method to test StringInflector::matchRegexRule().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::matchRegexRule
	 * @since   2.0
	 */
	public function testMatchRegexRule()
	{
		$this->assertThat(
			TestHelper::invoke($this->StringInflector, 'matchRegexRule', 'xyz', 'plural'),
			$this->equalTo('xyzs'),
			'Checks pluralising against the basic regex.'
		);

		$this->assertThat(
			TestHelper::invoke($this->StringInflector, 'matchRegexRule', 'xyzs', 'singular'),
			$this->equalTo('xyz'),
			'Checks singularising against the basic regex.'
		);

		$this->assertFalse(
			TestHelper::invoke($this->StringInflector, 'matchRegexRule', 'xyz', 'singular'),
			'Checks singularising against an unmatched regex.'
		);
	}

	/**
	 * Method to test StringInflector::setCache().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::setCache
	 * @since   2.0
	 */
	public function testSetCache()
	{
		TestHelper::invoke($this->StringInflector, 'setCache', 'foo', 'bar');

		$cache = TestHelper::getValue($this->StringInflector, 'cache');

		$this->assertThat(
			$cache['foo'],
			$this->equalTo('bar'),
			'Checks the cache was set.'
		);

		TestHelper::invoke($this->StringInflector, 'setCache', 'foo', 'car');

		$cache = TestHelper::getValue($this->StringInflector, 'cache');

		$this->assertThat(
			$cache['foo'],
			$this->equalTo('car'),
			'Checks an existing value in the cache was reset.'
		);
	}

	/**
	 * Method to test StringInflector::addCountableRule().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::addCountableRule
	 * @since   2.0
	 */
	public function testAddCountableRule()
	{
		// Add string.
		$this->StringInflector->addCountableRule('foo');

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertContains(
			'foo',
			$rules['countable'],
			'Checks a countable rule was added.'
		);

		// Add array.
		$this->StringInflector->addCountableRule(array('goo', 'car'));

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertContains(
			'car',
			$rules['countable'],
			'Checks a countable rule was added by array.'
		);
	}

	/**
	 * Method to test StringInflector::addWord().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::addWord
	 * @since   2.0
	 */
	public function testAddWord()
	{
		$this->assertEquals(
			$this->StringInflector,
			$this->StringInflector->addWord('foo')
		);

		$cache = TestHelper::getValue($this->StringInflector, 'cache');

		$this->assertArrayHasKey('foo', $cache);

		$this->assertEquals(
			'foo',
			$cache['foo']
		);

		$this->assertEquals(
			$this->StringInflector,
			$this->StringInflector->addWord('bar', 'foo')
		);

		$cache = TestHelper::getValue($this->StringInflector, 'cache');

		$this->assertArrayHasKey('bar', $cache);

		$this->assertEquals(
			'foo',
			$cache['bar']
		);
	}

	/**
	 * Method to test StringInflector::addPluraliseRule().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::addPluraliseRule
	 * @since   2.0
	 */
	public function testAddPluraliseRule()
	{
		$chain = $this->StringInflector->addPluraliseRule(array('/foo/', '/bar/'));

		$this->assertThat(
			$chain,
			$this->identicalTo($this->StringInflector),
			'Checks chaining.'
		);

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertCOntains(
			'/bar/',
			$rules['plural'],
			'Checks a pluralisation rule was added.'
		);
	}

	/**
	 * Method to test StringInflector::addSingulariseRule().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::addSingulariseRule
	 * @since   2.0
	 */
	public function testAddSingulariseRule()
	{
		$chain = $this->StringInflector->addSingulariseRule(array('/foo/', '/bar/'));

		$this->assertThat(
			$chain,
			$this->identicalTo($this->StringInflector),
			'Checks chaining.'
		);

		$rules = TestHelper::getValue($this->StringInflector, 'rules');

		$this->assertContains(
			'/bar/',
			$rules['singular'],
			'Checks a singularisation rule was added.'
		);
	}

	/**
	 * Method to test StringInflector::getInstance().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::getInstance
	 * @since   2.0
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'Windwalker\\String\\StringInflector',
			StringInflector::getInstance(),
			'Check getInstance returns the right class.'
		);

		// Inject an instance an test.
		TestHelper::setValue($this->StringInflector, 'instance', new \stdClass);

		$this->assertThat(
			StringInflector::getInstance(),
			$this->equalTo(new \stdClass),
			'Checks singleton instance is returned.'
		);

		$this->assertInstanceOf(
			'Windwalker\\String\\StringInflector',
			StringInflector::getInstance(true),
			'Check getInstance a fresh object with true argument even though the instance is set to something else.'
		);
	}

	/**
	 * Method to test StringInflector::isCountable().
	 *
	 * @param   string   $input     A string.
	 * @param   boolean  $expected  The expected result of the function call.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::isCountable
	 * @dataProvider  seedIsCountable
	 * @since   2.0
	 */
	public function testIsCountable($input, $expected)
	{
		$this->assertThat(
			$this->StringInflector->isCountable($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Method to test StringInflector::isPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::isPlural
	 * @dataProvider  seedSinglePlural
	 * @since   2.0
	 */
	public function testIsPlural($singular, $plural)
	{
		$this->assertTrue(
			$this->StringInflector->isPlural($plural),
			'Checks the plural is a plural.'
		);

		if ($singular != $plural)
		{
			$this->assertFalse(
				$this->StringInflector->isPlural($singular),
				'Checks the singular is not plural.'
			);
		}
	}

	/**
	 * Method to test StringInflector::isSingular().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::isSingular
	 * @dataProvider  seedSinglePlural
	 * @since   2.0
	 */
	public function testIsSingular($singular, $plural)
	{
		$this->assertTrue(
			$this->StringInflector->isSingular($singular),
			'Checks the singular is a singular.'
		);

		if ($singular != $plural)
		{
			$this->assertFalse(
				$this->StringInflector->isSingular($plural),
				'Checks the plural is not singular.'
			);
		}
	}

	/**
	 * Method to test StringInflector::toPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::toPlural
	 * @dataProvider  seedSinglePlural
	 * @since   2.0
	 */
	public function testToPlural($singular, $plural)
	{
		$this->assertThat(
			$this->StringInflector->toPlural($singular),
			$this->equalTo($plural)
		);
	}

	/**
	 * Method to test StringInflector::toPlural().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::toPlural
	 * @since   2.0
	 */
	public function testToPluralAlreadyPlural()
	{
		$this->assertFalse($this->StringInflector->toPlural('buses'));
	}

	/**
	 * Method to test StringInflector::toPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::toSingular
	 * @dataProvider  seedSinglePlural
	 * @since   2.0
	 */
	public function testToSingular($singular, $plural)
	{
		$this->assertThat(
			$this->StringInflector->toSingular($plural),
			$this->equalTo($singular)
		);
	}

	/**
	 * Method to test StringInflector::toPlural().
	 *
	 * @return  void
	 *
	 * @covers  Windwalker\String\StringInflector::toSingular
	 * @since   2.0
	 */
	public function testToSingularRetFalse()
	{
		// Assertion for already singular
		$this->assertFalse($this->StringInflector->toSingular('bus'));

		$this->assertFalse($this->StringInflector->toSingular('foo'));
	}
}
