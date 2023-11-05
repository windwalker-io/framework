<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    registerArgumentsSet(
        'crypt_encoder',
        SafeEncoder::HEX,
        \Windwalker\Crypt\SafeEncoder::BASE32,
        \Windwalker\Crypt\SafeEncoder::BASE32HEX,
        \Windwalker\Crypt\SafeEncoder::BASE64,
        \Windwalker\Crypt\SafeEncoder::BASE64URLSAFE,
    );

    expectedArguments(
        \Windwalker\Crypt\Symmetric\CipherInterface::encrypt(),
        2,
        argumentsSet('crypt_encoder')
    );

    expectedArguments(
        \Windwalker\Crypt\Symmetric\CipherInterface::decrypt(),
        2,
        argumentsSet('crypt_encoder')
    );

    registerArgumentsSet(
        'openssl_methods',
        'aes-128-cbc',
        'aes-128-cfb',
        'aes-128-cfb1',
        'aes-128-cfb8',
        'aes-128-ofb',
        'aes-192-cbc',
        'aes-192-cfb',
        'aes-192-cfb1',
        'aes-192-cfb8',
        'aes-192-ofb',
        'aes-256-cbc',
        'aes-256-cfb',
        'aes-256-cfb1',
        'aes-256-cfb8',
        'aes-256-ofb',
        'bf-cbc',
        'bf-cfb',
        'bf-ofb',
        'cast5-cbc',
        'cast5-cfb',
        'cast5-ofb',
        'idea-cbc',
        'idea-cfb',
        'idea-ofb'
    );

    expectedArguments(
        \Windwalker\Crypt\Symmetric\OpensslCipher::__construct(),
        0,
        ...argumentsSet('openssl_methods')
    );
}
