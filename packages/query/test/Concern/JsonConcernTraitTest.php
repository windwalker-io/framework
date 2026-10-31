<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Concern;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

class JsonConcernTraitTest extends TestCase
{
    #[Test]
    public function jsonSelectorThrowsWhenGrammarHasNoJsonSupport(): void
    {
        $escaper = new MockEscaper();
        $query = new Query($escaper, new BaseGrammar());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains('not supports JSON grammar');

        $query->jsonSelector('data->foo');
    }

    #[Test]
    public function whereJsonContainsThrowsWhenGrammarHasNoJsonSupport(): void
    {
        $escaper = new MockEscaper();
        $query = new Query($escaper, new BaseGrammar());

        $this->expectException(LogicException::class);

        $query->whereJsonContains('data->foo', 'bar');
    }
}
