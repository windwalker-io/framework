<?php

declare(strict_types=1);

namespace Windwalker\Test\Traits\Reactor;

use Swoole\Event;
use Windwalker\Utilities\Env;

/**
 * The SwooleTestTrait class.
 */
trait SwooleTestTrait
{
    public function nextTick(): void
    {
        if ($this->swooleEnabled()) {
            Event::wait();
        }
    }

    /**
     * swooleEnabled
     *
     * @return  bool
     */
    public function swooleEnabled(): bool
    {
        return extension_loaded('swoole');
    }

    public static function skipIfSwooleNotInstalled(): void
    {
        if (!Env::get('SWOOLE_ENABLED')) {
            self::markTestSkipped('Swoole havn\'t installed');
        }

        if (
            function_exists('swoole_version')
            && version_compare(swoole_version(), '6.0.0', '<')
        ) {
            self::markTestSkipped('Swoole version must be 6.0.0 or higher');
        }
    }
}
