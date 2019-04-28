<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Cache\Serializer;

/**
 * Class JsonHandler
 *
 * @since 2.0
 */
class JsonSerializer implements SerializerInterface
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
        return json_encode($data);
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
        return json_decode($data);
    }
}
