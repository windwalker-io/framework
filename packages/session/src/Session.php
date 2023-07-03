<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session;

use LogicException;
use Windwalker\Session\Bridge\BridgeInterface;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\TypeCast;

use function Windwalker\tap;

/**
 * The Session class.
 */
class Session implements SessionInterface, ArrayAccessibleInterface
{
    use OptionAccessTrait;

    protected mixed $storage = [];

    protected ?BridgeInterface $bridge = null;

    protected ?CookiesInterface $cookies;

    protected ?FlashBag $flashBag = null;

    /**
     * @var callable
     */
    protected $destructor = null;

    /**
     * Session constructor.
     *
     * @param  array                  $options
     * @param  BridgeInterface|null   $bridge
     * @param  CookiesInterface|null  $cookies
     *
     * @throws \Exception
     */
    public function __construct(array $options = [], ?BridgeInterface $bridge = null, ?CookiesInterface $cookies = null)
    {
        $this->prepareOptions(
            [
                static::OPTION_AUTO_COMMIT => true, // Only for non-native handlers
                'ini' => [
                    //
                ],
            ],
            $options
        );

        $this->bridge = $bridge ?? new NativeBridge();
        $this->cookies = $cookies ?? Cookies::create()
            ->httpOnly(true)
            ->expires('+30days')
            ->secure(false)
            ->sameSite(CookiesInterface::SAMESITE_LAX);
    }

    public function registerINI(): void
    {
        if (!headers_sent()) {
            foreach ((array) $this->getOption('ini') as $key => $value) {
                if ($value !== null) {
                    if (!str_starts_with($key, 'session.')) {
                        $key = 'session.' . $key;
                    }

                    ini_set($key, $value);
                }
            }
        }
    }

    public function setName(string $name): static
    {
        $this->bridge->setSessionName($name);

        return $this;
    }

    public function getName(): string
    {
        return $this->bridge->getSessionName();
    }

    public function setId(string $id): static
    {
        $this->bridge->setId($id);

        return $this;
    }

    public function getId(): ?string
    {
        return $this->bridge->getId();
    }

    public function start(): bool
    {
        if ($this->getOption('disabled')) {
            return false;
        }

        if ($this->bridge->isStarted()) {
            return true;
        }

        $this->registerINI();

        if (
            $this->bridge instanceof NativeBridge
            && $this->cookies instanceof Cookies
            && $this->getOptionAndINI('use_cookies')
        ) {
            // If you use auto cookie, we set cookie params first.
            // Only Native session bridge with native cookies use this.
            $this->setCookieParams();
        } else {
            // Otherwise, set session ID from $_COOKIE.
            $id = $this->cookies->get($this->bridge->getSessionName());

            if ($id !== null) {
                $this->bridge->setId($id);
            }

            // Must set cookie and update expires after session end.
            if (!$this->destructor && $this->getOption(static::OPTION_AUTO_COMMIT)) {
                $this->destructor = [$this, 'stop'];

                register_shutdown_function(fn() => $this->destruct());
            }
        }

        return tap(
            $this->bridge->start(),
            // Send Cookies after started
            function () {
                $this->storage = &$this->bridge->getStorage();

                $this->cookies->set(
                    $this->bridge->getSessionName(),
                    $this->bridge->getId()
                );
            }
        );
    }

    public function stop(bool $unset = true): bool
    {
        return $this->bridge->writeClose($unset);
    }

    public function fork(?string $newId = null): Session
    {
        if (!$this->isStarted()) {
            throw new LogicException(
                static::class
                . '::fork() only work after started, before started, you can use clone to copy it.'
            );
        }

        $new = clone $this;

        $bridge = $new->bridge;

        // To break old Session data reference.
        $storage = $this->storage;
        unset($this->storage);
        $this->storage = $storage;

        if ($newId === null) {
            // If using native php session, we can only regenerate new ID
            $bridge->regenerate(false, true);
        } else {
            // If using our implemented php bridge, we can fork it with specific ID.
            if ($this->bridge instanceof NativeBridge) {
                throw new LogicException('Fork with specific ID does not supports for NativeBridge');
            }

            $data = $new->getStorage();

            $bridge->close();
            $bridge->unset();

            $bridge->setId($newId);
            $bridge->start();
            $bridge->setStorage($data);
        }

        return $new;
    }

    public function regenerate(bool $deleteOld = false, bool $saveOld = true): bool
    {
        $this->bridge->regenerate($deleteOld, $saveOld);

        $this->cookies->set(
            $this->bridge->getSessionName(),
            $this->bridge->getId()
        );

        return true;
    }

    public function restart(): bool
    {
        $this->regenerate(true);

        return true;
    }

    /**
     * Get value from this object.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     */
    public function &get(mixed $key): mixed
    {
        $this->start();

        $ret = null;

        if (!$this->has($key)) {
            return $ret;
        }

        return $this->storage[$key];
    }

    /**
     * Set value to this object.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return  static
     */
    public function set(mixed $key, mixed $value): static
    {
        $this->start();

        $this->storage[$key] = $value;

        return $this;
    }

    /**
     * Set value default if not exists.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function def(mixed $key, mixed $default): mixed
    {
        $this->start();

        $this->storage[$key] ??= $default;

        return $this->getStorage()[$key];
    }

    /**
     * Check a key exists or not.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function has(mixed $key): bool
    {
        $this->start();

        return isset($this->storage[$key]);
    }

    /**
     * remove
     *
     * @param  mixed  $key
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function remove(mixed $key): static
    {
        $this->start();

        if ($this->has($key)) {
            unset($this->storage[$key]);
        }

        return $this;
    }

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        $this->start();

        if (!$recursive) {
            return $this->getStorage();
        }

        return TypeCast::toArray($this->getStorage(), true, $onlyDumpable);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $this->start();

        return $this->getStorage();
    }

    /**
     * count
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    public function count(): int
    {
        $this->start();

        return count($this->getStorage());
    }

    /**
     * @return array|null
     */
    public function &getStorage(): ?array
    {
        $this->start();

        return $this->bridge->getStorage();
    }

    public function overrideWith(string $name, mixed $override = null): mixed
    {
        $this->start();

        if ($override === null) {
            return $this->get($name);
        }

        $this->set($name, $override);

        return $override;
    }

    /**
     * clear
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->start();

        return $this->bridge->unset();
    }

    public function &all(): array
    {
        $this->start();

        return $this->getStorage();
    }

    public function destroy(): void
    {
        $this->getBridge()->destroy();
    }

    /**
     * Set session cookie parameters, this method should call before session started.
     *
     * @param  array  $options  An associative array which may have any of the keys lifetime, path, domain,
     *                          secure, httponly and samesite. The values have the same meaning as described
     *                          for the parameters with the same name. The value of the samesite element
     *                          should be either Lax or Strict. If any of the allowed options are not given,
     *                          their default values are the same as the default values of the explicit
     *                          parameters. If the samesite element is omitted, no SameSite cookie attribute
     *                          is set.
     *
     * @since   2.0
     */
    public function setCookieParams(?array $options = null): void
    {
        if (!headers_sent() && $this->getCookies() instanceof Cookies) {
            $options ??= $this->cookies->getOptions();

            if (isset($options['expires'])) {
                $options['lifetime'] = max($options['expires'] - time(), 0);

                unset($options['expires']);
            }

            session_set_cookie_params($options);
        }
    }

    /**
     * @return CookiesInterface|null
     */
    public function getCookies(): ?CookiesInterface
    {
        return $this->cookies;
    }

    /**
     * @param  CookiesInterface|null  $cookies
     *
     * @return  static  Return self to support chaining.
     */
    public function setCookies(?CookiesInterface $cookies): static
    {
        $this->cookies = $cookies;

        return $this;
    }

    protected function getOptionAndINI(string $name): mixed
    {
        return $this->getOption('ini')[$name] ?? ini_get('session.' . $name);
    }

    /**
     * @return FlashBag
     */
    public function getFlashBag(): FlashBag
    {
        $this->start();

        if ($this->flashBag === null) {
            $this->setFlashBag(new FlashBag());
        }

        return $this->flashBag;
    }

    /**
     * @param  FlashBag|null  $flashBag
     *
     * @return  static  Return self to support chaining.
     */
    public function setFlashBag(?FlashBag $flashBag): static
    {
        $this->flashBag = $flashBag;

        if ($flashBag) {
            $storage = &$this->getStorage();

            $flashBag->link($storage);
        }

        return $this;
    }

    /**
     * Add a flash message.
     *
     * @param  array|string  $messages  The message you want to set, can be an array to storage multiple messages.
     * @param  string        $type      The message type, default is `info`.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function addFlash(array|string $messages, string $type = 'info'): static
    {
        foreach ((array) $messages as $message) {
            $this->getFlashBag()->add($message, $type);
        }

        return $this;
    }

    /**
     * Take all flashes and clean them from bag.
     *
     * @return  array  All flashes data.
     *
     * @since   2.0
     */
    public function getFlashes(): array
    {
        return $this->getFlashBag()->all();
    }

    /**
     * @return BridgeInterface
     */
    public function getBridge(): BridgeInterface
    {
        return $this->bridge;
    }

    /**
     * @param  BridgeInterface  $bridge
     *
     * @return  static  Return self to support chaining.
     */
    public function setBridge(BridgeInterface $bridge): static
    {
        $this->bridge = $bridge;

        return $this;
    }

    /**
     * offsetExists
     *
     * @param  mixed  $key
     *
     * @return  bool
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->has($key);
    }

    /**
     * offsetGet
     *
     * @param  mixed  $key
     *
     * @return  mixed
     */
    public function &offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     * offsetSet
     *
     * @param  mixed  $key
     * @param  mixed  $value
     *
     * @return  void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * offsetUnset
     *
     * @param  mixed  $key
     *
     * @return  void
     */
    public function offsetUnset(mixed $key): void
    {
        $this->remove($key);
    }

    /**
     * __clone
     *
     * @return  void
     */
    public function __clone()
    {
        $this->bridge = clone $this->bridge;
        $this->cookies = clone $this->cookies;
        $this->flashBag = $this->flashBag ? clone $this->flashBag : $this->flashBag;
    }

    /**
     * isStarted
     *
     * @return  bool
     */
    public function isStarted(): bool
    {
        return $this->bridge->isStarted();
    }

    public function __destruct()
    {
        $this->destruct();
    }

    public function destruct(): void
    {
        if ($this->destructor) {
            ($this->destructor)();

            $this->destructor = null;
        }
    }
}
