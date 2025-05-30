<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Event\BaseEvent;
use Windwalker\ORM\Attributes\ORMAttributeTrait;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * The AbstractEntityEvent class.
 */
class AbstractEntityEvent extends BaseEvent implements AttributeInterface
{
    use ORMAttributeTrait;
    use ORMEventTrait;
    use AccessorBCTrait;

    /**
     * @param  array  $data
     */
    public function __construct(public array $data = [])
    {
        //
    }

    /**
     * @deprecated  Use property instead.
     */
    public function &getData(): array
    {
        return $this->data;
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
