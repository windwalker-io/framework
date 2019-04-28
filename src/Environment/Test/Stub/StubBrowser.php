<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Environment\Test\Stub;

use Windwalker\Environment\Browser\Browser;

/**
 * The StubClient class.
 *
 * @since  2.0
 */
class StubBrowser extends Browser
{
    /**
     * Allows public access to protected method.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function detectBrowser($userAgent)
    {
        return parent::detectBrowser($userAgent);
    }

    /**
     * Allows public access to protected method.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function detectEngine($userAgent)
    {
        return parent::detectEngine($userAgent);
    }

    /**
     * Allows public access to protected method.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function detectDevice($userAgent)
    {
        return parent::detectDevice($userAgent);
    }

    /**
     * Allows public access to protected method.
     *
     * @param   string $acceptEncoding The accept encoding string to parse.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function detectEncoding($acceptEncoding)
    {
        return parent::detectEncoding($acceptEncoding);
    }

    /**
     * Allows public access to protected method.
     *
     * @param   string $acceptLanguage The accept language string to parse.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function detectLanguage($acceptLanguage)
    {
        return parent::detectLanguage($acceptLanguage);
    }

    /**
     * Allows public access to protected method.
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function detectRobot($userAgent)
    {
        return parent::detectRobot($userAgent);
    }

    /**
     * Method for inspecting protected variables.
     *
     * @param   string $name The name of the property.
     *
     * @return  mixed  The value of the class variable.
     *
     * @throws \Exception
     * @since   2.0
     */
    public function getProperty($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new \Exception('Undefined or private property: ' . __CLASS__ . '::' . $name);
        }
    }

    /**
     * loadClientInformation()
     *
     * @param   string $userAgent The user-agent string to parse.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadClientInformation($userAgent = null)
    {
        return parent::loadClientInformation($userAgent);
    }

    /**
     * fetchConfigurationData()
     *
     * @return  void
     *
     * @since   2.0
     */
    public function fetchConfigurationData()
    {
        return parent::fetchConfigurationData();
    }

    /**
     * loadSystemURIs()
     *
     * @param   string $ua The user-agent string to parse.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function testHelperClient($ua)
    {
        $_SERVER['HTTP_USER_AGENT'] = $ua;

        $this->detectClientInformation();

        return $this->config->get('client');
    }
}
