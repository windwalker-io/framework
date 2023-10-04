<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Windwalker\Utilities\TypeCast;

/**
 * The AbstractRequestHandler class.
 */
abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    protected array $queue;

    /**
     * @var callable|null
     */
    protected $resolver;

    public function __construct(iterable $queue, ?callable $resolver = null)
    {
        if (!is_iterable($queue)) {
            throw new \TypeError('Queue must be array or Traversable.');
        }

        $queue = TypeCast::toArray($queue);

        if (empty($queue)) {
            throw new \InvalidArgumentException('Queue is empty');
        }

        $this->queue = $queue;

        if ($resolver === null) {
            $resolver = static fn($entry) => $entry;
        }

        $this->resolver = $resolver;
    }
}
