<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Descriptor;

/**
 * The abstract base descriptor.
 *
 * @since  1.0
 */
abstract class AbstractDescriptor implements DescriptorInterface
{
	/**
	 * Waiting described items.
	 *
	 * @var  array
	 *
	 * @since  1.0
	 */
	public $items = array();

	/**
	 * Add a item to describe.
	 *
	 * @param   mixed  $item  The item you want to describe.
	 *
	 * @return  DescriptorInterface  Return this object to support chaining.
	 *
	 * @since  1.0
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
	 * @since   1.0
	 */
	abstract protected function renderItem($item);

	/**
	 * Render all items description.
	 *
	 * @return  string
	 *
	 * @since   1.0
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
