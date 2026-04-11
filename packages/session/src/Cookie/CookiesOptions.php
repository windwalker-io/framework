<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

use Windwalker\Core\DateTime\Chronos;
use Windwalker\Utilities\Options\RecordOptionsTrait;
use Windwalker\Utilities\StrNormalize;

class CookiesOptions
{
    use RecordOptionsTrait;

    public ?Chronos $expires = null {
        set(\DateTimeInterface|string|int|null $value) => $this->expires = Chronos::tryWrap($value);
    }

    public function __construct(
        \DateTimeInterface|string|int|null $expires = null,
        public ?string $path = null,
        public ?string $domain = null,
        public ?bool $secure = null,
        public ?bool $httpOnly = null,
        public ?string $sameSite = null,
    ) {
        $this->expires = $expires;
    }

    protected function normalizeKey(int|string $key): string|int
    {
        if ($key === 'httponly') {
            $key = 'httpOnly';
        }

        if ($key === 'samesite') {
            $key = 'sameSite';
        }

        return $key;
    }

    public function toCookieParams(): array
    {
        return [
            'expires' => $this->expires->toUnix(),
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];
    }
}
