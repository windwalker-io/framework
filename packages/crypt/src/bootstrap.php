<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace {
    include_once __DIR__ . '/functions.php';
}

namespace Windwalker\Crypt {
    const SECRET_128BIT = 16;
    const SECRET_192BIT = 24;
    const SECRET_256BIT = 32;

    const ENCODER_RAW = 'raw';
    const ENCODER_HEX = 'hex';
    const ENCODER_BASE32 = 'base32';
    const ENCODER_BASE32HEX = 'base32hex';
    const ENCODER_BASE64 = 'base64';
    const ENCODER_BASE64URLSAFE = 'base64url';
    const ENCODER_BASE64DOTSLASH = 'base64dotslash';

    const ENCODERS = [
        ENCODER_RAW,
        ENCODER_HEX,
        ENCODER_BASE32,
        ENCODER_BASE32HEX,
        ENCODER_BASE64,
        ENCODER_BASE64URLSAFE,
        ENCODER_BASE64DOTSLASH,
    ];
}
