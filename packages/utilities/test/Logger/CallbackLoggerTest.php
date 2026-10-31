<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Logger;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Windwalker\Utilities\Logger\CallbackLogger;

class CallbackLoggerTest extends TestCase
{
    /**
     * @see  CallbackLogger::log
     */
    #[Test]
    public function logForwardsLevelMessageAndContextToCallback(): void
    {
        $records = [];
        $logger = new CallbackLogger(
            static function ($level, $message, array $context) use (&$records) {
                $records[] = [$level, (string) $message, $context];
            }
        );

        $logger->log(LogLevel::INFO, 'hello', ['a' => 1]);
        $logger->error('boom');

        self::assertSame(
            [
                [LogLevel::INFO, 'hello', ['a' => 1]],
                [LogLevel::ERROR, 'boom', []],
            ],
            $records
        );
    }
}
