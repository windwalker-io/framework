<?php

declare(strict_types=1);

namespace Windwalker\Form\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\ResultSet;
use Windwalker\Form\Test\Stub\StubField;
use Windwalker\Form\ValidateResult;

class ResultSetTest extends TestCase
{
    private function makeResult(int $status): ValidateResult
    {
        return new ValidateResult($status, new StubField('foo'));
    }

    #[Test]
    public function emptySetIsSuccessWithoutFirstResult(): void
    {
        $set = new ResultSet();

        self::assertTrue($set->isSuccess());
        self::assertFalse($set->isFailure());
        self::assertNull($set->getFirstFailure());
        self::assertCount(0, $set);
    }

    #[Test]
    public function addResultStoresResultsByName(): void
    {
        $ok = $this->makeResult(ValidateResult::STATUS_SUCCESS);
        $set = new ResultSet()->addResult('a', $ok);

        self::assertSame($ok, $set->getResult('a'));
        self::assertNull($set->getResult('missing'));
        self::assertCount(1, $set);
    }

    #[Test]
    public function constructorAcceptsResultsKeyedByName(): void
    {
        $ok = $this->makeResult(ValidateResult::STATUS_SUCCESS);
        $bad = $this->makeResult(ValidateResult::STATUS_FAILURE);
        $set = new ResultSet(['a' => $ok, 'b' => $bad]);

        self::assertSame($ok, $set->first());
        self::assertSame(['a' => $ok, 'b' => $bad], iterator_to_array($set));
    }

    #[Test]
    public function firstFailureMakesSetFailing(): void
    {
        $required = $this->makeResult(ValidateResult::STATUS_REQUIRED);
        $failure = $this->makeResult(ValidateResult::STATUS_FAILURE);
        $set = new ResultSet(
            [
                'ok' => $this->makeResult(ValidateResult::STATUS_SUCCESS),
                'required' => $required,
                'failure' => $failure,
            ]
        );

        self::assertTrue($set->isFailure());
        self::assertFalse($set->isSuccess());
        self::assertSame($required, $set->getFirstFailure());
    }
}
