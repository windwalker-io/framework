<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Uri;

use GuzzleHttp\UriTemplate\UriTemplate as GuzzleUriTemplate;
use Rize\UriTemplate as RizeUriTemplate;

/**
 * The UriTemplate class.
 */
class UriTemplate implements \Stringable
{
    protected \Closure $expandHandler;

    protected \Closure $extractHandler;

    public function __construct(protected string $template, protected array $vars = [])
    {
    }

    public function expand(array $vars = []): string
    {
        return (string) $this->getExpandHandler()($this->template, array_merge($this->vars, $vars));
    }

    public function extract(string $uri, bool $strict = false): array
    {
        return (array) $this->getExtractHandler()($this->template, $uri, $strict);
    }

    public function bind(string $name, mixed &$value): static
    {
        $this->vars[$name] = &$value;

        return $this;
    }

    public function bindValue(string $name, mixed $value): static
    {
        return $this->bind($name, $value);
    }

    /**
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * @param  array  $vars
     *
     * @return  static  Return self to support chaining.
     */
    public function setVars(array $vars): static
    {
        $this->vars = $vars;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param  string  $template
     *
     * @return  static  Return self to support chaining.
     */
    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return \Closure
     */
    public function getExpandHandler(): \Closure
    {
        return $this->expandHandler ??= static function (string $template, array $vars = []) {
            if (class_exists(RizeUriTemplate::class)) {
                return (new RizeUriTemplate())->expand($template, $vars);
            }

            if (class_exists(GuzzleUriTemplate::class)) {
                return GuzzleUriTemplate::expand($template, $vars);
            }

            throw new \DomainException(
                'Please install `rize/uri-template` or `guzzlehttp/uri-template` to support URI Template expand.'
            );
        };
    }

    /**
     * @param  \Closure  $expandHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setExpandHandler(\Closure $expandHandler): static
    {
        $this->expandHandler = $expandHandler;

        return $this;
    }

    /**
     * @return \Closure
     */
    public function getExtractHandler(): \Closure
    {
        return $this->extractHandler ?? static function (string $template, string $uri, bool $strict = false) {
            if (class_exists(RizeUriTemplate::class)) {
                return (new RizeUriTemplate())->extract($template, $uri, $strict);
            }

            throw new \DomainException(
                'Please install `rize/uri-template` to support URI Template extract.'
            );
        };
    }

    /**
     * @param  \Closure  $extractHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setExtractHandler(\Closure $extractHandler): static
    {
        $this->extractHandler = $extractHandler;

        return $this;
    }

    public function __toString(): string
    {
        return $this->expand();
    }
}
