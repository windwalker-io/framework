<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\DataMapper\Entity\Entity;
use Windwalker\DataMapper\Test\Stub\StubEntity;

/**
 * Test class of Entity
 *
 * @since 2.0
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var Entity
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
		$this->instance = new Entity($this->getTestFields(), $this->getTestData());
	}

	/**
	 * getTestFields
	 *
	 * @return  array
	 */
	protected function getTestFields()
	{
		return array(
			'id',
			'title',
			'content'
		);
	}

	/**
	 * getTestData
	 *
	 * @return  array
	 */
	protected function getTestData()
	{
		return array(
			'id' => 5,
			'title' => 'Sakura',
			'content' => 'foo',
			'created' => '2014-08-17',
			'user' => 255,
			'params' => '{}'
		);
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
		$dump = $this->instance->dump();

		$this->assertEquals('Sakura', $this->instance->title);
		$this->assertEquals(false, isset($dump['user']));
	}

	/**
	 * Method to test addFields().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\DataMapper\Entity\Entity::addFields
	 */
	public function testAddFields()
	{
		$entity = new Entity;

		$entity->addFields($this->getTestFields());

		$entity->bind($this->getTestData());

		$entity = $entity->dump();

		$this->assertFalse(isset($entity['params']));
		$this->assertEquals('Sakura', $entity['title']);
	}

	/**
	 * Method to test addField().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\DataMapper\Entity\Entity::addField
	 */
	public function testAddField()
	{
		$this->instance->addField('user');

		$this->instance->bind($this->getTestData());

		$this->assertEquals(255, $this->instance->user);
	}

	/**
	 * Method to test removeField().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\DataMapper\Entity\Entity::removeField
	 */
	public function testRemoveField()
	{
		$this->instance->removeField('content');

		$this->instance->bind($this->getTestData());

		$data = $this->instance->dump();

		$this->assertFalse(isset($data['content']));
	}

	/**
	 * testAccessor
	 *
	 * @return  void
	 */
	public function testAccessor()
	{
		$entity = new StubEntity;

		$this->assertEquals('foo_bar', $entity->foo_bar);
	}

	/**
	 * testMutator
	 *
	 * @return  void
	 */
	public function testMutator()
	{
		$entity = new StubEntity;

		$entity->flower_sakura = 'yoo';

		$this->assertEquals('yoo_bar', $entity->flower_sakura);
	}

	/**
	 * testJsonSerialize
	 *
	 * @return  void
	 */
	public function testJsonSerialize()
	{
		if (version_compare(PHP_VERSION, '5.4', '<'))
		{
			$this->markTestSkipped('PHP 5.3 do not support JsonSerialize()');

			return;
		}

		$this->assertEquals(json_encode($this->instance->dump(true)), json_encode($this->instance));
	}
}
