<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * The AbstractORMAttribute class.
 */
trait ORMAttributeTrait
{
    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        /** @var ?EntityMetadata $metadata */
        $metadata = $handler->getOptions()['metadata'] ?? null;

        // Not in setup process, return.
        if (!$metadata instanceof EntityMetadata) {
            return $handler->get();
        }

        return $this->handle($metadata, $handler);
    }

    /**
     * Run ORM attribute handler.
     *
     * @param  EntityMetadata    $metadata
     * @param  AttributeHandler  $handler
     *
     * @return  callable
     */
    abstract protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable;
}
