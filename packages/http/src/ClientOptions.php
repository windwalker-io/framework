<?php

declare(strict_types=1);

namespace Windwalker\Http;

use Windwalker\Http\Transport\Options\TransportOptions;
use Windwalker\Utilities\Options\RecordOptions;

class ClientOptions extends RecordOptions
{
    public ?TransportOptions $transport = null;

    public function __construct(
        TransportOptions|array|null $transport = null,
        public ?string $baseUri = null,
        public ?array $vars = null,
        public ?array $headers = null,
        public ?array $params = null,
        public ?array $files = null,
    ) {
        $this->transport = TransportOptions::tryWrap($transport);
    }
}
