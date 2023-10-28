<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

/**
 * Interface DumpableInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface DumpableInterface
{
    /**
     * Dump to array.
     *
     * @param  bool  $recursive     Dump children array.
     * @param  bool  $onlyDumpable  Objects only implements DumpableInterface will convert to array.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array;
}
