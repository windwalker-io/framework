<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Concern;

use DateTimeInterface;
use Windwalker\Query\Query;
use Windwalker\Utilities\TypeCast;

/**
 * Trait QueryHelperTrait
 */
trait QueryConcernTrait
{
    /**
     * convertArrayToWheres
     *
     * @param  Query  $query
     * @param  mixed  $wheres
     *
     * @return  Query
     */
    public static function convertAllToWheres(Query $query, mixed $wheres, $method = 'where'): Query
    {
        if ($wheres === null) {
            return $query;
        }

        if (is_callable($wheres)) {
            return $query->$method($wheres);
        }

        $wheres = TypeCast::toArray($wheres);

        foreach ($wheres as $key => $where) {
            // String key:
            // 'key' => 'value'
            if (!is_numeric($key)) {
                $query->$method($key, '=', $where);
                continue;
            }

            // String element:
            // 'key <= value'
            if (is_string($where)) {
                $query->{$method . 'Raw'}($where);
                continue;
            }

            // Array element
            // ['key', '=', 'value']
            if (is_array($where)) {
                $query->$method(...$where);
                continue;
            }

            // Callback or others
            $query->$method($where);
        }

        return $query;
    }

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
