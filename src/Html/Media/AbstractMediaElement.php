<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Media;

use Windwalker\Dom\Builder\HtmlBuilder;
use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\HtmlElements;

/**
 * The AbstractMediaElement class.
 * 
 * @since  2.1
 */
abstract class AbstractMediaElement extends HtmlElement
{
	const PRELOAD_NONE     = 'none';
	const PRELOAD_METADATA = 'metadata';
	const PRELOAD_AUTO     = 'auto';

	/**
	 * Property hint.
	 *
	 * @var  string
	 */
	protected $hint;

	/**
	 * Constructor.
	 *
	 * @param array   $attribs
	 */
	public function __construct($attribs = array())
	{
		parent::__construct($this->name, null, $attribs);

		$this->content = new HtmlElements;
	}

	/**
	 * Quick create for PHP 5.3
	 *
	 * @param array $attribs
	 *
	 * @return  static
	 */
	public static function create($attribs = array())
	{
		return new static($attribs);
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
		$content = $this->content;

		$content = $content . $this->hint;

		return HtmlBuilder::create($this->name, $content, $this->attribs, $forcePair);
	}

	/**
	 * setMainSource
	 *
	 * @param  string  $src
	 *
	 * @return  static
	 */
	public function setMainSource($src)
	{
		$this->setAttribute('src', $src);

		return $this;
	}

	/**
	 * addSource
	 *
	 * @param string $type
	 * @param string $src
	 * @param string $media
	 *
	 * @return  $this
	 */
	public function addSource($type, $src, $media = null)
	{
		$this->content[] = new HtmlElement('source', null, array(
			'src'   => $src,
			'type'  => $this->name . '/' . strtolower($type),
			'media' => $media
		));

		return $this;
	}

	/**
	 * addOggSource
	 *
	 * @param string $src
	 * @param string $media
	 *
	 * @return  static
	 */
	public function addOggSource($src, $media = null)
	{
		return $this->addSource('ogg', $src, $media);
	}

	/**
	 * Method to get property Hint
	 *
	 * @return  string
	 */
	public function getNoSupportHint()
	{
		return $this->hint;
	}

	/**
	 * Method to set property hint
	 *
	 * @param   string $hint
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setNoSupportHint($hint)
	{
		$this->hint = $hint;

		return $this;
	}

	/**
	 * autoplay
	 *
	 * @param   boolean  $bool
	 *
	 * @return  static
	 */
	public function autoplay($bool)
	{
		$this->attribs['autoplay'] = (bool) $bool;

		return $this;
	}

	/**
	 * controls
	 *
	 * @param   boolean  $bool
	 *
	 * @return  static
	 */
	public function controls($bool)
	{
		$this->attribs['controls'] = (bool) $bool;

		return $this;
	}

	/**
	 * loop
	 *
	 * @param   boolean  $bool
	 *
	 * @return  static
	 */
	public function loop($bool)
	{
		$this->attribs['loop'] = (bool) $bool;

		return $this;
	}

	/**
	 * muted
	 *
	 * @param   boolean  $bool
	 *
	 * @return  static
	 */
	public function muted($bool)
	{
		$this->attribs['muted'] = (bool) $bool;

		return $this;
	}

	/**
	 * preload
	 *
	 * @param   string  $data
	 *
	 * @return  static
	 */
	public function preload($data)
	{
		$this->attribs['preload'] = $data;

		return $this;
	}
}
