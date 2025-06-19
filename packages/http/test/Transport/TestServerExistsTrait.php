<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\HttpClient;
use Windwalker\Uri\Uri;

trait TestServerExistsTrait
{
    public static function checkTestServerRunningOrSkip(): void
    {
        if (!defined('WINDWALKER_TEST_HTTP_URL')) {
            static::markTestSkipped('No WINDWALKER_TEST_HTTP_URL provided');
        }

        if (
            function_exists('swoole_version')
            && version_compare(swoole_version(), '6.0.0', '<')
        ) {
            throw new \RuntimeException(
                'Swoole version must be 6.0.0 or higher to run Windwalker HTTP with Swoole.' .
                ' Please update your Swoole extension.'
            );
        }

        $url = new Uri(WINDWALKER_TEST_HTTP_URL);

        try {
            $http = new HttpClient();
            $res = $http->get($url);
        } catch (HttpRequestException $e) {
            if (str_contains($e->getMessage(), 'Connection refused')) {
                throw new HttpRequestException(
                    $e->getMessage() . ' - Try run: ' . sprintf(
                        'php -S %s:%s bin/test-server.php',
                        $url->getHost(),
                        $url->getPort()
                    )
                );
            }

            throw $e;
        }
    }
}
