<?php

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

/**
 * Interface FieldHydratorInterface
 */
interface FieldHydratorInterface extends HydratorInterface
{
    /**
     * Extract single field.
     *
     * @param  object  $object
     * @param  string  $field
     *
     * @return  mixed
     */
    public function extractField(object $object, string $field): mixed;
}
