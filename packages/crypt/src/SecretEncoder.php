<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Crypt;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\EncoderInterface;
use ParagonIE\ConstantTime\Hex;

/**
 * The SecretEncoder class.
 */
class SecretEncoder
{
    /**
     * @throws \Exception
     */
    public static function genSecret(
        int $length = SECRET_128BIT,
        string $encoder = ENCODER_BASE64URLSAFE
    ): string {
        $secret = random_bytes($length);

        return $encoder . ':' . static::encode($secret, $encoder);
    }

    /**
     * @throws \Exception
     */
    public static function genSecretWithPrefix(
        int $length = SECRET_128BIT,
        string $encoder = ENCODER_BASE64URLSAFE
    ): string {
        return $encoder . ':' . static::genSecret($length, $encoder);
    }

    public static function encode(string $binaryString, string $encoder = ENCODER_BASE64URLSAFE): string
    {
        if ($encoder === ENCODER_RAW) {
            return $binaryString;
        }

        /** @var class-string<EncoderInterface> $encoder */
        $encoder = SafeEncoder::chooseEncoder($encoder);

        if (is_a($encoder, Base64::class, true)) {
            return $encoder::encodeUnpadded($binaryString);
        }

        if (is_a($encoder, Base32::class, true)) {
            return $encoder::encodeUpperUnpadded($binaryString);
        }

        if (is_a($encoder, Hex::class, true)) {
            return $encoder::encodeUpper($binaryString);
        }

        return $encoder::encode($binaryString);
    }

    /**
     * @param  string  $string
     *
     * @return  array{ 0: string, 1: string }
     */
    public static function extract(string $string): array
    {
        return explode(':', $string, 2);
    }

    public static function getEncoder(string $string): ?string
    {
        if (!str_contains($string, ':')) {
            return null;
        }

        [$encoder] = static::extract($string);

        return $encoder;
    }

    public static function canDecode(string $string): bool
    {
        $encoder = static::getEncoder($string);

        return $encoder !== null;
    }

    public static function decode(string $string): string
    {
        [$decoder, $string] = static::extract($string);

        return static::decodeBy($string, $decoder);
    }

    public static function decodeBy(string $string, string $decoder = ENCODER_BASE64URLSAFE): string
    {
        if ($decoder === ENCODER_RAW) {
            return $string;
        }

        /** @var class-string<EncoderInterface> $decoder */
        $decoder = SafeEncoder::chooseEncoder($decoder);

        if (is_subclass_of($decoder, Base64::class)) {
            return $decoder::decodeNoPadding($string);
        }

        if (is_subclass_of($decoder, Hex::class)) {
            return $decoder::decode($string);
        }

        return $decoder::decode($string);
    }
}
