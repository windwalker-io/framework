<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Select;

use Windwalker\Dom\Builder\HtmlBuilder;
use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\HtmlElements;
use Windwalker\Html\Option;

/**
 * The InputList class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class AbstractInputList extends HtmlElement
{
	/**
	 * Property selected.
	 *
	 * @var  mixed
	 */
	protected $checked = null;

	/**
	 * Element content.
	 *
	 * @var  Option[]
	 */
	protected $content = array();

	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = '';

	/**
	 * Constructor
	 *
	 * @param string     $name
	 * @param mixed|null $options
	 * @param array      $attribs
	 * @param mixed      $checked
	 */
	public function __construct($name, $options, $attribs = array(), $checked = null)
	{
		$attribs['name'] = $name;

		$this->checked = $checked;

		parent::__construct('span', $options, $attribs);
	}

	/**
	 * prepareOptions
	 *
	 * @return  void
	 */
	protected function prepareOptions()
	{
		foreach ($this->content as &$option)
		{
			if ($option->getValue() == $this->getChecked())
			{
				$option['checked'] = 'checked';
			}

			$attrs = $option->getAttributes();

			$label = $this->createLabel($option);

			$attrs['type'] = $this->type;

			$input = new HtmlElement('input', '', $attrs);

			$option = new HtmlElements(array($input, $label));
		}
	}

	/**
	 * toString
	 *
	 * @param boolean $forcePair
	 *
	 * @return  string
	 */
	public function toString($forcePair = false)
	{
		$this->prepareOptions();

		$attrs['id'] = $this->getAttribute('id');
		$attrs['class'] = $this->type . '-inputs ' . $this->getAttribute('class');

		return HtmlBuilder::create($this->name, $this->content, $attrs, $forcePair);
	}

	/**
	 * createLabel
	 *
	 * @param Option $option
	 *
	 * @return  Htmlelement
	 */
	protected function createLabel($option)
	{
		$attrs = $option->getAttributes();

		$attrs['id'] = $option->getAttribute('id') . '-label';
		$attrs['for'] = $option->getAttribute('id');

		return new HtmlElement('label', $option->getContent(), $attrs);
	}

	/**
	 * Method to get property Checked
	 *
	 * @return  mixed
	 */
	public function getChecked()
	{
		return $this->checked;
	}

	/**
	 * Method to set property checked
	 *
	 * @param   mixed $checked
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setChecked($checked)
	{
		$this->checked = $checked;

		return $this;
	}
}
 