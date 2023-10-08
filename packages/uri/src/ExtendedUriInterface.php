<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Interface ExtendedUriInterface
 */
interface ExtendedUriInterface extends UriInterface
{
    /**
     * @param  string|null  $user
     *
     * @return  static
     */
    public function withUser(?string $user): static;

    /**
     * @param  string|null  $password
     *
     * @return  static
     */
    public function withPassword(?string $password): static;

    /**
     * @param  array|string  $query
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function withQueryParams(array|string $query): static;

    /**
     * @param  string        $name
     * @param  array|string  $value
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function withVar(string $name, mixed $value): static;

    /**
     * @param  string  $name
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function withoutVar(string $name): static;

    public function withPathAppend(string $suffix): static;

    public function getQueryValues(): array;

    /**
     * Checks if variable exists.
     *
     * @param  string  $name  Name of the query variable to check.
     *
     * @return  bool  True if the variable exists.
     *
     * @since   2.0
     */
    public function hasVar(string $name): bool;

    /**
     * Returns a query variable by name.
     *
     * @param  string       $name     Name of the query variable to get.
     * @param  string|null  $default  Default value to return if the variable is not set.
     *
     * @return  mixed   Query variables.
     *
     * @since   2.0
     */
    public function getVar(string $name, string $default = null): mixed;

    /**
     * Get URI username
     * Returns the username, or null if no username was specified.
     *
     * @return  string  The URI username.
     *
     * @since   2.0
     */
    public function getUser(): string;

    /**
     * Get URI password
     * Returns the password, or null if no password was specified.
     *
     * @return  string  The URI password.
     *
     * @since   2.0
     */
    public function getPassword(): string;

    /**
     * Checks whether the current URI is using HTTPS.
     *
     * @return  bool  True if using SSL via HTTPS.
     *
     * @since   2.0
     */
    public function isSSL(): bool;
}
