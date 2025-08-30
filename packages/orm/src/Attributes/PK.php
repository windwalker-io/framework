<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Cache\Exception\LogicException;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The PK class.
 */
#[Attribute]
class PK implements AttributeInterface
{
    use ORMAttributeTrait;

    protected bool $primary;

    protected Column $column;

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $metadata->addAttributeMap($this, $prop);

        $column = $metadata->getColumnByPropertyName($prop->getName());

        if ($column === null) {
            throw new LogicException(
                sprintf(
                    '%s attribute must before %s in class %s',
                    Column::class,
                    static::class,
                    $prop->getDeclaringClass()->getName()
                )
            );
        }

        $pk = $this;
        $this->column = $column;

        $setter = function () use ($column, $pk) {
            $this->keys[$column->getName()] = $pk;
        };

        $setter->call($metadata);

        return $handler->get();
    }

    /**
     * PK constructor.
     *
     * @param  bool  $primary
     */
    public function __construct(bool $primary = false)
    {
        $this->primary = $primary;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @return Column
     */
    public function getColumn(): Column
    {
        return $this->column;
    }

    /**
     * @param  Column  $column
     *
     * @return  static  Return self to support chaining.
     */
    public function setColumn(Column $column): static
    {
        $this->column = $column;

        return $this;
    }
}
