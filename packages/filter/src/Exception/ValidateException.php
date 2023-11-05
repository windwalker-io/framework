<?php

declare(strict_types=1);

namespace Windwalker\Filter\Exception;

use Throwable;
use UnexpectedValueException;
use Windwalker\Filter\ValidatorInterface;

/**
 * The ValidateException class.
 */
class ValidateException extends UnexpectedValueException
{
    protected ValidatorInterface $validator;

    public static function create(
        ValidatorInterface $validator,
        string $message = '',
        int $code = 0,
        ?Throwable $e = null
    ): static {
        $exception = new static($message, $code, $e);

        $exception->setValidator($validator);

        return $exception;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param  ValidatorInterface  $validator
     *
     * @return  static  Return self to support chaining.
     */
    public function setValidator(ValidatorInterface $validator): static
    {
        $this->validator = $validator;

        return $this;
    }
}
