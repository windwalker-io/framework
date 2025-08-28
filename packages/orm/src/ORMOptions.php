<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Utilities\Accessible\RecordOptionsTrait;

class ORMOptions
{
    use RecordOptionsTrait {
        wrap as parentWrap;
    }

    public const string SKIP_LOCKED = 'SKIP LOCKED';
    public const string NOWAIT = 'NOWAIT';

    public $forUpdateDo {
        get => is_bool($this->forUpdate) ? null : strtoupper($this->forUpdate);
    }

    public $forShareDo {
        get => is_bool($this->forShare) ? null : strtoupper($this->forShare);
    }

    public function __construct(
        public bool $updateNulls = false,
        public bool $ignoreEvents = false,
        public bool $ignoreOldData = false,
        public bool $transaction = false,
        public bool|string $forUpdate = false,
        public bool|string $forShare = false,
    ) {
    }

    public static function wrap(mixed $options): static
    {
        if (is_int($options)) {
            return new static(
                updateNulls: (bool) ($options & EntityMapper::UPDATE_NULLS),
                ignoreEvents: (bool) ($options & EntityMapper::IGNORE_EVENTS),
                ignoreOldData: (bool) ($options & EntityMapper::IGNORE_OLD_DATA),
                transaction: (bool) ($options & EntityMapper::TRANSACTION),
                forUpdate: (bool) ($options & EntityMapper::FOR_UPDATE),
            );
        }

        return static::parentWrap($options);
    }
}
