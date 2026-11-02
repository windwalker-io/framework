<?php

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use DateTimeInterface;

/**
 * Trait QueryHelperTrait
 */
trait QueryConcernTrait
{
    public function formatDateTime(DateTimeInterface $dateTime): string
    {
        return $dateTime->format($this->getDateFormat());
    }

    public function stripQuote(string $str): string
    {
        return $this->getEscaper()::stripQuoteIfExists($str);
    }

    public function stripNameQuote(string $str): string
    {
        $grammar = $this->getGrammar();
        $nq = $grammar::$nameQuote;

        return $this->getEscaper()::stripQuoteIfExists($str, $nq[0], $nq[1]);
    }
}
