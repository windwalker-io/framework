<?php

declare(strict_types=1);

namespace Windwalker\Environment;

/**
 * The Environment class.
 *
 * @since  2.0
 */
class Environment
{
    /**
     * Property os.
     *
     * @var string
     */
    protected static string $os;

    /**
     * Property uname.
     *
     * @var  string
     */
    protected static string $uname = PHP_OS;

    /**
     * Property globals.
     *
     * @var  array
     */
    protected array $serverParams = [];

    /**
     * Class init.
     *
     * @param  array|null  $serverParams
     */
    public function __construct(
        array $serverParams = null,
    ) {
        $this->serverParams = $serverParams ?? $_SERVER;
    }

    /**
     * @return  bool
     */
    public static function isWebServer(): bool
    {
        return PhpHelper::isWebServer();
    }

    /**
     * isCli
     *
     * @return  bool
     */
    public static function isCli(): bool
    {
        return PhpHelper::isCli();
    }

    /**
     * getOS
     *
     * @see  https://gist.github.com/asika32764/90e49a82c124858c9e1a
     *
     * @return  string
     */
    public static function getOS(): string
    {
        return static::$os ??= strtoupper(substr(static::$uname, 0, 3));
    }

    /**
     * isWin
     *
     * @return  bool
     */
    public static function isWindows(): bool
    {
        return static::getOS() === 'WIN';
    }

    /**
     * isUnix
     *
     * @see  https://gist.github.com/asika32764/90e49a82c124858c9e1a
     *
     * @return  bool
     */
    public static function isUnix(): bool
    {
        $unames = [
            'CYG',
            'DAR',
            'FRE',
            'HP-',
            'IRI',
            'LIN',
            'NET',
            'OPE',
            'SUN',
            'UNI',
        ];

        return in_array(static::getOS(), $unames);
    }

    /**
     * isLinux
     *
     * @return  bool
     */
    public static function isLinux(): bool
    {
        return static::getOS() === 'LIN';
    }

    /**
     * Method to set property os
     *
     * @param  string  $os
     */
    public static function setOS(string $os): void
    {
        static::$os = $os;
    }

    /**
     * Method to get property Uname
     *
     * @return  string
     */
    public static function getUname(): string
    {
        return static::$uname;
    }

    /**
     * @param  string  $uname
     *
     * @return  void
     */
    public static function setUname(string $uname): void
    {
        static::$uname = $uname;
    }

    /**
     * @return  string
     */
    public function getWorkingDirectory(): string
    {
        return getcwd();
    }

    /**
     * getRoot
     *
     * @param  bool  $full
     *
     * @return  string
     */
    public function getRoot(bool $full = true): string
    {
        return dirname($this->getEntry($full));
    }

    public function getUserHomeDir(): ?string
    {
        return $this->getServerParam('HOME')
            ?? ($this->getServerParam('HOMEDRIVE') . $this->getServerParam('HOMEPATH') ?? null);
    }

    public function getUserName(): ?string
    {
        return $this->getServerParam('USER') ?: $this->getServerParam('USERNAME');
    }

    public function getDeviceName(): ?string
    {
        return $this->getServerParam('USERDOMAIN');
    }

    public function getTempDir(): string
    {
        return sys_get_temp_dir();
    }

    /**
     * @param  bool  $full
     *
     * @return  string
     */
    public function getEntry(bool $full = true): string
    {
        $key = $full ? 'SCRIPT_FILENAME' : 'SCRIPT_NAME';

        $wdir = $this->getWorkingDirectory();

        $file = $this->getServerParam($key);

        if (str_starts_with($file, $wdir)) {
            $file = substr($file, strlen($wdir));
        }

        $file = trim($file, '.' . DIRECTORY_SEPARATOR);

        if ($full && static::isCli()) {
            $file = $wdir . DIRECTORY_SEPARATOR . $file;
        }

        return $file;
    }

    /**
     * @return  string
     */
    public function getServerPublicRoot(): string
    {
        return $this->getServerParam('DOCUMENT_ROOT');
    }

    /**
     * @param  bool  $withParams
     *
     * @return  string
     */
    public function getRequestUri(bool $withParams = true): string
    {
        if ($withParams) {
            return $this->getServerParam('REQUEST_URI');
        }

        return $this->getServerParam('PHP_SELF');
    }

    /**
     * @return  string
     */
    public function getHost(): string
    {
        return $this->getServerParam('HTTP_HOST');
    }

    /**
     * @return  string
     */
    public function getPort(): string
    {
        return $this->getServerParam('SERVER_PORT');
    }

    /**
     * @return  string
     */
    public function getScheme(): string
    {
        return $this->getServerParam('REQUEST_SCHEME');
    }

    /**
     * @return bool
     */
    public function isSSL(): bool
    {
        return $this->getScheme() === 'https';
    }

    /**
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return  mixed
     */
    protected function getServerParam(string $key, mixed $default = null): mixed
    {
        return $this->serverParams[$key] ?? $default;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @param  array  $serverParams
     *
     * @return  static  Return self to support chaining.
     */
    public function setServerParams(array $serverParams): static
    {
        $this->serverParams = $serverParams;

        return $this;
    }
}
