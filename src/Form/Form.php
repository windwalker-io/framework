<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form;

use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Form\Field\FieldHelper;
use Windwalker\Form\Field\FieldInterface;

/**
 * The Form class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Form
{
	/**
	 * Property fields.
	 *
	 * @var  array
	 */
	protected $fields = array();

	/**
	 * Property xml.
	 *
	 * @var  \SimpleXmlElement
	 */
	protected $xml = null;

	public function load($xml)
	{
		$this->xml = simplexml_load_string($xml);

		$this->addFields($this->xml);
	}

	public function loadFile($file)
	{
		$this->load(file_get_contents($file));
	}

	public function addFields(\Traversable $xml)
	{
		$fields = $xml->xpath('//field');

		foreach ($fields as $fieldXml)
		{
			/** @var $field \SimpleXMLElement */
			// show(FormHelper::encode($fieldXml->asXML()));

			$this->addField($fieldXml);
		}
	}

	public function addField($field)
	{
		if ($field instanceof \SimpleXMLElement)
		{
			$this->fields[] = FieldHelper::createByXml($field);
		}
		elseif ($field instanceof FieldInterface)
		{
			$xml = new \SimpleXMLElement('<root>' . $field . '</root>');

			$this->fields[] = FieldHelper::createByXml($xml);
		}
		elseif (is_string($field))
		{

		}
		else
		{
			throw new \InvalidArgumentException(__CLASS__ . '::addField() need FieldInterface or SimpleXMLElement.');
		}

		return $this;
	}
}
 