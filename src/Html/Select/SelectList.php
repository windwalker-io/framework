<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
	public function __construct($name, $options = array(), $attribs = array(), $selected = null, $multiple = false)
	{
		$attribs['name'] = $name;

		$this->setSelected($selected);
		$this->setMultiple($multiple);

		parent::__construct('select', (array) $options, $attribs);
	}

	/**
	 * Quick create for PHP 5.3
	 *
	 * @param   array  $attribs
	 *
	 * @return  static
	 */
	public static function create($name, $attribs = array())
	{
		return new static($name, array(), $attribs);
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
		$tmpContent = clone $this->getContent();
		$tmpName    = $this->getAttribute('name');

		$this->prepareOptions();

		if ($this->multiple)
		{
			$this->setAttribute('name', $this->getAttribute('name') . '[]');
		}

		$html = parent::toString($forcePair);

		$this->setAttribute('name', $tmpName);
		$this->setContent($tmpContent);

		return $html;
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

	/**
	 * __clone
	 *
	 * @return  void
	 */
	public function __clone()
	{
		$this->content = clone $this->content;
	}

	/**
	 * Method to get property Multiple
	 *
	 * @return  boolean
	 */
	public function getMultiple()
	{
		return $this->multiple;
	}

	/**
	 * Method to set property multiple
	 *
	 * @param   boolean $multiple
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setMultiple($multiple)
	{
		$this->multiple = $multiple;

		$this->setAttribute('multiple', $multiple ? 'true' : 'false');

		return $this;
	}

	/**
	 * addOption
	 *
	 * @param Option $option
	 * @param string $group
	 *
	 * @return  static
	 */
	public function addOption(Option $option, $group = null)
	{
		if ($group)
		{
			$content = $this->content[$group];

			array_push($content, $option);

			$this->content[$group] = $content;
		}
		else
		{
			$this->content[] = $option;
		}

		return $this;
	}
}

