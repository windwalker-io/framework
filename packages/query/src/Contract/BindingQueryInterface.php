<?php

declare(strict_types=1);

namespace Windwalker\Query\Contract;

/**
 * Parameter binding related query methods.
 */
interface BindingQueryInterface
{
    /**
     * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query
     * execution. Also removes a variable that has been bounded from the internal bounded array when the passed in
     * value is null.
     *
     * @param  string|integer|array  $key            The key that will be used in your SQL query to reference the value.
     *                                               Usually of the form ':key', but can also be an integer.
     * @param  mixed                 &$value         The value that will be bound. The value is passed by reference to
     *                                               support output parameters such as those possible with stored
     *                                               procedures.
     * @param  mixed                 $dataType       Constant corresponding to a SQL datatype.
     * @param  integer               $length         The length of the variable. Usually required for OUTPUT parameters.
     * @param  array                 $driverOptions  Optional driver options to be used.
     *
     * @return  static
     *
     * @since   3.5.5
     */
    public function bindParam(
        string|int|array|null $key = null,
        mixed &$value = null,
        mixed $dataType = null,
        int $length = 0,
        mixed $driverOptions = null
    ): static;

    /**
     * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query
     * execution. Also removes a variable that has been bounded from the internal bounded array when the passed in
     * value is null.
     *
     * @param  string|integer|array  $key       The key that will be used in your SQL query to reference the
     *                                          value. Usually of the form ':key', but can also be an integer.
     * @param  mixed                 &$value    The value that will be bound. The value is passed by reference to
     *                                          support output parameters such as those possible with stored
     *                                          procedures.
     * @param  mixed                 $dataType  Constant corresponding to a SQL datatype.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function bind(
        string|int|array|null $key = null,
        mixed $value = null,
        mixed $dataType = null
    ): static;

    /**
     * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then
     * that item is returned.
     *
     * @param  mixed  $key  The bounded variable key to retrieve.
     *
     * @return  array|null
     *
     * @since   2.0
     */
    public function getBounded(mixed $key = null): ?array;

    /**
     * @return  static
     *
     * @codeCoverageIgnore
     */
    public function resetBounded(): static;

    /**
     * unbind
     *
     * @param  string|array  $keys
     *
     * @return  static
     */
    public function unbind(mixed $keys): static;
}
