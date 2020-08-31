<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Concern;

use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\StrInflector;

/**
 * The StrInflectorTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait StringInflectorTrait
{
    public function isPlural(): bool
    {
        return StrInflector::isPlural($this->string);
    }

    public function isSingular(): bool
    {
        return StrInflector::isSingular($this->string);
    }

    public function toPlural(): StringObject
    {
        return $this->cloneInstance(
            function ($new) {
                $new->string = StrInflector::toPlural($new->string);
            }
        );
    }

    public function toSingular(): StringObject
    {
        return $this->cloneInstance(
            function ($new) {
                $new->string = StrInflector::toSingular($new->string);
            }
        );
    }
}
