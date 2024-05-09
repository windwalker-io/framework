<?php

declare(strict_types=1);

namespace Windwalker\Crypt;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\EncoderInterface;
use ParagonIE\ConstantTime\Hex;

/**
 * The SecretToolkit class.
 */
class SecretToolkit
{
    /**
     * @throws \Exception
     */
    public static function genSecretString(
        int $length = SECRET_128BIT,
        string $encoder = ENCODER_BASE64URLSAFE,
        bool $withPrefix = true
    ): string {
        $secret = random_bytes($length);

        return static::encode($secret, $encoder, $withPrefix);
    }

    /**
     * @throws \Exception
     */
    public static function genSecret(
        int $length = SECRET_128BIT,
        string $encoder = ENCODER_BASE64URLSAFE,
        bool $withPrefix = true
    ): string {
        return static::genSecretString($length, $encoder, $withPrefix);
    }

    public static function encode(
        string $binaryString,
        string $encoder = ENCODER_BASE64URLSAFE,
        bool $withPrefix = true
    ): string {
        if ($encoder === ENCODER_RAW) {
            return $binaryString;
        }

        /** @var class-string<EncoderInterface> $encoder */
        $encoderClass = SafeEncoder::chooseEncoder($encoder);

        if (is_a($encoderClass, Base64::class, true)) {
            $encoded = $encoderClass::encodeUnpadded($binaryString);
        } elseif (is_a($encoderClass, Base32::class, true)) {
            $encoded = $encoderClass::encodeUpperUnpadded($binaryString);
        } elseif (is_a($encoderClass, Hex::class, true)) {
            $encoded = $encoderClass::encode($binaryString);
        } else {
            $encoded = $encoderClass::encode($binaryString);
        }

        if ($withPrefix) {
            $encoded = $encoder . ':' . $encoded;
        }

        return $encoded;
    }

    /**
     * @param  string  $string
     *
     * @return  array{ 0: string, 1: string }
     */
    public static function extract(string $string): array
    {
        $extracted = explode(':', $string, 2);

        if (count($extracted) === 1) {
            throw new \InvalidArgumentException('Invalid secret string.');
        }

        return $extracted;
    }

    public static function stripPrefix(string $string): string
    {
        if (!static::canDecode($string)) {
            return $string;
        }

        [, $extracted] = static::extract($string);

        return $extracted;
    }

    public static function getEncoder(string $string): ?string
    {
        if (!str_contains($string, ':')) {
            return null;
        }

        [$encoder] = static::extract($string);

        if (!in_array($encoder, ENCODERS, true)) {
            return null;
        }

        return $encoder;
    }

    public static function canDecode(string $string): bool
    {
        $encoder = static::getEncoder($string);

        return $encoder !== null && in_array($encoder, ENCODERS, true);
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

    public static function decodeIfHasPrefix(string $string): string
    {
        if (static::canDecode($string)) {
            return static::decode($string);
        }

        return $string;
    }
}
