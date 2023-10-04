<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Column class.
 */
#[Attribute]
class Column implements AttributeInterface
{
    use ORMAttributeTrait;

    protected string $name;

    protected ReflectionProperty $property;

    /**
     * Column constructor.
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ReflectionProperty
     */
    public function getProperty(): ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $metadata->addAttributeMap(static::class, $prop);

        $this->property = $prop;
        $column = $this;

        $setter = function () use ($column, $prop) {
            $this->columns[$column->getName()] = $column;
            $this->propertyColumns[$prop->getName()] = $column;
        };

        $setter->call($metadata);

        return $handler->get();
    }
}
