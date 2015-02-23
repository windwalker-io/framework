<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Html\Select;

use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Option;

/**
 * The SelectList class.
 * 
 * @since  2.0
 */
class SelectList extends HtmlElement
{
	/**
	 * Property selected.
	 *
	 * @var  mixed
	 */
	protected $selected = null;

	/**
	 * Element content.
	 *
	 * @var  Option[]
	 */
	protected $content;

	/**
	 * Property multiple.
	 *
	 * @var  bool
	 */
	protected $multiple;

	/**
	 * Constructor
	 *
	 * @param string     $name
	 * @param mixed|null $options
	 * @param array      $attribs
	 * @param mixed      $selected
	 * @param bool       $multiple
	 */
	public function __construct($name, $options, $attribs = array(), $selected = null, $multiple = false)
	{
		$attribs['name'] = $name;

		$this->selected = $selected;
		$this->multiple = $multiple;

		parent::__construct('select', $options, $attribs);
	}

	/**
	 * toString
	 *
	 * @param bool $forcePair
	 *
	 * @return  string
	 */
	public function toString($forcePair = false)
	{
		$this->prepareOptions();

		if ($this->multiple)
		{
			$this->setAttribute('multiple', 'true');
			$this->setAttribute('name', $this->getAttribute('name') . '[]');
		}

		return parent::toString($forcePair);
	}

	/**
	 * prepareOptions
	 *
	 * @return  void
	 */
	protected function prepareOptions()
	{
		foreach ($this->content as $name => $option)
		{
			// Array means it is a group
			if (is_array($option))
			{
				foreach ($option as &$opt)
				{
					if ($this->checkSelected($opt->getValue()))
					{
						$opt['selected'] = 'selected';
					}
				}

				$this->content[$name] = new HtmlElement('optgroup', $option, array('label' => $name));
			}
			// Not array means it is an option
			else
			{
				if ($this->checkSelected($option->getValue()))
				{
					$option['selected'] = 'selected';
				}
			}
		}
	}

	/**
	 * checkSelected
	 *
	 * @param mixed $value
	 *
	 * @return  bool
	 */
	protected function checkSelected($value)
	{
		$value = (string) $value;

		if ($this->multiple)
		{
			return in_array($value, (array) $this->getSelected());
		}
		else
		{
			return $value == (string) $this->getSelected();
		}
	}

	/**
	 * Method to get property Selected
	 *
	 * @return  mixed
	 */
	public function getSelected()
	{
		return $this->selected;
	}

	/**
	 * Method to set property selected
	 *
	 * @param   mixed $selected
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setSelected($selected)
	{
		$this->selected = $selected;

		return $this;
	}
}

