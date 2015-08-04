<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Test;

use Windwalker\DataMapper\Test\Stub\StubDataMapperListener;
use Windwalker\DataMapper\Test\Stub\StubDispatcherAwareDatamapper;
use Windwalker\Event\ListenerMapper;
use Windwalker\Test\TestCase\AbstractBaseTestCase;

/**
 * The EventDispatcherTest class.
 *
 * @since  {DEPLOY_VERSION}
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

	public function testFind()
	{
		$this->instance->find(array('id' => 5), 'created', 5, 10);

		$this->assertEquals('onBeforeFind', $this->listener->beforeEvent->getName());
		$this->assertEquals(array('id' => 5), $this->listener->beforeEvent['conditions']);
		$this->assertEquals(array('created'), $this->listener->beforeEvent['order']);
		$this->assertEquals(5, $this->listener->beforeEvent['start']);
		$this->assertEquals(10, $this->listener->beforeEvent['limit']);
		$this->assertEquals(20, $this->instance->args[3]);

		$this->assertEquals('onAfterFind', $this->listener->afterEvent->getName());
		$this->assertEquals(array('doFind'), $this->listener->afterEvent['result']->method);
	}
}
