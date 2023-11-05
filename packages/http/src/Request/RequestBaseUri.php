<?php

declare(strict_types=1);

namespace Windwalker\Http\Request;

use BadMethodCallException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriNormalizer;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\Str;

/**
 * @property-read string $full
 * @property-read string $current
 * @property-read string $script
 * @property-read string $root
 * @property-read string $host
 * @property-read string $path
 * @property-read string $route
 *
 * @method string full()
 * @method string current()
 * @method string script($uri = null)
 * @method string root($uri = null)
 * @method string route()
 * @method string host($uri = null)
 * @method string path($uri = null)
 * @method string scheme()
 */
class RequestBaseUri extends Uri implements \JsonSerializable
{
    use InstanceCacheTrait;

    protected ?Uri $origin = null;

    protected ?string $scriptName = null;

    public static function parseFromRequest(ServerRequestInterface $request): static
    {
        $uri = new static((string) $request->getUri());
        $uri->original = (string) $uri;

        $server = $request->getServerParams();

        $uri = static::prepareBaseUri($uri, $server);

        // Get the host and path from the URI.
        $path = rtrim($uri->getPath(), '/\\');
        $script = trim($server['SCRIPT_NAME'] ?? '', '/');

        // Check if the path includes "index.php".
        if ($script && str_starts_with($path, $script)) {
            // Remove the index.php portion of the path.
            $path = substr_replace($path, '', strpos($path, $script), strlen($script));
            $path = rtrim($path, '/\\');
        }

        $uri->withPath($path);
        $uri = $uri->withScriptName(pathinfo($script, PATHINFO_BASENAME));

        return $uri;
    }

    protected static function prepareBaseUri(UriInterface $uri, array $server): UriInterface
    {
        // If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
        if (str_contains(PHP_SAPI, 'cgi') && !ini_get('cgi.fix_pathinfo') && !empty($server['REQUEST_URI'])) {
            // We aren't expecting PATH_INFO within PHP_SELF so this should work.
            $uri = $uri->withPath(rtrim(dirname($server['PHP_SELF'] ?? ''), '/\\'));
        } else {
            // Pretty much everything else should be handled with SCRIPT_NAME.
            $uri = $uri->withPath(rtrim(dirname($server['SCRIPT_NAME'] ?? ''), '/\\'));
        }

        // Clear the unused parts of the requested URI.
        return $uri->withFragment('');
    }

    /**
     * @param  string|null  $ignore
     *
     * @return string|null
     */
    public function getScriptName(?string $ignore = null): ?string
    {
        $script = $this->scriptName;

        if ($script === $ignore) {
            return '';
        }

        return $script;
    }

    /**
     * @param  string  $scriptName
     *
     * @return  static  Return self to support chaining.
     */
    public function withScriptName(string $scriptName): static
    {
        $new = $this;
        $new->scriptName = $scriptName;

        return $new;
    }

    /**
     * addPrefix
     *
     * @param  string  $name
     * @param  string  $url
     *
     * @return  string
     */
    public function suffix(string $name, string $url): string
    {
        return rtrim($this->$name, '/') . '/' . ltrim($url, '/');
    }

    public function absolute(string $url, bool $full = false): string
    {
        // If uri has no scheme
        if (!preg_match('#^[a-z]+\://#i', $url)) {
            // We just need the prefix since we have a path relative to the root.
            if (str_starts_with($url, '/')) {
                $url = $this->addPrefix(
                    $url,
                    $full
                        ? $this->toString(Uri::FULL_HOST)
                        : ''
                );
            } else {
                $url = $this->addPrefix(
                    $url,
                    $full
                        ? $this->toString(Uri::FULL_HOST | Uri::PATH)
                        : $this->toString(Uri::PATH)
                );
            }
        }

        return $url;
    }

    public function addPrefix(string $url, string $prefix = '/'): string
    {
        return rtrim($prefix, '/') . Str::ensureLeft($url, '/');
    }

    public static function getFields(): array
    {
        return [
            'full',
            'current',
            'script',
            'root',
            'host',
            'path',
            'route',
        ];
    }

    public function all(): array
    {
        $all = [];

        foreach (static::getFields() as $field) {
            $all[$field] = $this->__get($field);
        }

        return $all;
    }

    /**
     * clear
     *
     * @param  string  $uri
     *
     * @return  string
     *
     * @since  4.0
     */
    public static function normalize(string $uri): string
    {
        return (new Uri($uri))->toString();
    }

    public function base(bool $full = false): string
    {
        $base = static::normalize(
            $full
                ? $this->path
                : $this->root
        );

        return $base ?: '/';
    }

    /**
     * addUriBase
     *
     * @param  string  $uri
     * @param  string  $path
     *
     * @return string
     *
     * @since  3.5.22.6
     */
    public function addUriBase(string $uri, ?string $base = null): string
    {
        if (!static::isAbsoluteUrl($uri)) {
            $base ??= $this->path;

            $uri = $this::normalize($base . '/' . $uri);
        }

        return $uri;
    }

    public function makeFull(string $uri): string
    {
        $uri = $this->addUriBase($uri, $this->root);

        if ($uri[0] === '/') {
            $uri = $this->host($uri);
        }

        return $uri;
    }

    /**
     * isAbsoluteUrl
     *
     * @param  string  $uri
     *
     * @return  boolean
     */
    public static function isAbsoluteUrl(string $uri): bool
    {
        return stripos($uri, 'http') === 0 || str_starts_with($uri, '/');
    }

    public static function isFullUrl(string $uri): bool
    {
        return stripos($uri, 'http') === 0;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }

    public function __get(string $name)
    {
        switch ($name) {
            case 'full':
                return $this->cacheStorage['full'] ??= $this->original;

            case 'current':
                return $this->cacheStorage['current'] ??= rtrim(
                    Uri::wrap($this->original)
                        ->toString(
                            static::FULL_HOST | static::PATH
                        ),
                    '/'
                );

            case 'script':
                return $this->scriptName;

            case 'root':
                return $this->cacheStorage['root'] ??= UriNormalizer::ensureDir(
                    $this->toString(static::FULL_HOST | static::PATH),
                );

            case 'host':
                return $this->cacheStorage['host'] ??= $this->toString(static::FULL_HOST);

            case 'path':
                return $this->cacheStorage['path'] ??= UriNormalizer::ensureDir($this->toString(static::PATH));

            case 'route':
                return $this->cacheStorage['route'] ??= (function () {
                    // Set the extended (non-base) part of the request URI as the route.
                    $route = substr_replace($this->current, '', 0, strlen($this->root));

                    $file = explode('/', $this->script);
                    $file = array_pop($file);

                    if ($file === '' || str_starts_with($route, $file)) {
                        $route = trim(substr($route, strlen($file)), '/');
                    }

                    return UriNormalizer::ensureDir(rtrim($route, '/'));
                })();
        }

        return $this->$name;
    }

    /**
     * __call
     *
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed
     */
    public function __call(string $name, array $args): mixed
    {
        if (in_array($name, static::getFields())) {
            if (isset($args[0])) {
                return $this->suffix($name, $args[0]);
            }

            return $this->$name;
        }

        throw new BadMethodCallException('Method: ' . __CLASS__ . '::' . $name . '() not found.');
    }

    /**
     * @param  string  $origin
     *
     * @return  static  Return self to support chaining.
     */
    public function withOriginal(string $origin): static
    {
        $new = clone $this;
        $new->original = $origin;

        return $new;
    }
}
