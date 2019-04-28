<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Middleware\Chain;

use Windwalker\Middleware\CallbackMiddleware;
use Windwalker\Middleware\EndMiddleware;
use Windwalker\Middleware\MiddlewareInterface;

/**
 * The Chain Builder
 *
 * @since 2.0
 */
class ChainBuilder
{
    const SORT_ASC = 'ASC';

    const SORT_DESC = 'DESC';

    /**
     * The middleware chain.
     *
     * @var  MiddlewareInterface[]|\SplStack
     */
    protected $stack;

    /**
     * Property endMiddleware.
     *
     * @var  MiddlewareInterface
     */
    protected $endMiddleware;

    /**
     * ChainBuilder constructor.
     *
     * @param MiddlewareInterface[] $middlewares
     * @param string                $sort
     *
     * @throws \ReflectionException
     */
    public function __construct(array $middlewares = [], $sort = self::SORT_DESC)
    {
        $this->stack = $this->createStack();

        $this->addMiddlewares($middlewares, $sort);
    }

    /**
     * Add a middleware into chain.
     *
     * @param mixed $middleware The middleware, can be a object, class name, callback, or middleware object.
     *                          These type will all convert to middleware object and store in chain.
     *
     * @return  static Return self to support chaining.
     * @throws \ReflectionException
     */
    public function add($middleware)
    {
        $object = $this->marshalMiddleware($middleware);

        if (count($this->stack)) {
            $object->setNext($this->stack->top());
        }

        $this->stack[] = $object;

        return $this;
    }

    /**
     * marshalMiddleware
     *
     * @param   mixed $middleware
     *
     * @return  MiddlewareInterface
     * @throws \ReflectionException
     */
    protected function marshalMiddleware($middleware)
    {
        if (is_string($middleware) && class_exists($middleware)) {
            $reflection = new \ReflectionClass($middleware);

            if (!$reflection->isInstantiable()) {
                throw new \LogicException(
                    sprintf('Element %s should be an instantiable class name.', $reflection->getName())
                );
            }

            $args = func_get_args();

            array_shift($args);

            $object = $reflection->newInstanceArgs($args);
        } elseif ($middleware instanceof MiddlewareInterface) {
            $object = $middleware;
        } elseif (is_callable($middleware)) {
            $object = new CallbackMiddleware($middleware);
        } else {
            throw new \InvalidArgumentException('Not valid MiddleChaining element.');
        }

        return $object;
    }

    /**
     * Call chaining.
     *
     * @param  mixed $data
     *
     * @return mixed
     */
    public function execute($data = null)
    {
        if ($this->getEndMiddleware()) {
            $end = $this->getEndMiddleware();

            if (count($this->stack)) {
                $this->stack->bottom()->setNext($end);
            }

            $this->stack->unshift($end);
        }

        if (!count($this->stack)) {
            return null;
        }

        // Start call chaining.
        $result = $this->stack->top()->execute($data);

        // Remove end middleware so we can re-use this chain.
        if ($this->getEndMiddleware()) {
            $this->stack->shift();
        }

        return $result;
    }

    /**
     * createStack
     *
     * @return  \SplStack
     */
    protected function createStack()
    {
        $stack = new \SplStack();
        $stack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP);

        return $stack;
    }

    /**
     * Method to get property Stack
     *
     * @return  \SplStack|MiddlewareInterface[]
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * Method to set property stack
     *
     * @param   \SplStack $stack
     *
     * @return  static  Return self to support chaining.
     */
    public function setStack(\SplStack $stack)
    {
        $this->stack = $stack;

        return $this;
    }

    /**
     * reset
     *
     * @return  void
     */
    protected function reset()
    {
        $this->stack = $this->createStack();
    }

    /**
     * addMiddlewares
     *
     * @param array  $middlewares
     * @param string $sort
     *
     * @return  static
     * @throws \ReflectionException
     */
    public function addMiddlewares(array $middlewares, $sort = self::SORT_DESC)
    {
        if ($sort == static::SORT_DESC) {
            $middlewares = array_reverse($middlewares);
        }

        foreach ($middlewares as $middleware) {
            $this->add($middleware);
        }

        return $this;
    }

    /**
     * getEndMiddleware
     *
     * @return  MiddlewareInterface
     */
    protected function getEndMiddleware()
    {
        if (!$this->endMiddleware) {
            $this->endMiddleware = new EndMiddleware();
        }

        return $this->endMiddleware;
    }

    /**
     * Method to set property endMiddleware
     *
     * @param   MiddlewareInterface|callable $middleware
     *
     * @return  static  Return self to support chaining.
     * @throws \ReflectionException
     */
    public function setEndMiddleware($middleware)
    {
        $object = $this->marshalMiddleware($middleware);

        $this->endMiddleware = $object;

        return $this;
    }

    /**
     * dumpStack
     *
     * @return  array
     */
    public function dumpStack()
    {
        return iterator_to_array(clone $this->stack);
    }
}
