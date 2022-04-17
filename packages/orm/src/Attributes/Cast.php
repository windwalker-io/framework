<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

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
class Cast implements AttributeInterface
{
    use ORMAttributeTrait;

    public const USE_CONSTRUCTOR = 1 << 0;

    public const USE_HYDRATOR = 1 << 1;

    public const NULLABLE = 1 << 2;

    public const DEFAULT_IF_EMPTY = 1 << 3;

    public const DEFAULT_IF_NULL = 1 << 4;

    public const EMPTY_STRING_TO_NULL = 1 << 5;

    protected mixed $hydrate;

    protected int $options;

    /**
     * @var mixed
     */
    protected $extract;

    /**
     * Cast constructor.
     *
     * @param  string      $hydrate
     * @param  mixed|null  $extract
     * @param  int         $options
     */
    public function __construct(mixed $hydrate, mixed $extract = null, int $options = 0)
    {
        $this->hydrate = $hydrate;
        $this->options = $options;
        $this->extract = $extract;
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

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $column = $metadata->getColumnByPropertyName($prop->getName());

        $colName = $column ? $column->getName() : $prop->getName();

        $metadata->cast(
            $colName,
            $this->getHydrate(),
            $this->getExtract(),
            $this->getOptions()
        );

        return $handler->get();
    }
}
