<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test\Hasher;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Crypt\Hasher\PasswordHasher;

class PasswordHasherTest extends TestCase
{
    #[Test]
    public function hashesVerifiesAndDescribesPassword(): void
    {
        $hasher = new PasswordHasher(PASSWORD_BCRYPT, ['cost' => 4]);
        $hash = $hasher->hash('secret');

        self::assertTrue($hasher->verify('secret', $hash));
        self::assertFalse($hasher->verify('wrong', $hash));
        self::assertSame('bcrypt', $hasher->getInfo($hash)['algoName']);
        self::assertTrue($hasher->equals($hash, $hash));
        self::assertFalse($hasher->equals($hash, 'different'));
    }

    #[Test]
    public function reportsWhetherHashNeedsRehashing(): void
    {
        $hash = password_hash('secret', PASSWORD_BCRYPT, ['cost' => 4]);
        $currentHasher = new PasswordHasher(PASSWORD_BCRYPT, ['cost' => 4]);
        $updatedHasher = new PasswordHasher(PASSWORD_BCRYPT, ['cost' => 5]);

        self::assertFalse($currentHasher->needsRehash($hash));
        self::assertTrue($updatedHasher->needsRehash($hash));
        self::assertContains(PASSWORD_BCRYPT, PasswordHasher::algos());
    }

    #[Test]
    public function generatesPasswordsFromProvidedSeed(): void
    {
        $password = PasswordHasher::genRandomPassword(64, 'abc');

        self::assertSame(64, strlen($password));
        self::assertSame([], array_diff(str_split($password), str_split('abc')));
    }
}
