<?php

declare(strict_types=1);

namespace Windwalker\ORM\Traits;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;
use Windwalker\Cache\Exception\LogicException;

trait UUIDTrait
{
    /**
     * @link http://tools.ietf.org/html/rfc4122#section-4.1.7 RFC 4122, § 4.1.7: Nil UUID
     */
    public const NIL = '00000000-0000-0000-0000-000000000000';

    /**
     * @link https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis-00#section-5.10 Max UUID
     */
    public const MAX = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    public const UUID3 = 'uuid3';

    public const UUID4 = 'uuid4';

    public const UUID5 = 'uuid5';

    public const UUID6 = 'uuid6';

    public const UUID7 = 'uuid7';

    public const UUID8 = 'uuid8';

    public function __construct(
        public string|int|\Closure $version = self::UUID7,
        public int $options = 0
    ) {
        //
    }

    public static function getDefault(string|int|\Closure $version): UuidInterface
    {
        if ($version instanceof \Closure) {
            return $version();
        }

        if (is_numeric($version)) {
            $method = 'uuid' . $version;

            return RamseyUuid::$method();
        }

        if ($version === RamseyUuid::NIL) {
            return RamseyUuid::fromString(RamseyUuid::NIL);
        }

        if ($version === RamseyUuid::MAX) {
            return RamseyUuid::fromString(RamseyUuid::MAX);
        }

        return RamseyUuid::$version();
    }

    protected static function checkLibrary(): void
    {
        if (!class_exists(RamseyUuid::class)) {
            throw new LogicException('Please install ramsey/uuid ^4.0 first.');
        }
    }
}
