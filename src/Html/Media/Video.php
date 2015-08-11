<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Media;

/**
 * The Video class.
 * 
 * @since  2.1
 */
class Video extends AbstractMediaElement
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'video';

	/**
	 * addMp4Source
	 *
	 * @param string $src
	 * @param string $media
	 *
	 * @return  static
	 */
	public function addMp4Source($src, $media = null)
	{
		return $this->addSource('mp4', $src, $media);
	}

	/**
	 * addWebMSource
	 *
	 * @param string $src
	 * @param string $media
	 *
	 * @return  static
	 */
	public function addWebMSource($src, $media = null)
	{
		return $this->addSource('webm', $src, $media);
	}

	/**
	 * poster
	 *
	 * @param   string  $data
	 *
	 * @return  static
	 */
	public function poster($data)
	{
		$this->attribs['poster'] = $data;

		return $this;
	}

	/**
	 * height
	 *
	 * @param   string  $data
	 *
	 * @return  static
	 */
	public function height($data)
	{
		$this->attribs['height'] = $data;

		return $this;
	}

	/**
	 * width
	 *
	 * @param   string  $data
	 *
	 * @return  static
	 */
	public function width($data)
	{
		$this->attribs['width'] = $data;

		return $this;
	}
}
