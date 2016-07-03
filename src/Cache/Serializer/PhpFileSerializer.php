<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Cache\Serializer;

/**
 * The PhpFileSerializer class.
 *
 * @since  {DEPLOY_VERSION}
 */
class PhpFileSerializer implements SerializerInterface
{
	/**
	 * Encode data.
	 *
	 * @param   mixed $data
	 *
	 * @return  string
	 */
	public function serialize($data)
	{
		return "<?php \n\nreturn " . var_export($data, true) . ';';
	}

	/**
	 * Decode data.
	 *
	 * @param   string $data
	 *
	 * @return  mixed
	 */
	public function unserialize($data)
	{
		return $data;
	}
}
