<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Crypt\Password;

class PasswordTest extends TestCase
{
    #[Test]
    public function generatesPasswordsWithConfiguredLengthAndSeed(): void
    {
        $password = Password::genRandomPassword(40, 'abc123');

        self::assertSame(40, strlen($password));
        self::assertSame([], array_diff(str_split($password), str_split('abc123')));
    }

    #[Test]
    public function generatesPasswordUsingDefaultSeed(): void
    {
        $password = Password::genRandomPassword();

        self::assertSame(20, strlen($password));
        self::assertSame([], array_diff(str_split($password), str_split(Password::SEED_ALNUM)));
    }
}
