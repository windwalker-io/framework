<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base32Hex;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\EncoderInterface;
use ParagonIE\ConstantTime\Hex;

/**
 * This Encoder class uses paragonie/constant_time_encoding to provider timing-safe encoder/decoder.
 */
class SafeEncoder
{
    public const HEX = 'hex';

    public const BASE32 = 'base32';

    public const BASE32HEX = 'base32hex';

    public const BASE64 = 'base64';

    public const BASE64URLSAFE = 'base64url';

    /**
     * encode
     *
     * @param  string  $encoder
     * @param  string  $data
     *
     * @return  string
     */
    public static function encode(string $encoder, string $data): string
    {
        return static::encodeBy($encoder, $data);
    }

    /**
     * decode
     *
     * @param  string  $encoder
     * @param  string  $data
     *
     * @return  string
     */
    public static function decode(string $encoder, string $data): string
    {
        return static::decodeBy($encoder, $data);
    }

    /**
     * Workaround for https://youtrack.jetbrains.com/issue/WI-70511
     *
     * @param  string  $encoder
     * @param  string  $data
     *
     * @return  string
     */
    protected static function encodeBy(string $encoder, string $data): string
    {
        /** @var class-string<EncoderInterface> $encoder */
        $encoder = static::chooseEncoder($encoder);

        return $encoder::encode($data);
    }

    /**
     * Workaround for https://youtrack.jetbrains.com/issue/WI-70511
     *
     * @param  string  $encoder
     * @param  string  $data
     *
     * @return  string
     */
    protected static function decodeBy(string $encoder, string $data): string
    {
        /** @var class-string<EncoderInterface> $encoder */
        $encoder = static::chooseEncoder($encoder);

        return $encoder::decode($data);
    }

    /**
     * chooseEncoder
     *
     * @param  string  $encoder
     *
     * @return  class-string<EncoderInterface>
     */
    public static function chooseEncoder(string $encoder): string
    {
        return match ($encoder) {
            static::HEX => Hex::class,
            static::BASE32 => Base32::class,
            static::BASE32HEX => Base32Hex::class,
            static::BASE64 => Base64::class,
            default => Base64UrlSafe::class, // static::BASE64URLSAFE
        };
    }
}
