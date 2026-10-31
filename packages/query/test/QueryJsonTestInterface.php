<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

/**
 * Interface QueryJsonTestInterface
 */
interface QueryJsonTestInterface
{
    #[DataProvider('parseJsonSelectorProvider')]
    public function parseJsonSelectorConvertsToDbJsonPath(string $selector, string $expected): void;

    public function jsonQuoteWrapsJsonExpression(): void;

    public function autoAliasPrefixesColumnsWithTableAlias(): void;

    public function jsonContainsBuildsContainsCondition(): void;

    public function jsonNotContainsBuildsNegatedCondition(): void;

    public function jsonLengthBuildsLengthExpression(): void;
}
