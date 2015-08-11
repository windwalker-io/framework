<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Enum;

use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\HtmlElements;

/**
 * The AbstractHtmlList class.
 * 
 * @since  2.1
 */
abstract class AbstractHtmlList extends HtmlElement
{
	/**
	 * Constructor.
	 *
	 * @param ListItem[] $items
	 * @param array      $attribs
	 */
	public function __construct($items = array(), $attribs = array())
	{
		parent::__construct($this->name, null, $attribs);

		$this->setItems((array) $items);
	}

	/**
	 * Quick create for PHP 5.3
	 *
	 * @param   array  $attribs
	 *
	 * @return  static
	 */
	public static function create($attribs = array())
	{
		return new static($attribs);
	}

	/**
	 * addItem
	 *
	 * @param   ListItem|string  $item
	 * @param   array            $attribs
	 *
	 * @return  static
	 */
	public function addItem($item, $attribs = array())
	{
		if (!$item instanceof ListItem)
		{
			$item = new ListItem($item, $attribs);
		}

		$this->content[] = $item;

		return $this;
	}

	/**
	 * setItems
	 *
	 * @param   ListItem[] $items
	 *
	 * @return  static
	 */
	public function setItems(array $items)
	{
		$this->content = new HtmlElements;

		foreach ($items as $item)
		{
			$this->addItem($item);
		}

		return $this;
	}
}
