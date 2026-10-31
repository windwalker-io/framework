<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Env;

class EnvTest extends TestCase
{
    private const string NAME = 'WINDWALKER_TEST_ENV_VAR';

    #[Before]
    #[After]
    public function clearVariable(): void
    {
        unset($_SERVER[self::NAME], $_ENV[self::NAME]);
    }

    #[Test]
    public function getPrefersServerOverEnv(): void
    {
        $_SERVER[self::NAME] = 'server';
        $_ENV[self::NAME] = 'env';

        self::assertSame('server', Env::get(self::NAME));
    }

    #[Test]
    public function getFallsBackToEnvThenDefault(): void
    {
        self::assertSame('default', Env::get(self::NAME, 'default'));
        self::assertNull(Env::get(self::NAME));

        $_ENV[self::NAME] = 'env';

        self::assertSame('env', Env::get(self::NAME, 'default'));
    }
}
