<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

final class PhpBuiltinTestServer
{
    private ?Process $process = null;

    private bool $shutdownRegistered = false;

    private bool $signalHandlersRegistered = false;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $documentRoot,
        private readonly ?string $routerScript = null,
        private readonly string $healthPath = '/__health',
        private readonly float $startupTimeoutSeconds = 5.0,
        private readonly float $shutdownTimeoutSeconds = 2.0,
    ) {
    }

    public static function create(
        string $documentRoot,
        ?string $routerScript = null,
        string $host = '127.0.0.1',
        string $healthPath = '/__health',
    ): self {
        return new self(
            host: $host,
            port: self::findAvailablePort($host),
            documentRoot: $documentRoot,
            routerScript: $routerScript,
            healthPath: $healthPath,
        );
    }

    public function start(): void
    {
        if ($this->process?->isRunning()) {
            return;
        }

        $cmd = [PHP_BINARY, '-S', "{$this->host}:{$this->port}", '-t', $this->documentRoot];

        if ($this->routerScript !== null) {
            $cmd[] = $this->routerScript;
        }

        $this->process = new Process($cmd);
        $this->process->start();

        $this->registerCleanupHandlers();
        $this->waitUntilReady();
    }

    public function stop(): void
    {
        if ($this->process === null) {
            return;
        }

        $this->process->stop((int) $this->shutdownTimeoutSeconds);
        $this->process = null;
    }

    public function isRunning(): bool
    {
        return $this->process?->isRunning() ?? false;
    }

    public function baseUrl(): string
    {
        return sprintf('http://%s:%d', $this->host, $this->port);
    }

    public function port(): int
    {
        return $this->port;
    }

    public function host(): string
    {
        return $this->host;
    }

    private function registerCleanupHandlers(): void
    {
        if (!$this->shutdownRegistered) {
            $this->shutdownRegistered = true;

            register_shutdown_function(function (): void {
                $this->stop();
            });
        }

        if ($this->signalHandlersRegistered || !extension_loaded('pcntl')) {
            return;
        }

        $this->signalHandlersRegistered = true;

        pcntl_async_signals(true);

        $handler = function (int $signal): void {
            $this->stop();

            if (PHP_OS_FAMILY !== 'Windows' && function_exists('posix_kill')) {
                pcntl_signal($signal, SIG_DFL);
                posix_kill(getmypid(), $signal);
            }

            exit(128 + $signal);
        };

        foreach ([SIGINT, SIGTERM, SIGHUP, SIGQUIT] as $signal) {
            pcntl_signal($signal, $handler);
        }
    }

    private function waitUntilReady(): void
    {
        $url = $this->baseUrl() . $this->healthPath;
        $context = stream_context_create(
            [
                'http' => [
                    'timeout' => 0.3,
                    'ignore_errors' => true,
                ],
            ]
        );

        $deadline = microtime(true) + $this->startupTimeoutSeconds;

        do {
            if (!$this->process->isRunning()) {
                throw new RuntimeException(
                    'PHP built-in server process exited unexpectedly: '
                    . $this->process->getErrorOutput()
                );
            }

            $result = @file_get_contents($url, false, $context);

            if ($result !== false) {
                return;
            }

            usleep(100_000);
        } while (microtime(true) < $deadline);

        $this->stop();

        throw new RuntimeException(
            sprintf(
                'PHP built-in server did not become ready within %.1fs.',
                $this->startupTimeoutSeconds,
            )
        );
    }

    private static function findAvailablePort(string $host): int
    {
        $socket = stream_socket_server("tcp://{$host}:0", $errno, $errstr);

        if ($socket === false) {
            throw new RuntimeException("Could not find available port: {$errstr}");
        }

        $name = stream_socket_get_name($socket, false);

        fclose($socket);

        if ($name === false) {
            throw new RuntimeException('Could not determine allocated port.');
        }

        $parts = explode(':', $name);

        return (int) end($parts);
    }
}
