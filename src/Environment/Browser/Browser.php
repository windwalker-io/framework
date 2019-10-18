<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Environment\Browser;

/**
 * Class to model a Web Client.
 *
 * This class is based on Joomla WebClient
 *
 * @since  2.0
 */
class Browser
{
    public const DEVICE_WINDOWS = 'Windows';

    public const DEVICE_WINDOWS_PHONE = 'Windows Phone';

    public const DEVICE_WINDOWS_CE = 'Windows CE';

    public const DEVICE_IPHONE = 'iPhone';

    public const DEVICE_IPAD = 'iPad';

    public const DEVICE_IPOD = 'iPod';

    public const DEVICE_MAC = 'Mac';

    public const DEVICE_BLACKBERRY = 'Blackberry';

    public const DEVICE_ANDROID = 'Android';

    public const DEVICE_LINUX = 'Linux';

    public const ENGINE_TRIDENT = 'Trident';

    public const ENGINE_EDGE_HTML = 'EdgeHTML';

    public const ENGINE_WEBKIT = 'Webkit';

    public const ENGINE_GECKO = 'Gecko';

    public const ENGINE_PRESTO = 'Presto';

    public const ENGINE_KHTML = 'KHTML';

    public const ENGINE_BLINK = 'Blink';

    public const AMAYA = 'Amaya';

    public const IE = 'MSIE';

    public const EDGE = 'Edge';

    public const EDG = 'Edg';

    public const FIREFOX = 'Firefox';

    public const CHROME = 'Chrome';

    public const SAFARI = 'Safari';

    public const OPERA = 'Opera';

    public const VIVALDI = 'Vivaldi';

    public const ANDROID_TABLET = 'Android Tablet';

    /**
     * The detected platform on which the web client runs.
     *
     * @var    integer
     * @since  2.0
     */
    protected $device;

    /**
     * True if the web client is a mobile device.
     *
     * @var    boolean
     * @since  2.0
     */
    protected $mobile = false;

    /**
     * The detected rendering engine used by the web client.
     *
     * @var    integer
     * @since  2.0
     */
    protected $engine;

    /**
     * The detected browser used by the web client.
     *
     * @var    integer
     * @since  2.0
     */
    protected $browser;

    /**
     * The detected browser version used by the web client.
     *
     * @var    string
     * @since  2.0
     */
    protected $browserVersion;

    /**
     * The priority order detected accepted languages for the client.
     *
     * @var    array
     * @since  2.0
     */
    protected $languages = [];

    /**
     * The priority order detected accepted encodings for the client.
     *
     * @var    array
     * @since  2.0
     */
    protected $encodings = [];

    /**
     * The web client's user agent string.
     *
     * @var    string
     * @since  2.0
     */
    protected $userAgent;

    /**
     * The web client's accepted encoding string.
     *
     * @var    string
     * @since  2.0
     */
    protected $acceptEncoding;

    /**
     * The web client's accepted languages string.
     *
     * @var    string
     * @since  2.0
     */
    protected $acceptLanguage;

    /**
     * True if the web client is a robot.
     *
     * @var    boolean
     * @since  2.0
     */
    protected $robot = false;

    /**
     * An array of flags determining whether or not a detection routine has been run.
     *
     * @var    array
     * @since  2.0
     */
    protected $detection = [];

    /**
     * Property server.
     *
     * @var    array
     * @since  3.0
     */
    protected $server;

    /**
     * Class constructor.
     *
     * @param   string $userAgent The optional user-agent string to parse.
     * @param   array  $server    The server properties, typically is $_SERVER superglobal.
     *
     * @since   2.0
     */
    public function __construct($userAgent = null, $server = [])
    {
        $this->server = $server = $server ?: $_SERVER;

        // If no explicit user agent string was given attempt to use the implicit one from server environment.
        if (empty($userAgent) && isset($server['HTTP_USER_AGENT'])) {
            $this->userAgent = $server['HTTP_USER_AGENT'];
        } else {
            $this->userAgent = $userAgent;
        }

        // If no explicit acceptable encoding string was given attempt to use the implicit one from server environment.
        if (isset($server['HTTP_ACCEPT_ENCODING'])) {
            $this->acceptEncoding = $server['HTTP_ACCEPT_ENCODING'];
        }

        // If no explicit acceptable languages string was given attempt to use the implicit one from server environment.
        if (isset($server['HTTP_ACCEPT_LANGUAGE'])) {
            $this->acceptLanguage = $server['HTTP_ACCEPT_LANGUAGE'];
        }
    }

    /**
     * Detects the client browser and version in a user agent string.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function detectBrowser($userAgent)
    {
        $patternBrowser = '';

        // Attempt to detect the browser type.  Obviously we are only worried about major browsers.
        if ((stripos($userAgent, 'MSIE') !== false) && (stripos($userAgent, 'Opera') === false)) {
            $this->browser  = static::IE;
            $patternBrowser = 'MSIE';
        } elseif (stripos($userAgent, 'Trident') !== false) {
            $this->browser  = static::IE;
            $patternBrowser = ' rv';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $this->browser  = static::EDGE;
            $patternBrowser = 'Edge';
        } elseif (stripos($userAgent, 'Edg') !== false) {
            $this->browser  = static::EDG;
            $patternBrowser = 'Edg';
        } elseif ((stripos($userAgent, 'Firefox') !== false) && (stripos($userAgent, 'like Firefox') === false)) {
            $this->browser  = static::FIREFOX;
            $patternBrowser = 'Firefox';
        } elseif (stripos($userAgent, 'Vivaldi') !== false) {
            // Vivaldi must before Chrome & Safari
            $this->browser  = static::VIVALDI;
            $patternBrowser = 'Vivaldi';
        } elseif (stripos($userAgent, 'Chrome') !== false) {
            $this->browser  = static::CHROME;
            $patternBrowser = 'Chrome';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $this->browser  = static::SAFARI;
            $patternBrowser = 'Safari';
        } elseif (stripos($userAgent, 'Opera') !== false) {
            $this->browser  = static::OPERA;
            $patternBrowser = 'Opera';
        }

        // If we detected a known browser let's attempt to determine the version.
        if ($this->browser) {
            // Build the REGEX pattern to match the browser version string within the user agent string.
            $pattern = '#(?<browser>Version|' . $patternBrowser . ')[/: ]+(?<version>[0-9.|a-zA-Z.]*)#';

            // Attempt to find version strings in the user agent string.
            $matches = [];

            if (preg_match_all($pattern, $userAgent, $matches)) {
                // Do we have both a Version and browser match?
                if (count($matches['browser']) === 2) {
                    // See whether Version or browser came first, and use the number accordingly.
                    if (strripos($userAgent, 'Version') < strripos($userAgent, $patternBrowser)) {
                        $this->browserVersion = $matches['version'][0];
                    } else {
                        $this->browserVersion = $matches['version'][1];
                    }
                } elseif (count($matches['browser']) > 2) {
                    $key = array_search('Version', $matches['browser'], true);

                    if ($key) {
                        $this->browserVersion = $matches['version'][$key];
                    }
                } else {
                    // We only have a Version or a browser so use what we have.
                    $this->browserVersion = $matches['version'][0];
                }
            }
        }

        // Mark this detection routine as run.
        $this->detection['browser'] = true;
    }

    /**
     * Method to detect the accepted response encoding by the client.
     *
     * @param   string $acceptEncoding The client accept encoding string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function detectEncoding($acceptEncoding)
    {
        // Parse the accepted encodings.
        $this->encodings = array_map('trim', (array) explode(',', $acceptEncoding));

        // Mark this detection routine as run.
        $this->detection['acceptEncoding'] = true;
    }

    /**
     * Detects the client rendering engine in a user agent string.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function detectEngine($userAgent)
    {
        if (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false) {
            // Attempt to detect the client engine -- starting with the most popular ... for now.
            $this->engine = self::ENGINE_TRIDENT;
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $this->engine = self::ENGINE_EDGE_HTML;
        } elseif (stripos($userAgent, 'Edg') !== false) {
            $this->engine = self::ENGINE_BLINK;
        } elseif (stripos($userAgent, 'AppleWebKit') !== false || stripos($userAgent, 'blackberry') !== false) {
            // Evidently blackberry uses WebKit and doesn't necessarily report it.  Bad RIM.
            $this->engine = self::ENGINE_WEBKIT;
        } elseif (stripos($userAgent, 'Chrome') !== false) {
            $result  = explode('/', stristr($userAgent, 'Chrome'));
            $version = explode(' ', $result[1]);

            if ($version[0] >= 28) {
                $this->engine = self::ENGINE_BLINK;
            } else {
                $this->engine = self::ENGINE_WEBKIT;
            }
        } elseif (stripos($userAgent, 'Gecko') !== false && stripos($userAgent, 'like Gecko') === false) {
            // We have to check for like Gecko because some other browsers spoof Gecko.
            $this->engine = self::ENGINE_GECKO;
        } elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'Presto') !== false) {
            // Sometimes Opera browsers don't say Presto.
            $this->engine = self::ENGINE_PRESTO;
        } elseif (stripos($userAgent, 'KHTML') !== false) {
            // *sigh*
            $this->engine = self::ENGINE_KHTML;
        } elseif (stripos($userAgent, 'Amaya') !== false) {
            // Lesser known engine but it finishes off the major list from Wikipedia :-)
            $this->engine = self::AMAYA;
        }

        // Mark this detection routine as run.
        $this->detection['engine'] = true;
    }

    /**
     * Method to detect the accepted languages by the client.
     *
     * @param   mixed $acceptLanguage The client accept language string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function detectLanguage($acceptLanguage)
    {
        // Parse the accepted encodings.
        $this->languages = array_map('trim', (array) explode(',', $acceptLanguage));

        // Mark this detection routine as run.
        $this->detection['acceptLanguage'] = true;
    }

    /**
     * Detects the client platform in a user agent string.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function detectDevice($userAgent)
    {
        // Attempt to detect the client platform.
        switch (true) {
            case stripos($userAgent, 'Windows') !== false:
                $this->device = self::DEVICE_WINDOWS;

                // Let's look at the specific mobile options in the Windows space.
                if (stripos($userAgent, 'Windows Phone') !== false) {
                    $this->mobile = true;
                    $this->device = self::DEVICE_WINDOWS_PHONE;
                } elseif (stripos($userAgent, 'Windows CE') !== false) {
                    $this->mobile = true;
                    $this->device = self::DEVICE_WINDOWS_CE;
                }

                break;

            case stripos($userAgent, 'iPhone') !== false:
                // Interestingly 'iPhone' is present in all iOS devices so far including iPad and iPods.
                $this->mobile = true;
                $this->device = self::DEVICE_IPHONE;

                // Let's look at the specific mobile options in the iOS space.
                if (stripos($userAgent, 'iPad') !== false) {
                    $this->device = self::DEVICE_IPAD;
                } elseif (stripos($userAgent, 'iPod') !== false) {
                    $this->device = self::DEVICE_IPOD;
                }

                break;

            case stripos($userAgent, 'iPad') !== false:
                // In case where iPhone is not mentioned in iPad user agent string
                $this->mobile = true;
                $this->device = self::DEVICE_IPAD;

                break;

            case stripos($userAgent, 'iPod') !== false:
                // In case where iPhone is not mentioned in iPod user agent string
                $this->mobile = true;
                $this->device = self::DEVICE_IPOD;

                break;

            case preg_match('/macintosh|mac os x/i', $userAgent):
                // This has to come after the iPhone check because mac strings are also present in iOS devices.
                $this->device = self::DEVICE_MAC;

                break;

            case stripos($userAgent, 'Blackberry') !== false:
                $this->mobile = true;
                $this->device = self::DEVICE_BLACKBERRY;

                break;

            case stripos($userAgent, 'Android') !== false:
                $this->mobile = true;
                $this->device = self::DEVICE_ANDROID;
                /*
                 * Attempt to distinguish between Android phones and tablets
                 * There is no totally foolproof method but certain rules almost always hold
                 * Android 3.x is only used for tablets
                 * Some devices and browsers encourage users to change their UA string to include Tablet.
                 * Google encourages manufacturers to exclude the string Mobile from tablet device UA strings.
                 * In some modes Kindle Android devices include the string Mobile but they include the string Silk.
                 */
                if (stripos($userAgent, 'Android 3') !== false || stripos($userAgent, 'Tablet') !== false
                    || stripos($userAgent, 'Mobile') === false || stripos($userAgent, 'Silk') !== false) {
                    $this->device = self::ANDROID_TABLET;
                }

                break;

            case stripos($userAgent, 'Linux') !== false:
                $this->device = self::DEVICE_LINUX;

                break;
        }

        // Mark this detection routine as run.
        $this->detection['device'] = true;
    }

    /**
     * Determines if the browser is a robot or not.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function detectRobot($userAgent)
    {
        if (preg_match('/http|bot|robot|spider|crawler|curl|^$/i', $userAgent)) {
            $this->robot = true;
        } else {
            $this->robot = false;
        }

        $this->detection['robot'] = true;
    }

    /**
     * getPlatform
     *
     * @param bool $refresh
     *
     * @return  int
     */
    public function getDevice($refresh = false)
    {
        if (empty($this->detection['device']) || $refresh) {
            $this->detectDevice($this->userAgent);
        }

        return $this->device;
    }

    /**
     * getMobile
     *
     * @param bool $refresh
     *
     * @return  boolean
     */
    public function isMobile($refresh = false)
    {
        if (empty($this->detection['device']) || $refresh) {
            $this->detectDevice($this->userAgent);
        }

        return $this->mobile;
    }

    /**
     * getEngine
     *
     * @param bool $refresh
     *
     * @return  string
     */
    public function getEngine($refresh = false)
    {
        if (empty($this->detection['engine']) || $refresh) {
            $this->detectEngine($this->userAgent);
        }

        return $this->engine;
    }

    /**
     * getBrowser
     *
     * @param bool $refresh
     *
     * @return  int
     */
    public function getBrowser($refresh = false)
    {
        if (empty($this->detection['browser']) || $refresh) {
            $this->detectBrowser($this->userAgent);
        }

        return $this->browser;
    }

    /**
     * getBrowserVersion
     *
     * @param bool $refresh
     *
     * @return  string
     */
    public function getBrowserVersion($refresh = false)
    {
        if (empty($this->detection['browser']) || $refresh) {
            $this->detectBrowser($this->userAgent);
        }

        return $this->browserVersion;
    }

    /**
     * getLanguages
     *
     * @param bool $refresh
     *
     * @return  array
     */
    public function getLanguages($refresh = false)
    {
        if (empty($this->detection['acceptLanguage']) || $refresh) {
            $this->detectLanguage($this->acceptLanguage);
        }

        return $this->languages;
    }

    /**
     * getEncodings
     *
     * @param bool $refresh
     *
     * @return  array
     */
    public function getEncodings($refresh = false)
    {
        if (empty($this->detection['acceptEncoding']) || $refresh) {
            $this->detectEncoding($this->acceptEncoding);
        }

        return $this->encodings;
    }

    /**
     * getUserAgent
     *
     * @return  string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * setUserAgent
     *
     * @param   string $userAgent
     *
     * @return  Browser  Return self to support chaining.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * getRobot
     *
     * @param bool $refresh
     *
     * @return  boolean
     */
    public function isRobot($refresh = false)
    {
        if (empty($this->detection['robot']) || $refresh) {
            $this->detectRobot($this->userAgent);
        }

        return $this->robot;
    }

    /**
     * Determine if we are using a secure (SSL) connection.
     *
     * @return  boolean  True if using SSL, false if not.
     *
     * @since   2.0
     */
    public function isSSLConnection()
    {
        return (!empty($this->server['HTTPS']) && strtolower($this->server['HTTPS']) !== 'off');
    }

    /**
     * getRemoteIP
     *
     * @see https://www.phpini.com/php/php-get-real-ip
     *
     * @return  string
     *
     * @since  3.5.3
     */
    public function getRemoteIP(): string
    {
        $server = $this->server;

        if (!empty($server['HTTP_CLIENT_IP'])) {
            return $server['HTTP_CLIENT_IP'];
        }

        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            return $server['HTTP_X_FORWARDED_FOR'];
        }

        return $server['REMOTE_ADDR'];
    }
}
