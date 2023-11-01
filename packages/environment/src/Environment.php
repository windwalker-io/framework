<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

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
     * @var string|null
     */
    protected ?string $os = null;

    /**
     * Property uname.
     *
     * @var  string
     */
    protected string $uname = PHP_OS;

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
    public function isWebServer(): bool
    {
        return PhpHelper::isWebServer();
    }

    /**
     * isCli
     *
     * @return  bool
     */
    public function isCli(): bool
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
    public function getOS(): string
    {
        if (!$this->os) {
            // Detect the native operating system type.
            $this->os = strtoupper(substr($this->uname, 0, 3));
        }

        return $this->os;
    }

    /**
     * isWin
     *
     * @return  bool
     */
    public function isWindows(): bool
    {
        return $this->getOS() === 'WIN';
    }

    /**
     * isUnix
     *
     * @see  https://gist.github.com/asika32764/90e49a82c124858c9e1a
     *
     * @return  bool
     */
    public function isUnix(): bool
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

        return in_array($this->getOS(), $unames);
    }

    /**
     * isLinux
     *
     * @return  bool
     */
    public function isLinux(): bool
    {
        return $this->getOS() === 'LIN';
    }

    /**
     * Method to set property os
     *
     * @param  string|null  $os
     *
     * @return  static  Return self to support chaining.
     */
    public function setOS(?string $os): static
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Method to get property Uname
     *
     * @return  string
     */
    public function getUname(): string
    {
        return $this->uname;
    }

    /**
     * Method to set property uname
     *
     * @param  string  $uname
     *
     * @return  static  Return self to support chaining.
     */
    public function setUname(string $uname): static
    {
        $this->uname = $uname;

        return $this;
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
        return $this->getServerParam('USERNAME');
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

        if ($full && $this->isCli()) {
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
