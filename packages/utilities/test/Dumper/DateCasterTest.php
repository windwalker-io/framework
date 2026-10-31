<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Dumper;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Caster\ConstStub;
use Symfony\Component\VarDumper\Cloner\Stub;
use Windwalker\Utilities\Dumper\DateCaster;

class DateCasterTest extends TestCase
{
    /**
     * @see  DateCaster::castDateTime
     */
    #[Test]
    public function castDateTimeReplacesDatePropertyWithVirtualInfo(): void
    {
        $date = new DateTimeImmutable('2020-01-02 03:04:05.5', new DateTimeZone('Asia/Taipei'));
        $stub = new Stub();
        $stub->class = 'DateTimeImmutable';

        $result = DateCaster::castDateTime($date, [Caster::PREFIX_DYNAMIC . 'date' => 'x'], $stub, false, 0);

        self::assertArrayNotHasKey(Caster::PREFIX_DYNAMIC . 'date', $result);
        self::assertSame('DateTimeImmutable @1577905445', $stub->class);

        $dateStub = $result[Caster::PREFIX_VIRTUAL . 'date'];

        self::assertInstanceOf(ConstStub::class, $dateStub);
        self::assertSame('2020-01-02 03:04:05.500 Asia/Taipei (+08:00)', $dateStub->class);
        self::assertSame('2020-01-02T03:04:05.500+08:00', $dateStub->value);
        self::assertSame('Off', $result[Caster::PREFIX_VIRTUAL . 'DST']->value);
        self::assertStringEndsWith(' from now', $result[Caster::PREFIX_VIRTUAL . 'diff']->value);
    }

    /**
     * @see  DateCaster::castDateTime
     */
    #[Test]
    public function castDateTimeReportsDstAndOmitsZoneNameForOffsetTimezones(): void
    {
        $stub = new Stub();
        $stub->class = 'DateTime';

        $summer = DateCaster::castDateTime(
            new DateTimeImmutable('2020-07-01 12:00:00', new DateTimeZone('Europe/London')),
            [],
            $stub,
            false,
            0
        );
        $offset = DateCaster::castDateTime(
            new DateTimeImmutable('2020-07-01 12:00:00+02:00'),
            [],
            $stub,
            false,
            0
        );

        self::assertSame('On', $summer[Caster::PREFIX_VIRTUAL . 'DST']->value);
        self::assertSame('2020-07-01 12:00:00.0 +02:00', $offset[Caster::PREFIX_VIRTUAL . 'date']->class);
        self::assertSame('', $offset[Caster::PREFIX_VIRTUAL . 'DST']->value);
    }

    /**
     * @see  DateCaster::formatInterval
     */
    #[Test]
    #[DataProvider('intervalProvider')]
    public function formatIntervalRendersSignedHumanReadableInterval(string $spec, bool $invert, string $expected): void
    {
        $interval = new DateInterval($spec);
        $interval->invert = (int) $invert;

        $method = new ReflectionMethod(DateCaster::class, 'formatInterval');

        self::assertSame($expected, $method->invoke(null, $interval));
    }

    public static function intervalProvider(): array
    {
        return [
            'zero' => ['PT0S', false, '0s'],
            'years months days' => ['P1Y2M3D', false, '+1y 2m 3d'],
            'time part' => ['PT1H2M3S', true, '-01:02:03.0'],
            'carry over minutes' => ['PT90M', false, '+01:30:00.0'],
            'carry over hours into days' => ['PT30H', false, '+1d 06:00:00.0'],
        ];
    }
}
