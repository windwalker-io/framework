<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use Windwalker\Utilities\Enum\EnumPhpAdapterTrait;

enum StubLangEnum: string
{
    use EnumPhpAdapterTrait;

    case Default = 'en';
    case USA = 'en-US';
    case UK = 'en-GB';
    case Taiwan = 'zh-TW';
    case Hongkong = 'zh-HK';
    case Japan = 'ja-JP';
    case Korean = 'kr-KR';
    case China = 'zh-CN';
    case Vietnam = 'vi-VN';
    case France = 'fr-FR';
    case Germany = 'de-DE';
    case Spain = 'es-ES';
    case Italy = 'it-IT';
    case Russia = 'ru-RU';
}
