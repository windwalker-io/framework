<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Options;

use Windwalker\Utilities\Options\RecordOptionsTrait;

/**
 * Record options fixture with a constructor argument, an array property and a nested record,
 * used by RecordOptionsTraitTest and RecordOptionsTest.
 *
 * Can be removed together with those tests once RecordOptionsTrait is removed.
 */
class StubRecordOptions
{
    use RecordOptionsTrait;

    public ?StubRecordOptions $child = null;

    public array $list = [];

    public ?string $fooBar = null;

    public mixed $plain = null;

    public function __construct(public string $name = 'default') {}
}
