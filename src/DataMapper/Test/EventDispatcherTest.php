<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\DataMapper\Test\Stub\StubDataMapperListener;
use Windwalker\DataMapper\Test\Stub\StubDispatcherAwareDatamapper;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * The EventDispatcherTest class.
 *
 * @since  2.1
 */
class EventDispatcherTest extends AbstractBaseTestCase
{
	/**
	 * Property instance.
	 *
	 * @var  StubDispatcherAwareDatamapper
	 */
	protected $instance;

	/**
	 * Property listener.
	 *
	 * @var  StubDataMapperListener
	 */
	protected $listener;

	/**
	 * setUp
	 *
	 * @return  void
	 */
	public function setUp()
	{
		$this->instance = new StubDispatcherAwareDatamapper('stub');

		$this->listener = new StubDataMapperListener;

		$this->instance->getDispatcher()->addListener($this->listener);
	}

	/**
	 * testFind
	 *
	 * @return  void
	 */
	public function testFind()
	{
		$this->instance->find(array('id' => 5), 'created', 5, 10);

		$event = $this->listener->events['onBeforeFind'];

		// Before
		$this->assertEquals('onBeforeFind', $event->getName());
		$this->assertEquals(array('id' => 5), $event['conditions']);
		$this->assertEquals(array('created'), $event['order']);
		$this->assertEquals(5, $event['start']);
		$this->assertSame($event['mapper'], $this->instance);

		// Listener will change limit to 20
		$this->assertEquals(20, $event['limit']);
		$this->assertEquals(20, $this->instance->args[3]);

		$event = $this->listener->events['onAfterFind'];

		// After
		$this->assertEquals('onAfterFind', $event->getName());
		$this->assertEquals(array('doFind', 'After'), $event['result']->method);
		$this->assertSame($event['mapper'], $this->instance);
	}

	/**
	 * testFindAll
	 *
	 * @return  void
	 */
	public function testFindAll()
	{
		$this->instance->findAll('created', 5, 10);

		$event = $this->listener->events['onBeforeFindAll'];

		// Before
		$this->assertEquals('onBeforeFindAll', $event->getName());
		$this->assertEquals('created', $event['order']);
		$this->assertEquals(5, $event['start']);
		$this->assertSame($event['mapper'], $this->instance);

		// Listener will change limit to 20
		$this->assertEquals(20, $event['limit']);
		$this->assertEquals(20, $this->instance->args[3]);

		$event = $this->listener->events['onAfterFindAll'];

		// After
		$this->assertEquals('onAfterFindAll', $event->getName());
		$this->assertEquals(array('doFind', 'After', 'After'), $event['result']->method);
		$this->assertSame($event['mapper'], $this->instance);
	}

	/**
	 * testFindOne
	 *
	 * @return  void
	 */
	public function testFindOne()
	{
		$result = $this->instance->findOne(array('id' => 5), 'created');

		$event = $this->listener->events['onBeforeFindOne'];

		$this->assertEquals('onBeforeFindOne', $event->getName());
		$this->assertEquals(array('id' => 5), $event['conditions']);
		$this->assertEquals('id', $event['order']);
		$this->assertEquals(array('id'), $this->instance->args[1]);
		$this->assertSame($event['mapper'], $this->instance);

		$event = $this->listener->events['onAfterFindOne'];

		$this->assertEquals('onAfterFindOne', $event->getName());
		$this->assertEquals('doFind', $result->method);
		$this->assertEquals('after', $result->foo);
		$this->assertSame($event['mapper'], $this->instance);
	}

	/**
	 * testFindColumn
	 *
	 * @return  void
	 */
	public function testFindColumn()
	{
		$result = $this->instance->findColumn('foo', array('id' => 5), 'created', 5, 10);

		$event = $this->listener->events['onBeforeFindColumn'];

		// Before
		$this->assertEquals('onBeforeFindColumn', $event->getName());
		$this->assertEquals(array('id' => 5), $event['conditions']);
		$this->assertEquals('created', $event['order']);
		$this->assertEquals(5, $event['start']);
		$this->assertSame($event['mapper'], $this->instance);

		// Listener will change this
		$this->assertEquals('bar', $event['column']);

		$event = $this->listener->events['onAfterFindColumn'];

		// After
		$this->assertEquals('onAfterFindColumn', $event->getName());
		$this->assertEquals('After', $result);
		$this->assertSame($event['mapper'], $this->instance);
	}

	/**
	 * testCreate
	 *
	 * @return  void
	 */
	public function testCreate()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testCreateOne
	 *
	 * @return  void
	 */
	public function testCreateOne()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testUpdate
	 *
	 * @return  void
	 */
	public function testUpdate()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testUpdateOne
	 *
	 * @return  void
	 */
	public function testUpdateOne()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testUpdateAll
	 *
	 * @return  void
	 */
	public function testUpdateAll()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testSave
	 *
	 * @return  void
	 */
	public function testSave()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testSaveOne
	 *
	 * @return  void
	 */
	public function testSaveOne()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testDelete
	 *
	 * @return  void
	 */
	public function testDelete()
	{
		$this->markTestIncomplete();
	}

	/**
	 * testFlush
	 *
	 * @return  void
	 */
	public function testFlush()
	{
		$this->markTestIncomplete();
	}
}
