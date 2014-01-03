<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class UriHelper
 *
 * @since 1.0
 */
class UriHelper
{
	/**
	 * A base encode & decode function, will auto convert white space to plus to avoid errors.
	 *
	 * @param   string $action 'encode' OR 'decode'
	 * @param   string $url    A url or a base64 string to convert.
	 *
	 * @return  string URL or base64 decode string.
	 */
	public static function base64($action, $url)
	{
		switch ($action)
		{
			case 'encode':
				$url = base64_encode($url);
				break;

			case 'decode':
				$url = str_replace(' ', '+', $url);
				$url = base64_decode($url);
				break;
		}

		return $url;
	}

	/**
	 * A download function to hide real file path. When call this function, will start download instantly.
	 *
	 * This function should call when view has not executed yet, if header sended,
	 *  the file which downloaded will error, because download by stream will
	 *  contain header in this file.
	 *
	 * @param   string  $path     The file system path with filename & type.
	 * @param   boolean $absolute Absolute URL or not.
	 * @param   boolean $stream   Use stream or redirect to download.
	 * @param   array   $option   Some download options.
	 *
	 * @return  void
	 */
	public static function download($path, $absolute = false, $stream = false, $option = array())
	{
		if ($stream)
		{
			if (!$absolute)
			{
				$path = JPATH_ROOT . '/' . $path;
			}

			if (!is_file($path))
			{
				die();
			}

			$file = pathinfo($path);

			$filesize = filesize($path) + \JArrayHelper::getValue($option, 'size_offset', 0);
			ini_set('memory_limit', \JArrayHelper::getValue($option, 'memory_limit', '1540M'));

			// Set Header
			header('Content-Type: application/octet-stream');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header('Content-Transfer-Encoding: binary');
			header('Content-Encoding: none');
			header('Content-type: application/force-download');
			header('Content-length: ' . $filesize);
			header('Content-Disposition: attachment; filename="' . $file['basename'] . '"');

			$handle    = fopen($path, 'rb');
			$chunksize = 1 * (1024 * 1024);

			// Start Download File by Stream
			while (!feof($handle))
			{
				$buffer = fread($handle, $chunksize);
				echo $buffer;
				ob_flush();
				flush();
			}

			fclose($handle);

			jexit();
		}
		else
		{
			if (!$absolute)
			{
				$path = \JURI::root() . $path;
			}

			// Redirect it.
			$app = \JFactory::getApplication();
			$app->redirect($path);
		}
	}

	/**
	 * Make a URL safe.
	 * - Replace white space to '%20'.
	 *
	 * @param   string $uri The URL you want to make safe.
	 *
	 * @return  string  Replaced URL.
	 */
	public static function safe($uri)
	{
		$uri = (string) $uri;
		$uri = str_replace(' ', '%20', $uri);

		return $uri;
	}
}
