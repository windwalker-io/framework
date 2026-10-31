<?php

declare(strict_types=1);

namespace Windwalker\Authentication\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Authentication\AuthResult;
use Windwalker\Authentication\ResultSet;

class ResultSetTest extends TestCase
{
    #[Test]
    public function emptySetIsFailureAndHasNoFirstResult(): void
    {
        $results = new ResultSet();

        self::assertTrue($results->isFailure());
        self::assertFalse($results->isSuccess());
        self::assertNull($results->getFirstSuccess());
        self::assertNull($results->getFirstFailure());
        self::assertNull($results->first());
        self::assertSame([], $results->getResults());
        self::assertCount(0, $results);
    }

    #[Test]
    public function resultsCanBeAddedAndQueriedByOutcome(): void
    {
        $firstFailure = new AuthResult(AuthResult::INVALID_CREDENTIAL, []);
        $success = new AuthResult(AuthResult::SUCCESS, []);
        $laterFailure = new AuthResult(AuthResult::USER_NOT_FOUND, []);
        $results = new ResultSet(['first' => $firstFailure]);

        self::assertSame($results, $results->addResult('success', $success));
        $results->addResult('later', $laterFailure);

        self::assertTrue($results->isSuccess());
        self::assertFalse($results->isFailure());
        self::assertSame($success, $results->getFirstSuccess());
        self::assertSame($firstFailure, $results->getFirstFailure());
        self::assertSame($firstFailure, $results->first());
        self::assertCount(3, $results);
    }

    #[Test]
    public function matchedMethodResolvesMatchedResult(): void
    {
        $success = new AuthResult(AuthResult::SUCCESS, []);
        $results = new ResultSet(['local' => $success]);
        $results->matchedMethod = 'local';

        self::assertSame('local', $results->getMatchedMethod());
        self::assertSame($success, $results->getMatchedResult());
    }

    #[Test]
    public function iteratorPreservesResultKeysAndOrder(): void
    {
        $first = new AuthResult(AuthResult::INVALID_CREDENTIAL, []);
        $second = new AuthResult(AuthResult::SUCCESS, []);
        $results = new ResultSet([
            'first' => $first,
            'second' => $second
        ]);

        self::assertSame([
            'first' => $first,
            'second' => $second
        ], iterator_to_array($results));
    }
}
