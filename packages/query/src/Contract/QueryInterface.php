<?php

declare(strict_types=1);

namespace Windwalker\Query\Contract;

/**
 * The public API of Query, aggregating all focused query interfaces so it can be proxied by other objects.
 */
interface QueryInterface extends
    BaseQueryInterface,
    BindingQueryInterface,
    WhereQueryInterface,
    JsonQueryInterface,
    IterateQueryInterface {}
