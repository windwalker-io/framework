<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Test\Stub;

use Windwalker\Event\Event;

/**
 * The StubDataMapperListener class.
 *
 * @since  {DEPLOY_VERSION}
 */
class StubDataMapperListener
{
	/**
	 * Property lastEvent.
	 *
	 * @var  Event
	 */
	public $beforeEvent;

	/**
	 * Property afterEvent.
	 *
	 * @var  Event
	 */
	public $afterEvent;

	/**
	 * onFind
	 *
	 * @param Event $event
	 *
	 * @return  void
	 */
	public function onBeforeFind(Event $event)
	{
		$this->beforeEvent = clone $event;

		$event['limit'] = 20;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
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
		$this->beforeEvent = clone $event;
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
		$this->afterEvent = clone $event;
	}
}
