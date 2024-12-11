<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\ORM\Metadata\EntityMetadata;

trait CastAttributeTrait
{
    use ORMAttributeTrait;

    /**
     * @inheritDoc
     */
    public function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        $metadata->castByAttribute(
            $handler,
            $this
        );

        return $handler->get();
    }
}
