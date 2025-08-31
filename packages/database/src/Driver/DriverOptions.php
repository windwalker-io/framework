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
        public ?int $port = null,
        public ?string $prefix = null,
        public ?string $charset = null,
        public ?string $collation = null,
        public ?string $platform = null,
        public ?string $dsn = null,
        public bool $debug = false,
        public array $driverOptions = [],
        public array $pool = [],
        public bool $strict = true,
        public array $modes = [],
        public array $afterConnect = [],
        public array $extra = [],
    ) {
    }
}
