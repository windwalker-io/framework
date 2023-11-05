<?php

declare(strict_types=1);

namespace Windwalker\Crypt;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base32Hex;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64DotSlash;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\EncoderInterface;
use ParagonIE\ConstantTime\Hex;

/**
 * This Encoder class uses paragonie/constant_time_encoding to provider timing-safe encoder/decoder.
 */
class SafeEncoder
{
    /** @deprecated  */
    public const HEX = ENCODER_HEX;

    /** @deprecated  */
    public const BASE32 = ENCODER_BASE32;

    /** @deprecated  */
    public const BASE32HEX = ENCODER_BASE32HEX;

    /** @deprecated  */
    public const BASE64 = ENCODER_BASE64;

    /** @deprecated  */
    public const BASE64URLSAFE = ENCODER_BASE64URLSAFE;

    /** @deprecated  */
    public const BASE64DOTSLASH = ENCODER_BASE64DOTSLASH;

    /**
     * encode
     *
     * @param  string|callable  $encoder
     * @param  string           $data
     *
     * @return  string
     */
    public static function encode(string|callable $encoder, string $data): string
    {
        if (is_callable($encoder)) {
            return $encoder($data);
        }

        return static::encodeBy($encoder, $data);
    }

    /**
     * decode
     *
     * @param  string|callable  $decoder
     * @param  string           $data
     *
     * @return  string
     */
    public static function decode(string|callable $decoder, string $data): string
    {
        if (is_callable($decoder)) {
            return $decoder($data);
        }

        return static::decodeBy($decoder, $data);
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
        if ($encoder === ENCODER_RAW) {
            return $data;
        }

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
            ENCODER_HEX => Hex::class,
            ENCODER_BASE32 => Base32::class,
            ENCODER_BASE32HEX => Base32Hex::class,
            ENCODER_BASE64 => Base64::class,
            ENCODER_BASE64DOTSLASH => Base64DotSlash::class,
            default => Base64UrlSafe::class, // BASE64URLSAFE
        };
    }
}
