<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Descriptor;

/**
 * The abstract base descriptor.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractDescriptor implements DescriptorInterface
{
	/**
	 * Waiting described items.
	 *
	 * @var  array
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public $items = array();

	/**
	 * Add a item to describe.
	 *
	 * @param   mixed  $item  The item you want to describe.
	 *
	 * @return  DescriptorInterface  Return this object to support chaining.
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	public function addItem($item)
	{
		$this->items[] = $item;

		return $this;
	}

	/**
	 * Render an item description.
	 *
	 * @param   mixed  $item  The item to br described.
	 *
	 * @return  string
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	abstract protected function renderItem($item);

	/**
	 * Render all items description.
	 *
	 * @return  string
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function render()
	{
		$description = array();

		foreach ($this->items as $item)
		{
			$description[] = $this->renderItem($item);
		}

		return implode("\n", $description);
	}
}
