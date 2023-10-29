<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

/**
 * Interface QueryJsonTestInterface
 */
interface QueryJsonTestInterface
{
    #[DataProvider('parseJsonSelectorProvider')]
    public function testParseJsonSelector(string $selector, string $expected): void;

    public function testJsonQuote(): void;

    public function testAutoAlias(): void;

    public function testJsonContains(): void;

    public function testJsonNotContains(): void;

    public function testJsonLength(): void;
}
