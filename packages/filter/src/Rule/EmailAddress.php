<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilterVar;

/**
 * The Email class.
 */
class EmailAddress extends AbstractFilterVar
{
    /**
     * REF5321 is more suitable for SMTP transport.
     *
     * @see https://stackoverflow.com/a/30234506
     * @see https://www.easy365manager.com/rfc-5321-and-rfc-5322/
     */
    public const RFC5321 = '/^(?!(?>"?(?>\\\[ -~]|[^"])"?){255,})(?!"?(?>\\\[ -~]|[^"]){65,}"?@)(?>([!#-\'*+\/-9=?^-~-]+)(?>\.(?1))*|"(?>[ !#-\[\]-~]|\\\[ -~])*")@(?!.*[^.]{64,})(?>([a-z\d](?>[a-z\d-]*[a-z\d])?)(?>\.(?2)){0,126}|\[(?:(?>IPv6:(?>([a-f\d]{1,4})(?>:(?3)){7}|(?!(?:.*[a-f\d][:\]]){8,})((?3)(?>:(?3)){0,6})?::(?4)?))|(?>(?>IPv6:(?>(?3)(?>:(?3)){5}:|(?!(?:.*[a-f\d]:){6,})(?5)?::(?>((?3)(?>:(?3)){0,4}):)?))?(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)(?>\.(?6)){3}))\])$/iD';

    public function getFilterName(): int
    {
        return FILTER_SANITIZE_EMAIL;
    }

    public function test(mixed $value, bool $strict = false): bool
    {
        // We use RFC532 to
        return (bool) preg_match(static::RFC5321, (string) $value);
    }

    public function getOptions(): ?int
    {
        return null;
    }
}
