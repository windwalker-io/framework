<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Console\Descriptor\Text;

use Windwalker\Console\Descriptor\AbstractDescriptor;
use Windwalker\Console\Option\Option;

/**
 * Class Option AbstractDescriptor
 *
 * @since    1.0
 */
class TextOptionDescriptor extends AbstractDescriptor
{
	/**
	 * Option description template.
	 *
	 * @var string
	 *
	 * @since  1.0
	 */
	protected $template = <<<EOF
  <info>%s</info>
%s

EOF;

	/**
	 * The template of every description line.
	 *
	 * @var string
	 *
	 * @since  1.0
	 */
	protected $templateLineBody = '      %s';

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
			throw new \InvalidArgumentException('Option descriptor need Command object to describe it.');
		}

		/** @var Option $option */
		$name        = $option->getName();
		$aliases     = $option->getAlias();
		$description = $option->getDescription() ?: 'No description';

		// Merge aliases
		array_unshift($aliases, $name);

		foreach ($aliases as &$alias)
		{
			$alias = strlen($alias) > 1 ? '--' . $alias : '-' . $alias;
		}

		// Sets the body indent.
		$body = array();

		$description = explode("\n", $description);

		foreach ($description as $line)
		{
			$line = trim($line);
			$line = sprintf($this->templateLineBody, $line);
			$body[] = $line;
		}

		return sprintf($this->template, implode(' / ', $aliases), implode("\n", $body));
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
		return parent::render();
	}
}
