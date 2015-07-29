<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Descriptor;

/**
 * The abstract base descriptor.
 *
 * @since  2.0
 */
abstract class AbstractDescriptor implements DescriptorInterface
{
	/**
	 * Waiting described items.
	 *
	 * @var  array
	 *
	 * @since  2.0
	 */
	public $items = array();

	/**
	 * Add a item to describe.
	 *
	 * @param   mixed  $item  The item you want to describe.
	 *
	 * @return  DescriptorInterface  Return this object to support chaining.
	 *
	 * @since  2.0
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
	 * @since   2.0
	 */
	abstract protected function renderItem($item);

	/**
	 * Render all items description.
	 *
	 * @return  string
	 *
	 * @since   2.0
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
