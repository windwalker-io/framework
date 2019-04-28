<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI\Annotation;

use Doctrine\Common\Annotations\Annotation\Enum;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The Inject class.
 *
 * @Annotation
 *
 * @Target({"PROPERTY"})
 *
 * @since  3.4.4
 */
class Inject
{
    /**
     * Property key.
     *
     * @var string
     */
    protected $key;

    /**
     * Property new.
     *
     * @Enum({true, false})
     *
     * @var  bool
     */
    protected $new = false;

    /**
     * Inject constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->key = isset($values['key']) ? $values['key'] : null;
        $this->new = isset($values['new']) ? $values['new'] : false;
    }

    /**
     * getInjectable
     *
     * @param Container $container
     * @param string    $class
     *
     * @return  mixed
     *
     * @throws \ReflectionException
     * @throws \Windwalker\DI\Exception\DependencyResolutionException
     *
     * @since  3.4.4
     */
    public function resolveInjectable(Container $container, $class)
    {
        $id = $this->key === null ? $class : $this->key;

        if ($container->has($id)) {
            return $container->get($id, $this->new);
        }

        if (!class_exists($id)) {
            throw new DependencyResolutionException(
                sprintf('Class: "%s" not exists.', $id)
            );
        }

        return $container->newInstance($id);
    }
}
