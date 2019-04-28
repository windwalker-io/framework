<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The PhpTypeValidator class.
 *
 * @since  3.2
 */
class PhpTypeValidator extends AbstractValidator
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type;

    /**
     * PhpTypeValidator constructor.
     *
     * @param string $type
     */
    public function __construct($type = '')
    {
        $this->setType($type);
    }

    /**
     * Test value and return boolean
     *
     * @param mixed $value
     *
     * @return  boolean
     */
    protected function test($value)
    {
        $type = $this->type;

        if (!$type) {
            return true;
        }

        switch ($type) {
            case 'numeric':
                return is_numeric($value);

            case 'scalar':
                return is_scalar($value);

            case 'callable':
                return is_callable($value);

            case 'iterable':
                return is_iterable($value);

            case 'bool':
                return is_bool($value);

            case 'float':
                return is_float($value);

            case 'real':
                return is_real($value);

            case 'int':
                return is_integer($value);

            case 'long':
                return is_long($value);

            case 'nan':
                return is_long($value);
        }

        if (class_exists($type)) {
            return $value instanceof $type;
        }

        return strtolower(gettype($value)) === $type;
    }

    /**
     * Method to get property Type
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Method to set property type
     *
     * @param   string $type
     *
     * @return  static  Return self to support chaining.
     */
    public function setType($type)
    {
        $this->type = strtolower($type);

        return $this;
    }

    /**
     * formatMessage
     *
     * @param string $message
     * @param mixed  $value
     *
     * @return string
     */
    protected function formatMessage($message, $value)
    {
        $type = class_exists($this->type) ? get_class($value) : gettype($value);

        return sprintf($message, $this->type, $type);
    }
}
