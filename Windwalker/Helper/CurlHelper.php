<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

use Windwalker\Object\NullObject;
use Windwalker\Object\Object;

/**
 * Class CurlHelper
 *
 * @since 1.0
 */
class CurlHelper
{
	/**
	 * Request a page and return it as string.
	 *
	 * @param   string $url    A url to request.
	 * @param   mixed  $method Request method, GET or POST. If is array, equal to $option.
	 * @param   string $query  Query string. eg: 'option=com_content&id=11&Itemid=125'. <br /> Only use for POST.
	 * @param   array  $option An option array to override CURL OPT.
	 *
	 * @throws \Exception
	 * @return  mixed  If success, return string, or return false.
	 */
	public static function get($url = '', $method = 'get', $query = '', $option = array())
	{
		if (!$url)
		{
			return false;
		}

		if ((!function_exists('curl_init') || !is_callable('curl_init')) && ini_get('allow_url_fopen'))
		{
			return file_get_contents($url);
		}

		if (is_array($method))
		{
			$option = $method;
		}

		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.163 Safari/535.1",
			CURLOPT_FOLLOWLOCATION => !ini_get('open_basedir') ? true : false,
			CURLOPT_SSL_VERIFYPEER => false
		);

		// Merge option
		$options = array_merge($options, $option);

		$http = \JHttpFactory::getHttp(new \JRegistry($options), 'curl');

		try
		{
			switch ($method)
			{
				case 'post':
				case 'put':
				case 'patch':
					$result = $http->$method(UriHelper::safe($url), $query);
					break;

				default:
					$result = $http->$method(UriHelper::safe($url));
					break;
			}
		}
		catch (\Exception $e)
		{
			return new NullObject;
		}

		return $result;
	}

	/**
	 * Get a page and save it as file.
	 *
	 * @param   string $url    A url to request.
	 * @param   string $path   A system path with file name to save it.
	 * @param   array  $option An option array to override CURL OPT.
	 *
	 * @return  Object Object with success or fail information.
	 */
	public static function download($url = null, $path = null, $option = array())
	{
		if (!$url)
		{
			return false;
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.path');

		$url  = new \JUri($url);
		$path = \JPath::clean($path);

		// $folder_path = JPATH_ROOT.DS.'files'.DS.$url->task_id ;
		if (substr($path, -1) == DIRECTORY_SEPARATOR)
		{
			$file_name   = basename($url);
			$file_path   = $path . $file_name;
			$folder_path = $path;
		}
		else
		{
			$file_path   = $path;
			$folder_path = str_replace(basename($path), '', $file_path);
		}

		\JPath::setPermissions($folder_path, 644, 755);

		if (!\is_dir($folder_path))
		{
			\JFolder::create($folder_path);
		}

		$fp = fopen($file_path, 'w+');
		$ch = curl_init();

		$options = array(
			CURLOPT_URL            => UriHelper::safe($url),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.163 Safari/535.1",
			CURLOPT_FOLLOWLOCATION => !ini_get('open_basedir') ? true : false,
			CURLOPT_FILE           => $fp,
			CURLOPT_SSL_VERIFYPEER => false
		);

		// Merge option
		foreach ($option as $key => $opt)
		{
			if (isset($option[$key]))
			{
				$options[$key] = $option[$key];
			}
		}

		curl_setopt_array($ch, $options);
		curl_exec($ch);

		$errno  = curl_errno($ch);
		$errmsg = curl_error($ch);

		curl_close($ch);
		fclose($fp);

		if ($errno)
		{
			$return = new Object;

			$return->set('errorCode', $errno);
			$return->set('errorMsg',  $errmsg);

			return $return;
		}
		else
		{
			$return = new Object;

			$return->set('filePath', $file_path);

			return $return;
		}
	}
}
