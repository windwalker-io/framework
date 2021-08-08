<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Cache\Exception\LogicException;

/**
 * The UUID class.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class UUID extends CastForSave
{
    /**
     * CastForSave constructor.
     */
    public function __construct(public mixed $caster = null, public int $options = Cast::DEFAULT_IF_EMPTY)
    {
        $this->caster ??= $this->getUUIDCaster();
    }

    public function getUUIDCaster(): \Closure
    {
        return function () {
            if (!class_exists(\Ramsey\Uuid\Uuid::class)) {
                throw new LogicException('Please install ramsey/uuid ^4.0 first.');
            }

            return (string) \Ramsey\Uuid\Uuid::uuid1();
        };
    }
}
