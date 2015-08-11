<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Test\Stub;

use Windwalker\Data\Data;
use Windwalker\Event\Event;

/**
 * The StubDataMapperListener class.
 *
 * @since  2.1
 */
class StubDataMapperListener
{
	/**
	 * Property lastEvent.
	 *
	 * @var  Event[]
	 */
	public $events;

	/**
	 * onFind
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeFind(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event->setArgument('limit', 20);
	}

	/**
	 * onAfterFind
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterFind(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'][] = array('method' => 'After');
	}

	/**
	 * onBeforeFindAll
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeFindAll(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['limit'] = 20;
	}

	/**
	 * onAfterFindAll
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterFindAll(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'][] = array('method' => 'After');
	}

	/**
	 * onBeforeFindOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeFindOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['order'] = 'id';
	}

	/**
	 * onAfterFindOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterFindOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result']->foo = 'after';
	}

	/**
	 * onBeforeFindColumn
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeFindColumn(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['column'] = 'bar';
	}

	/**
	 * onAfterFindColumn
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterFindColumn(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'After';
	}

	/**
	 * onBeforeCreate
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeCreate(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['dataset'] = array();
	}

	/**
	 * onAfterCreate
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterCreate(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeCreateOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeCreateOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['data'] = array();
	}

	/**
	 * onAfterCreateOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterCreateOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeUpdate
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeUpdate(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['confFields'] = 'state';
	}

	/**
	 * onAfterUpdate
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterUpdate(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeUpdateOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeUpdateOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['confFields'] = 'state';
	}

	/**
	 * onAfterUpdateOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterUpdateOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeSave
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeSave(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['confFields'] = 'state';
	}

	/**
	 * onAfterSave
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterSave(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeSaveOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeSaveOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['confFields'] = 'state';
	}

	/**
	 * onAfterSaveOne
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterSaveOne(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeFlush
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeFlush(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['conditions'] = array('state' => 1);
	}

	/**
	 * onAfterFlush
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterFlush(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}

	/**
	 * onBeforeDelete
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeDelete(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['conditions'] = array('state' => 1);
	}

	/**
	 * onAfterDelete
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onAfterDelete(Event $event)
	{
		$this->events[__FUNCTION__] = clone $event;

		$event['result'] = 'after';
	}
}
