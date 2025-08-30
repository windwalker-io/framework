<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use ReflectionProperty;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Event\AbstractSaveEvent;
use Windwalker\ORM\Event\AbstractUpdateWhereEvent;
use Windwalker\ORM\Event\WatchEvent;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The Watch class.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Watch implements AttributeInterface
{
    use ORMAttributeTrait;

    public const int BEFORE_SAVE = 1 << 0;

    /**
     * @deprecated Use INCLUDE_CREATE instead.
     */
    public const int ON_CREATE = 1 << 1;

    public const int INCLUDE_CREATE = 1 << 1;

    public const int INCLUDE_UPDATE_WHERE = 1 << 2;

    /**
     * @var callable|string
     */
    protected $columnOrHandler;

    /**
     * Watch constructor.
     */
    public function __construct(
        string|callable $columnOrHandler,
        public int $options = 0,
    ) {
        $this->columnOrHandler = $columnOrHandler;
    }

    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap($this, $handler->getReflector());

        return function () use ($handler, $metadata) {
            $ref = $handler->getReflector();
            $target = $handler();

            if ($ref instanceof ReflectionProperty) {
                $method = $this->columnOrHandler;
                $column = $metadata->getColumnByPropertyName($ref->getName())->getName();
            } else {
                $method = $target;
                $column = $this->columnOrHandler;
            }

            $metadata->watch($column, $method, $this->options);

            return $method;
        };
    }

    public static function createWatchEvent(
        AbstractSaveEvent|AbstractUpdateWhereEvent $event,
        mixed $value,
        mixed $oldValue = null,
    ): WatchEvent {
        $type = $event instanceof AbstractUpdateWhereEvent
            ? AbstractSaveEvent::TYPE_UPDATE
            : $event->type;

        $watchEvent = new WatchEvent(
            type: $type,
            originEvent: $event,
            source: $event->data,
            value: $value,
        );

        $watchEvent->setDataRef($event->data);
        $watchEvent->metadata = $event->metadata;

        if ($event instanceof AbstractSaveEvent) {
            $watchEvent->oldData = $event->oldData;
            $watchEvent->oldValue = $oldValue;
            $watchEvent->isUpdateWhere = false;
        } else {
            $watchEvent->oldData = $event->data;
            $watchEvent->oldValue = null;
            $watchEvent->isUpdateWhere = true;
        }

        return $watchEvent;
    }
}
