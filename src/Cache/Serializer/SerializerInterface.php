<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Cache\Serializer;

/**
 * Interface DataHandlerInterface
 */
interface SerializerInterface
{
    /**
     * Encode data.
     *
     * @param   mixed $data
     *
     * @return  string
     */
    public function serialize($data);

    /**
     * Decode data.
     *
     * @param   string $data
     *
     * @return  mixed
     */
    public function unserialize($data);
}
