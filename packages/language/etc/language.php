<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Language\LanguagePackage;

return [
    'language' => [
        'enabled' => true,

        // Language debug will mark untranslated string by `??` and stored orphan in Languages object.
        'debug' => (bool) (env('LANG_DEBUG') ?? false),

        // The current locale
        'locale' => 'zh-TW',

        // The default locale, if translated string in current locale not found, will fallback to default locale.
        'fallback' => 'en-US',

        'paths' => [
            '@languages'
        ],

        'providers' => [
            LanguagePackage::class
        ],
    ]
];
