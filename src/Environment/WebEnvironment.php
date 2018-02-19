<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment;

use Windwalker\Environment\Browser\Browser;

/**
 * The WebEnvironment class.
 *
 * @since  2.0
 */
class WebEnvironment extends Environment
{
    /**
     * Property client.
     *
     * @var  Browser
     */
    public $browser;

    /**
     * create
     *
     * @param array|null $server
     *
     * @return  static
     */
    public static function create(array $server = null)
    {
        $server = $server ?: $_SERVER;
        $agent  = isset($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : null;

        return new static(new Browser($agent, $server), new Platform($server));
    }

    /**
     * Class init.
     *
     * @param Browser  $browser
     * @param Platform $platform
     */
    public function __construct(Browser $browser = null, Platform $platform = null)
    {
        $this->browser = $browser ?: new Browser;

        parent::__construct($platform);
    }

    /**
     * Method to get property Browser
     *
     * @return  Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Method to set property Browser
     *
     * @param   Browser $browser
     *
     * @return  static  Return self to support chaining.
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;

        return $this;
    }
}
