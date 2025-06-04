<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

class StubLangCode
{
    public function __construct(public string $tag = 'USA')
    {
    }

    public function __invoke(): string
    {
        return match ($this->tag) {
            'USA' => 'en-US',
            'UK' => 'en-GB',
            'Taiwan' => 'zh-TW',
            'Hongkong' => 'zh-HK',
            'Japan' => 'ja-JP',
            'Korean' => 'kr-KR',
            'China' => 'zh-CN',
            'Vietnam' => 'vi-VN',
            'France' => 'fr-FR',
            'Germany' => 'de-DE',
            'Spain' => 'es-ES',
            'Italy' => 'it-IT',
            'Russia' => 'ru-RU',
            default => 'en',
        };
    }
}
