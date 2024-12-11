<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Cast class.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_PROPERTY)]
class Cast implements CastAttributeInterface
{
    use CastAttributeTrait;

    /**
     * Cast constructor.
     *
     * @param  string      $hydrate
     * @param  mixed|null  $extract
     * @param  int         $options
     */
    public function __construct(protected mixed $hydrate, protected mixed $extract = null, protected int $options = 0)
    {
        //
    }

    /**
     * @return mixed
     */
    public function getHydrate(): mixed
    {
        return $this->hydrate;
    }

    /**
     * @return mixed
     */
    public function getExtract(): mixed
    {
        return $this->extract;
    }

    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }
}
