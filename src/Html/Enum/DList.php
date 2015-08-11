<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Enum;

/**
 * The DList class.
 * 
 * @since  2.1
 */
class DList extends AbstractHtmlList
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'dl';

	/**
	 * addDescription
	 *
	 * @param string $title
	 * @param string $description
	 * @param array  $titleAttribs
	 * @param array  $descAttribs
	 *
	 * @return  $this
	 */
	public function addDescription($title, $description, $titleAttribs = array(), $descAttribs = array())
	{
		$this->addTitle($title, $titleAttribs)
			->addDesc($description, $descAttribs);

		return $this;
	}

	/**
	 * addItem
	 *
	 * @param   DListDescription|string  $item
	 * @param   array                    $attribs
	 *
	 * @return  static
	 */
	public function addDesc($item, $attribs = array())
	{
		if (!$item instanceof DListDescription)
		{
			$item = new DListDescription($item, $attribs);
		}

		$this->content[] = $item;

		return $this;
	}

	/**
	 * addItem
	 *
	 * @param   DListTitle|string  $item
	 * @param   array              $attribs
	 *
	 * @return  static
	 */
	public function addTitle($item, $attribs = array())
	{
		if (!$item instanceof DListTitle)
		{
			$item = new DListTitle($item, $attribs);
		}

		$this->content[] = $item;

		return $this;
	}

	/**
	 * addItem
	 *
	 * @param   DListTitle|string  $item
	 * @param   array              $attribs
	 *
	 * @return  static
	 */
	public function addItem($item, $attribs = array())
	{
		if ($item instanceof DListTitle)
		{
			$this->addTitle($item);
		}
		else
		{
			$this->addDesc($item, $attribs);
		}

		return $this;
	}
}
