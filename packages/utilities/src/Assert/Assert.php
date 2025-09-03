<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use Closure;
use ReflectionFunction;
use Throwable;
use Windwalker\Utilities\SimpleTemplate;
use Windwalker\Utilities\Str;

/**
 * @psalm-type  MessageClosure = Closure(string $caller, mixed $value, string $type): string
 *
 * The Assert class.
 */
class Assert
{
    public string $caller;

    /**
     * @var callable
     */
    protected $exceptionHandler;

    /**
     * Assert constructor.
     *
     * @param  callable  $exceptionHandler
     * @param  ?string   $caller
     */
    public function __construct(callable $exceptionHandler, ?string $caller = null)
    {
        $this->caller = $caller ?? static::getCaller(2);
        $this->exceptionHandler = $exceptionHandler;
    }

    public static function create(
        string $exceptionClass = \RuntimeException::class,
        int $code = 500,
        ?string $caller = null,
    ): static {
        return new static(
            fn($message) => new $exceptionClass($message, $code),
            $caller
        );
    }

    /**
     * @template T of mixed
     *
     * @param  T  $assertion
     * @param  string|MessageClosure  $message
     * @param  mixed  $value
     *
     * @return  T
     */
    public function assert(mixed $assertion, string|Closure $message, mixed $value = null): mixed
    {
        if (is_callable($assertion)) {
            $result = $assertion();
        } else {
            $result = (bool) $assertion;
        }

        if (!$result) {
            $this->throwException($message, $value);
        }

        return $assertion;
    }

    /**
     * @template T of mixed
     *
     * @param  T  $assertion
     * @param  string|MessageClosure  $message
     * @param  mixed  $value
     *
     * @return  T
     */
    public function __invoke(mixed $assertion, string|Closure $message, mixed $value = null): mixed
    {
        return $this->assert($assertion, $message, $value);
    }

    public function throwException(string|Closure $message, $value = null): void
    {
        throw $this->exception($message, $value);
    }

    public function exception(string|Closure $message, $value = null)
    {
        return ($this->exceptionHandler)($this->createMessage($message, $value));
    }

    protected function createMessage(string|Closure $message, $value): string
    {
        $described = static::describeValue($value);

        if ($message instanceof Closure) {
            try {
                $message = $message($this->caller, $value, $described);
            } catch (Throwable $e) {
                $message = 'Error when generating assert message: ' . $e->getMessage();
            }
        }

        if (str_contains($message, '%')) {
            $message = sprintf($message, $described);
        }

        if (str_contains($message, '{')) {
            $message = SimpleTemplate::render(
                $message,
                [
                    'caller' => $this->caller,
                    'value' => $value,
                    'type' => $described,
                ],
                '.',
                ['{', '}']
            );
        }

        return $message;
    }

    public static function getCaller(int $backSteps = 2): string
    {
        $trace = debug_backtrace()[$backSteps];

        return trim(($trace['class'] ?? '') . '::' . ($trace['function']), ':') . '()';
    }

    /**
     * @throws \ReflectionException
     */
    public static function describeValue(mixed $value, ?int $truncate = 50): string
    {
        if ($value === null) {
            return '(NULL)';
        }

        if ($value === true) {
            return 'BOOL (TRUE)';
        }

        if ($value === false) {
            return 'BOOL (FALSE)';
        }

        if ($value instanceof Closure) {
            $ref = new ReflectionFunction($value);
            $classRef = $ref->getClosureScopeClass();
            $line = $ref->getStartLine() . ':' . $ref->getEndLine();

            return $classRef?->getName() . "::{Closure} ($line)";
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_string($value)) {
            $value = Str::truncate($value, $truncate, '...');

            return sprintf('string(%s) "%s"', strlen($value), $value);
        }

        if (is_numeric($value)) {
            return sprintf('%s(%s)', gettype($value), $value);
        }

        return get_debug_type($value);
    }
}
