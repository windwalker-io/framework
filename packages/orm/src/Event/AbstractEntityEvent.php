<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Event\AbstractEvent;
use Windwalker\ORM\Attributes\ORMAttributeTrait;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

/**
 * The AbstractEntityEvent class.
 */
class AbstractEntityEvent extends AbstractEvent implements AttributeInterface
{
    use ORMAttributeTrait;
    use ORMEventTrait;

    protected array $data;

    /**
     * @return array
     */
    public function &getData(): array
    {
        return $this->data;
    }

    /**
     * @param  array  $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap(static::class, $handler->getReflector());

        return $handler->get();
    }
}
