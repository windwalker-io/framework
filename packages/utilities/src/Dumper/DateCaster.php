<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Dumper;

use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Caster\ConstStub;
use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * The DateCaster class.
 */
class DateCaster
{
    public static function castDateTime(\DateTimeInterface $d, array $a, Stub $stub, bool $isNested, int $filter)
    {
        $prefix = Caster::PREFIX_VIRTUAL;
        $location = $d->getTimezone()->getLocation();
        $fromNow = (new \DateTime())->diff($d);

        $title = $d->format(\DateTimeInterface::RFC3339_EXTENDED);
        $dst = $location ? ($d->format('I') ? "On" : "Off") : '';

        unset(
            $a[Caster::PREFIX_DYNAMIC . 'date'],
        );
        $a[$prefix . 'date'] = new ConstStub(self::formatDateTime($d, $location ? ' e (P)' : ' P'), $title);
        $a[$prefix . 'DST'] = new ConstStub('DST', $dst);
        $a[$prefix . 'diff'] = new ConstStub('from_now', static::formatInterval($fromNow) . ' from now');

        $stub->class .= $d->format(' @U');

        return $a;
    }

    private static function formatInterval(\DateInterval $i): string
    {
        $format = '%R';

        if (0 === $i->y && 0 === $i->m && ($i->h >= 24 || $i->i >= 60 || $i->s >= 60)) {
            $i = date_diff($d = new \DateTime(), date_add(clone $d, $i)); // recalculate carry over points
            $format .= 0 < $i->days ? '%ad ' : '';
        } else {
            $format .= ($i->y ? '%yy ' : '') . ($i->m ? '%mm ' : '') . ($i->d ? '%dd ' : '');
        }

        $format .= $i->h || $i->i || $i->s || $i->f
            ? '%H:%I:' . self::formatSeconds(
                (string) $i->s,
                substr((string) $i->f, 2)
            )
            : '';
        $format = '%R ' === $format ? '0s' : $format;

        return $i->format(rtrim($format));
    }

    private static function formatDateTime(\DateTimeInterface $d, string $extra = ''): string
    {
        return $d->format('Y-m-d H:i:' . self::formatSeconds($d->format('s'), $d->format('u')) . $extra);
    }

    private static function formatSeconds(string $s, string $us): string
    {
        return sprintf(
            '%02d.%s',
            $s,
            0 === ($len = \strlen($t = rtrim($us, '0'))) ? '0' : ($len <= 3 ? str_pad($t, 3, '0') : $us)
        );
    }
}
