<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The EntitySetup class.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class EntitySetup implements AttributeInterface
{
    use ORMAttributeTrait;

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->addAttributeMap($this, $handler->reflector);

        return static function () use ($handler, $metadata) {
            $handler->resolver
                ->call(
                    $method = $handler(),
                    [
                        'metadata' => $metadata,
                        static::class => $metadata,
                    ]
                );

            return $method;
        };
    }
}
