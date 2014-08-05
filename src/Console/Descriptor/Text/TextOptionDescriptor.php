<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Console\Descriptor\Text;

use Windwalker\Console\Descriptor\AbstractDescriptor;
use Windwalker\Console\Option\Option;

/**
 * Class Option AbstractDescriptor
 *
 * @since    {DEPLOY_VERSION}
 */
class TextOptionDescriptor extends AbstractDescriptor
{
	/**
	 * Option description template.
	 *
	 * @var string
	 *
	 * @since  {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since  {DEPLOY_VERSION}
	 */
	public function render()
	{
		return parent::render();
	}
}
