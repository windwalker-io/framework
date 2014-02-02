<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Descriptor;

use Joomla\Console\Descriptor\Text\TextOptionDescriptor;
use Joomla\Console\Option\Option;

/**
 * Class Option Descriptor
 */
class OptionDescriptor extends TextOptionDescriptor
{
	/**
	 * The max length of command.
	 *
	 * @var int
	 *
	 * @since  1.0
	 */
	protected $maxLength = 0;

	/**
	 * Offset that between every commands and their descriptions.
	 *
	 * @var int
	 *
	 * @since  1.0
	 */
	protected $offsetAfterCommand = 4;

	/**
	 * Option description template.
	 *
	 * @var string
	 *
	 * @since  1.0
	 */
	protected $template = <<<EOF
  <info>%-{WIDTH}s</info>%s
EOF;

	/**
	 * Render an item description.
	 *
	 * @param   mixed  $option  The item to br described.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  string  Rendered description.
	 *
	 * @since   1.0
	 */
	protected function renderItem($option)
	{
		if (!($option instanceof Option))
		{
			throw new \InvalidArgumentException('Command descriptor need Command object to describe it.');
		}

		/** @var Command $command */
		$name        = $option->getName();
		$description = $option->getDescription() ?: 'No description';
		$aliases     = $option->getAlias();

		// Merge aliases
		array_unshift($aliases, $name);

		foreach ($aliases as &$alias)
		{
			$alias = strlen($alias) > 1 ? '--' . $alias : '-' . $alias;
		}

		$name = implode(' | ', $aliases);

		$template = str_replace('{WIDTH}', $this->maxLength + $this->offsetAfterCommand, $this->template);

		// Sets the body indent.
		$body = array();

		$description = explode("\n", $description);

		$line1  = array_shift($description);
		$body[] = sprintf($template, $name, $line1);

		foreach ($description as $line)
		{
			$line = trim($line);
			$line = sprintf($template, '', $line);
			$body[] = $line;
		}

		return implode("\n", $body);
	}

	/**
	 * Render all items description.
	 *
	 * @return  string
	 *
	 * @since  1.0
	 */
	public function render()
	{
		// Count the max command length as column width.
		foreach ($this->items as $item)
		{
			$name        = $item->getName();
			$aliases     = $item->getAlias();

			// Merge aliases
			array_unshift($aliases, $name);

			foreach ($aliases as &$alias)
			{
				$alias = strlen($alias) > 1 ? '--' . $alias : '-' . $alias;
			}

			$name = implode(' | ', $aliases);

			/** @var $item Option */
			$length = strlen($name);

			if ($length > $this->maxLength)
			{
				$this->maxLength = $length;
			}
		}

		return parent::render();
	}
}
