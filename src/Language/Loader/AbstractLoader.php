<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Loader;

/**
 * Class AbstractLoader
 *
 * @since 2.0
 */
abstract class AbstractLoader implements LoaderInterface
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = '';

    /**
     * getName
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }
}
