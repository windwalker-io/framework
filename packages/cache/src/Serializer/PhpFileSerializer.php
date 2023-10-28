<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Serializer;

/**
 * The PhpFileSerializer class.
 *
 * @since  3.0
 */
class PhpFileSerializer implements SerializerInterface
{
    /**
     * Encode data.
     *
     * @param  mixed  $data
     *
     * @return string|null
     */
    public function serialize(mixed $data): ?string
    {
        return "<?php \n\nreturn " . var_export($data, true) . ';';
    }

    /**
     * Decode data.
     *
     * @param  string  $data
     *
     * @return mixed
     */
    public function unserialize(string $data): mixed
    {
        return $data;
    }
}
