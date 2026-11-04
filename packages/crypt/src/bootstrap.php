<?php

declare(strict_types=1);

namespace {
    include_once __DIR__ . '/functions.php';
}

namespace Windwalker\Crypt {
    if (!defined('Windwalker\Crypt\SECRET_128BIT')) {
        define('Windwalker\Crypt\SECRET_128BIT', 16);
    }

    if (!defined('Windwalker\Crypt\SECRET_192BIT')) {
        define('Windwalker\Crypt\SECRET_192BIT', 24);
    }

    if (!defined('Windwalker\Crypt\SECRET_256BIT')) {
        define('Windwalker\Crypt\SECRET_256BIT', 32);
    }

    if (!defined('Windwalker\Crypt\SECRET_LENGTH_DEFAULT')) {
        define('Windwalker\Crypt\SECRET_LENGTH_DEFAULT', -1);
    }

    if (!defined('Windwalker\Crypt\ENCODER_RAW')) {
        define('Windwalker\Crypt\ENCODER_RAW', 'raw');
    }

    if (!defined('Windwalker\Crypt\ENCODER_HEX')) {
        define('Windwalker\Crypt\ENCODER_HEX', 'hex');
    }

    if (!defined('Windwalker\Crypt\ENCODER_BASE32')) {
        define('Windwalker\Crypt\ENCODER_BASE32', 'base32');
    }

    if (!defined('Windwalker\Crypt\ENCODER_BASE32HEX')) {
        define('Windwalker\Crypt\ENCODER_BASE32HEX', 'base32hex');
    }

    if (!defined('Windwalker\Crypt\ENCODER_BASE64')) {
        define('Windwalker\Crypt\ENCODER_BASE64', 'base64');
    }

    if (!defined('Windwalker\Crypt\ENCODER_BASE64URLSAFE')) {
        define('Windwalker\Crypt\ENCODER_BASE64URLSAFE', 'base64url');
    }

    if (!defined('Windwalker\Crypt\ENCODER_BASE64DOTSLASH')) {
        define('Windwalker\Crypt\ENCODER_BASE64DOTSLASH', 'base64dotslash');
    }

    if (!defined('Windwalker\Crypt\ENCODERS')) {
        define(
            'Windwalker\Crypt\ENCODERS',
            [
                ENCODER_RAW,
                ENCODER_HEX,
                ENCODER_BASE32,
                ENCODER_BASE32HEX,
                ENCODER_BASE64,
                ENCODER_BASE64URLSAFE,
                ENCODER_BASE64DOTSLASH,
            ]
        );
    }
}
