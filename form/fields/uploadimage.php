<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Form
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Supports an upload image field, and if file exists, will show this image..
 *
 * @package     Windwalker.Framework
 * @subpackage  Form
 */
class JFormFieldUploadimage extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	public $type = 'Uploadimage';

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldFile
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$accept   = $this->element['accept'] ? ' accept="' . (string) $this->element['accept'] . '"' : '';
		$size     = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$class    = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$readonly = (string) $this->element['readonly'];
		$value    = $this->value;

		$width  = $this->element['width'] ? $this->element['width'] : 150;
		$height = $this->element['height'] ? $this->element['height'] : 150;
		$crop   = $this->element['crop'] ? $this->element['crop'] : 1;

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		if ($readonly != 'false' && $readonly)
		{
			return JHtml::image($this->value, $this->name, array('width' => 150));
		}
		else
		{
			$html = '';

			if ($this->value)
			{
				$html .= '<div class="image-' . $this->id . '">' . JHtml::image(AKHelper::_('thumb.resize', $this->value, $width, $height, $crop), $this->name, array()) . '</div>';
			}

			$html .= '<input type="file" name="' . $this->getName($this->element['name'] . '_upload') . '" id="' . $this->id . '"' . ' value=""' . $accept . $disabled . $class . $size
				. $onchange . ' />';

			$html .= '<label><input type="checkbox" name="' . $this->getName($this->element['name'] . '_delete') . '" id="' . $this->id . '"' . ' value="1" />' . JText::_('JACTION_DELETE') . '</label>';
			$html .= '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" />';

			return $html;
		}

	}

	/**
	 * Method to attach a JForm object to the field.
	 *  Catch upload files when form setup.
	 *
	 * @param   object &$element    The JXmlElement object representing the <field /> tag for the form field object.
	 * @param   mixed  $value       The form field value to validate.
	 * @param   string $group       The field name group control value. This acts as as an array container for the field.
	 *                              For example if the field has name="foo" and the group value is set to "bar" then the
	 *                              full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);

		if (JRequest::getVar($this->element['name'] . '_delete') == 1)
		{
			$this->value = '';
		}
		else
		{
			// Upload Image
			// ===============================================
			if (isset($_FILES['jform']['name']['profile']))
			{
				foreach ($_FILES['jform']['name']['profile'] as $key => $var):

					if (!$var)
					{
						continue;
					}

					// Get Field Attr
					$width  = $this->element['save_width'] ? $this->element['save_width'] : 800;
					$height = $this->element['save_height'] ? $this->element['save_height'] : 800;

					// Build File name
					$src  = $_FILES['jform']['tmp_name']['profile'][$key];
					$var  = explode('.', $var);
					$date = JFactory::getDate('now', JFactory::getConfig()->get('offset'));
					$name = md5((string) $date . $width . $height . $src) . '.' . array_pop($var);
					$url  = "images/cck/{$date->year}/{$date->month}/{$date->day}/" . $name;

					// A Event for extend.
					JFactory::getApplication()->triggerEvent('onCCKEngineUploadImage', array(&$url, &$this, &$this->element));

					$dest = JPATH_ROOT . '/' . $url;

					// Upload First
					JFile::upload($src, $dest);

					// Resize image
					$img = new JImage;
					$img->loadFile(JPATH_ROOT . '/' . $url);
					$img = $img->resize($width, $height);

					switch (array_pop($var))
					{
						case 'gif':
							$type = IMAGETYPE_GIF;
							break;
						case 'png':
							$type = IMAGETYPE_PNG;
							break;
						default :
							$type = IMAGETYPE_JPEG;
							break;
					}

					// Save
					$img->toFile($dest, $type, array('quality' => 85));

					// Set in Value
					$this->value = $url;
				endforeach;

			}
		}

		return true;
	}

	/**
	 * Show image for com_users.
	 */
	public static function showImage($value)
	{
		if ($value)
		{
			return JHtml::image($value, 'image');
		}
	}
}
