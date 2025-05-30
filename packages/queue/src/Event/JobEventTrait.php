<?php

declare(strict_types=1);

namespace Windwalker\Queue\Event;

use Windwalker\Queue\QueueMessage;
use Windwalker\Utilities\Assert\TypeAssert;

/**
 * The JobEventTrait class.
 */
trait JobEventTrait
{
    use QueueEventTrait;

    public QueueMessage $message;

    /**
     * @var callable
     */
    public mixed $job {
        set {
            TypeAssert::assert(is_callable($value), 'Property {caller} must be a callable, {value} given.', $value);

            $this->job = $value;
        }
    }
}
