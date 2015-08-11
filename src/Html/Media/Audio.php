<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Media;

/**
 * The Audio class.
 * 
 * @since  2.1
 */
class Audio extends AbstractMediaElement
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'audio';

	/**
	 * addMp3Source
	 *
	 * @param string $src
	 * @param string $media
	 *
	 * @return  static
	 */
	public function addMp3Source($src, $media = null)
	{
		return $this->addSource('mpeg', $src, $media);
	}

	/**
	 * addWavSource
	 *
	 * @param string $src
	 * @param string $media
	 *
	 * @return  static
	 */
	public function addWavSource($src, $media = null)
	{
		return $this->addSource('wav', $src, $media);
	}
}
