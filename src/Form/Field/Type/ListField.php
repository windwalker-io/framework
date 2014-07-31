<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field\Type;

use Windwalker\Dom\DomElements;
use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Form\Field\AbstractField;
use Windwalker\Html\Option;
use Windwalker\Html\Select\SelectList;

/**
 * The ListField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class ListField extends AbstractField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'list';

	/**
	 * Property options.
	 *
	 * @var  Option[]
	 */
	protected $options = array();

	/**
	 * @param string $name
	 * @param null   $label
	 * @param array  $options
	 * @param array  $attributes
	 * @param null   $filter
	 * @param null   $rule
	 */
	public function __construct($name, $label = null, $options = array(), $attributes = array(), $filter = null, $rule = null)
	{
		parent::__construct($name, $label, $attributes, $filter, $rule);

		$this->handleOptions($name, $options);
	}

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepareAttributes(&$attrs)
	{
		$attrs['name']     = $this->getFieldName();
		$attrs['id']       = $this->getAttribute('id', $this->getId());
		$attrs['class']    = $this->getAttribute('class');
		$attrs['size']     = $this->getAttribute('size');
		$attrs['readonly'] = $this->getAttribute('readonly');
		$attrs['disabled'] = $this->getAttribute('disabled');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['multiple'] = $this->getAttribute('multiple');
	}

	/**
	 * buildInput
	 *
	 * @param array $attrs
	 *
	 * @return  mixed|void
	 */
	public function buildInput($attrs)
	{
		$options = $this->getOptions();

		return new SelectList($this->getFieldName(), $options, $this->attributes, $this->getValue());
	}

	/**
	 * getOptions
	 *
	 * @return  array|Option[]
	 */
	protected function getOptions()
	{
		return array_merge($this->options, $this->prepareOptions());
	}

	/**
	 * prepareOptions
	 *
	 * @return  array|Option[]
	 */
	protected function prepareOptions()
	{
		return array();
	}

	/**
	 * prepareOptions
	 *
	 * @param string|\SimpleXMLElement $xml
	 * @param Option[]            $options
	 *
	 * @throws \InvalidArgumentException
	 * @return  void
	 */
	protected function handleOptions($xml, $options = array())
	{
		if ($xml instanceof \SimpleXMLElement)
		{
			foreach ($xml->children() as $option)
			{
				$attributes = XmlHelper::getAttributes($option);

				$option = new Option((string) $option, XmlHelper::getAttribute($option, 'value'), $attributes);

				$this->options[] = $option;
			}
		}

		else
		{
			foreach ($options as $option)
			{
				if (!($option instanceof Option))
				{
					throw new \InvalidArgumentException('Please give me Option as options.');
				}
			}

			$this->options = $options;
		}
	}
}
 