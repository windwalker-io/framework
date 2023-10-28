<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes\Test\Stub\Attrs;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

/**
 * The StubMemberResolver class.
 */
#[Attribute]
class StubMemberResolver implements AttributeInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $obj = $handler->getObject();
            $ref = $handler->getReflector();

            $obj->output[] = sprintf('%s = %s', $ref::class, $ref->getName());

            return $handler();
        };
    }
}
