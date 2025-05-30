<?php

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Query\Query;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

/**
 * Trait QueryEventTrait
 */
trait QueryEventTrait
{
    use AccessorBCTrait;

    public string $sql = '';

    public array $bounded = [];

    public ?StatementInterface $statement = null;

    public mixed $query = null;

    public string $debugQueryString {
        get {
            $query = $this->query;

            if ($query instanceof Query) {
                $query = $query->render(true);
            }

            return (string) $query;
        }
    }
}
