<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Windwalker\Utilities\Options\RecordOptions;

class DriverOptions extends RecordOptions
{
    public function __construct(
        public ?string $driver = null,
        public ?string $host = 'localhost',
        public ?string $file = null,
        public mixed $unixSocket = null,
        public ?string $dbname = null,
        public ?string $user = null,
        public ?string $password = null,
        public int|string|null $port = null {
            get => $this->port !== null ? (int) $this->port : null;
        },
        public ?string $prefix = null,
        public ?string $charset = null,
        public ?string $collation = null,
        public ?string $platform = null,
        public ?string $dsn = null,
        public bool $debug = false,
        public array $driverOptions = [],
        public array $pool = [],
        public bool $strict = true,
        public ?array $modes = null,
        /**
         * @var array<\Closure(AbstractDriver): void|string>
         */
        public \Closure|string|array $afterConnect = [],
        public array $extra = [],
        /**
         * @var array{
         *     key?: string,
         *     cert?: string,
         *     ca?: string,
         *     capath?: string,
         *     cipherAlgos?: string,
         *     verify?: bool,
         * }
         */
        public ?array $tls = null,
    ) {
    }

    public static function tlsOptions(
        ?string $key = null,
        ?string $cert = null,
        ?string $ca = null,
        ?string $capath = null,
        ?string $cipherAlgos = null,
        ?bool $verify = null
    ): array {
        $options = compact('key', 'cert', 'ca', 'capath', 'cipherAlgos', 'verify');

        return array_filter($options, fn($value) => $value !== null);
    }
}
