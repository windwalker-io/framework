<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

class StubLangCode
{
    public function __construct(public string|\UnitEnum $tag = 'USA')
    {
    }

    public function __invoke(): string
    {
        $cases = StubLangEnum::rawValues();

        $tag = $this->tag;

        if ($tag instanceof \UnitEnum) {
            $tag = $tag->name;
        }

        return $cases[$tag] ?? StubLangEnum::Default->value;
    }
}
