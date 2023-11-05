<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;

/**
 * This attribute is use for cast value before data preparef to store into DB.
 * And will not execute when Entity hydrating and extracting in object level.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class CastForSave
{
    /**
     * CastForSave constructor.
     */
    public function __construct(protected mixed $caster = null, public int $options = 0)
    {
    }

    /**
     * @return mixed
     */
    public function getCaster(): mixed
    {
        return $this->caster ?? $this->getDefaultCaster();
    }

    /**
     * @param  mixed  $caster
     *
     * @return  static  Return self to support chaining.
     */
    public function setCaster(mixed $caster): static
    {
        $this->caster = $caster;

        return $this;
    }

    protected function getDefaultCaster(): callable
    {
        return static fn($value) => $value;
    }
}
