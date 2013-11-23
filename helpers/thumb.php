<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Setting some const.
$option = JRequest::getVar('option');
$params = JComponentHelper::getParams($option);

// Thumb cache path.
define('AKTHUMB_CACHE_PATH', JPath::clean(JPATH_ROOT . '/' . $params->get('thumb_cache_path', 'cache/thumbs/cache')));
// Thumb cache URL.
define('AKTHUMB_CACHE_URL', JURI::root() . $params->get('thumb_cache_url', 'cache/thumbs/cache'));
// Thumb temp path.
define('AKTHUMB_TEMP_PATH', JPath::clean(JPATH_ROOT . '/' . $params->get('thumb_temp_path', 'cache/thumbs/temp')));
// Thumb temp URL.
define('AKTHUMB_TEMP_URL', JURI::root() . $params->get('thumb_temp_url', 'cache/thumbs/temp'));

/**
 * A quick thumb generator, will return generated thumb url.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperThumb
{
	/**
	 * Thumb cache path.
	 *
	 * @var string
	 */
	public static $cache_path = AKTHUMB_CACHE_PATH;

	/**
	 * Thumb cache URL.
	 *
	 * @var string
	 */
	public static $cache_url = AKTHUMB_CACHE_URL;

	/**
	 * Thumb temp path.
	 *
	 * @var string
	 */
	public static $temp_path = AKTHUMB_TEMP_PATH;

	/**
	 * Thumb temp URL.
	 *
	 * @var string
	 */
	public static $temp_url = AKTHUMB_TEMP_URL;

	/**
	 * Default image URL.
	 * Use some placeholder to replace variable.
	 * - {width}    => Image width.
	 * - {height}   => Image height.
	 * - {zc}       => Crop or not.
	 * - {q}        => Image quality.
	 * - {file_type}=> File type.
	 *
	 * @var string
	 */
	public static $default_image = 'http://placehold.it/{width}x{height}';

	/**
	 * Resize an image, auto catch it from remote host and generate a new thumb in cache dir.
	 *
	 * @param   string  $url       Image URL, recommend a absolute URL.
	 * @param   integer $width     Image width, do not include 'px'.
	 * @param   integer $height    Image height, do not include 'px'.
	 * @param   boolean $zc        Crop or not.
	 * @param   integer $q         Image quality
	 * @param   string  $file_type File type.
	 *
	 * @return  string  The cached thumb URL.
	 */
	public static function resize($url = null, $width = 100, $height = 100, $zc = 0, $q = 85, $file_type = 'jpg')
	{
		if (!$url)
		{
			return self::getDefaultImage($width, $height, $zc, $q, $file_type);
		}
		$path = self::getImagePath($url);

		try
		{
			$img = new JImage();

			if (JFile::exists($path))
			{
				$img->loadFile($path);
			}
			else
			{
				return self::getDefaultImage($width, $height, $zc, $q, $file_type);
			}

			// get Width Height
			$imgdata = JImage::getImageFileProperties($path);

			// set save data
			if ($file_type != 'png' && $file_type != 'gif')
			{
				$file_type = 'jpg';
			}
			$file_name = md5($url . $width . $height . $zc . $q . implode('', (array) $imgdata)) . '.' . $file_type;
			$file_path = self::$cache_path . DS . $file_name;
			$file_url  = trim(self::$cache_url, '/') . '/' . $file_name;

			// img exists?
			if (JFile::exists($file_path))
			{
				return $file_url;
			}

			// crop
			if ($zc)
			{
				$img = self::crop($img, $width, $height, $imgdata);
			}

			// resize
			$img = $img->resize($width, $height);

			// save
			switch ($file_type)
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

			JFolder::create(self::$cache_path);
			$img->toFile($file_path, $type, array('quality' => $q));

			return $file_url;

		} catch (Exception $e)
		{

			if (JDEBUG)
			{
				echo $e->getMessage();
			}

			return self::getDefaultImage($width, $height, $zc, $q, $file_type);

		}
	}

	/**
	 * Get the origin image path, if is a remote image, will store in temp dir first.
	 *
	 * @param   string $url  The image URL.
	 * @param   string $hash Not available now..
	 *
	 * @return  string  Image path.
	 */
	public static function getImagePath($url, $hash = null)
	{
		$self = JFactory::getURI();
		$url  = JFactory::getURI($url);

		// is same host?
		if ($self->getHost() == $url->getHost())
		{

			$url  = $url->toString();
			$path = str_replace(JURI::root(), JPATH_ROOT . DS, $url);
			$path = JPath::clean($path);

		}
		elseif (!$url->getHost())
		{

			// no host
			$url  = $url->toString();
			$path = JPath::clean(JPATH_ROOT . DS . $url);

		}
		else
		{

			// other host
			$path = self::$temp_path . '/' . md5(JFile::getName($url)) . 'jpg';
			if (!JFile::exists($path))
			{
				$result = AKHelper::_('curl.getFile', (string) $url, $path);
			}
		}

		return $path;
	}

	/**
	 * Crop image, will count image with height percentage, and crop from middle.
	 *
	 * @param   JImage  A JImage object.
	 * @param   integer Target width.
	 * @param   integer Target height.
	 * @param   object  Image information.
	 *
	 * @return  JImage Croped image object.
	 */
	public static function crop($img, $w, $h, $data)
	{
		$p = $w / $h;

		$oH = $data->height;
		$oW = $data->width;
		$oP = $oW / $oH;

		$x = 0;
		$y = 0;

		if ($p > $oP)
		{

			$rW = $oW;
			$rH = $oW / $p;

			$y = ($oH - $rH) / 2;

		}
		else
		{

			$rH = $oH;
			$rW = $oH * $p;

			$x = ($oW - $rW) / 2;

		}

		$img = $img->crop($rW, $rH, $x, $y);

		return $img;
	}

	/**
	 * Set image name hash, not available now.
	 */
	public static function setHash($path, $width, $height, $zc, $q)
	{

	}

	/**
	 * Set a new default image placeholder.
	 *
	 * @param   string $url Default image placeholder.
	 */
	public static function setDefaultImage($url)
	{
		self::$default_image = $url;
	}

	/**
	 * Get default image and replace the placeholders.
	 *
	 * @param   integer $width     Image width, do not include 'px'.
	 * @param   integer $height    Image height, do not include 'px'.
	 * @param   boolean $zc        Crop or not.
	 * @param   integer $q         Image quality
	 * @param   string  $file_type File type.
	 *
	 * @return  string  Default image.
	 */
	public static function getDefaultImage($width = 100, $height = 100, $zc = 0, $q = 85, $file_type = 'jpg')
	{
		$replace['{width}']     = $width;
		$replace['{height}']    = $height;
		$replace['{zc}']        = $zc;
		$replace['{q}']         = $q;
		$replace['{file_type}'] = $file_type;
		$url                    = self::$default_image;
		$url                    = strtr($url, $replace);

		return $url;
	}

	/**
	 * Set cache path, and all image will cache in here.
	 *
	 * @param   string  Cache path.
	 */
	public static function setCachePath($path)
	{
		self::$cache_path = $path;
	}

	/**
	 * Set cache URL, and all image will cll from here.
	 *
	 * @param   string  Cache URL.
	 */
	public static function setCacheUrl($url)
	{
		self::$cache_url = $url;
	}

	/**
	 * Set temp path, and all remote image will store in here.
	 *
	 * @param   string  Temp path.
	 */
	public static function setTempPath($path)
	{
		self::$temp_path = $path;
	}

	/**
	 * Set cache position, will auto set cache path, url and temp path.
	 * If position set in: "cache/thumb"
	 * - Cache path:    ROOT/cache/thumb/cache
	 * - Temp path:     ROOT/cache/thumb/temp
	 * - Cache URL:     http://your-site.com/cache/thumb/cache/
	 */
	public static function setCachePosition($path)
	{
		self::setCachePath(JPATH_ROOT . '/' . trim($path, '/') . '/cache');
		self::setTempPath(JPATH_ROOT . '/' . trim($path, '/') . '/temp');
		self::setCacheUrl(trim($path, '/') . '/cache');
	}

	/**
	 * Reset cache position.
	 */
	public static function resetCachePosition()
	{
		self::setCachePath(AKTHUMB_CACHE_PATH);
		self::setTempPath(AKTHUMB_TEMP_PATH);
		self::setCacheUrl(AKTHUMB_CACHE_URL);
	}

	/**
	 * Delete all cache and temp images.
	 *
	 * @param   boolean $temp Is delete temp dir too?
	 */
	public static function clearCache($temp = false)
	{
		if (JFolder::exists(self::$cache_path))
		{
			JFolder::delete(self::$cache_path);
		}

		if ($temp && JFolder::exists(self::$temp_path))
		{
			JFolder::delete(self::$temp_path);
		}
	}
}
