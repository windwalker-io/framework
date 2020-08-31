<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM;

/**
 * The EmptyElement class.
 */
class EmptyElement extends DOMElement
{


    /**
     * @inheritDoc
     */
    public function render(?string $type = self::HTML, bool $format = false): string
    {
        $nodes = $this->childNodes;

        foreach ($nodes as $node) {

        }
    }
}
