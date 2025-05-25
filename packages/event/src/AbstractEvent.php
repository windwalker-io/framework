<?php

declare(strict_types=1);

namespace Windwalker\Event;

use ReflectionClass;
use Windwalker\Utilities\Assert\ArgumentsAssert;

/**
 * The AbstractEvent class.
 *
 * @deprecated  Use BaseEvent instead.
 */
#[\AllowDynamicProperties]
abstract class AbstractEvent extends BaseEvent
{
    /**
     * Constructor.
     *
     * @param  string|null  $name       The event name.
     * @param  array        $arguments  The event arguments.
     *
     * @since   2.0
     */
    public function __construct(?string $name = null, array $arguments = [])
    {
        $this->name = $name ?? static::class;

        $this->merge($arguments);
    }
}
