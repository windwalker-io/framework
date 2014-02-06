<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Descriptor;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Console\Command\Command;
use Joomla\Console\Descriptor\Text\TextCommandDescriptor;

/**
 * Class Option Descriptor
 */
class CommandDescriptor extends TextCommandDescriptor
{
	/**
	 * Render all items description.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function render()
	{
		// Count the max command length as column width.
		foreach ($this->items as $item)
		{
			/** @var $item AbstractCommand */
			$length = strlen($item->getName());

			if ($length > $this->maxLength)
			{
				$this->maxLength = $length;
			}
		}

		$description = array();

		foreach ($this->items as $item)
		{
			$currentLevel = $this->renderItem($item);

			$children = array();

			foreach ($item->getChildren() as $child)
			{
				$children[] = $this->renderItem($child);
			}

			if ($children)
			{
				$children = implode("\n", $children);
				$children = str_replace("\n", "\n  ", $children);

				$description[] = $currentLevel . "\n  " . $children . "\n";
			}
			else
			{
				$description[] = $currentLevel;
			}
		}

		return implode("\n", $description);
	}
}
