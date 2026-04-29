<?php

declare(strict_types=1);

namespace Windwalker\Session\Cookie;

use Windwalker\Utilities\Options\RecordOptionsTrait;

class CookiesOptions
{
    use RecordOptionsTrait;

    // phpcs:disable
    public ?\DateTimeImmutable $expires = null {
        set(\DateTimeImmutable|string|int|null $value) {
            if ($value === null) {
                $this->expires = null;

                return;
            }

            if (is_int($value)) {
                $value = \DateTimeImmutable::createFromTimestamp($value);
            }

            if (is_string($value)) {
                $value = new \DateTimeImmutable($value);
            }

            $this->expires = \DateTimeImmutable::createFromInterface($value);
        }
    }
    // phpcs:enable

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
        $params = [
            'expires' => $this->expires ? (int) $this->expires->format('U') : null,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        return array_filter($params, fn ($v) => $v !== null);
    }

    public function toSessionCookieParams(): array
    {
        $params = [
            'expires' => $this->expires ? ((int) $this->expires->format('U')) - time() : null,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        return array_filter($params, fn ($v) => $v !== null);
    }
}
