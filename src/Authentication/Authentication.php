<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Authentication;

use Windwalker\Authentication\Method\MethodInterface;

/**
 * The Authentication class.
 *
 * @since  2.0
 */
class Authentication implements AuthenticationInterface
{
    const SUCCESS = 'SUCCESS';

    const INVALID_CREDENTIAL = 'INVALID_CREDENTIAL';

    const EMPTY_CREDENTIAL = 'EMPTY_CREDENTIAL';

    const USER_NOT_FOUND = 'USER_NOT_FOUND';

    const INVALID_USERNAME = 'INVALID_USERNAME';

    const INVALID_PASSWORD = 'INVALID_PASSWORD';

    /**
     * Property results.
     *
     * @var  integer[]
     */
    protected $results = [];

    /**
     * Property methods.
     *
     * @var  MethodInterface[]
     */
    protected $methods = [];

    /**
     * Property credential.
     *
     * @var Credential
     */
    protected $credential;

    /**
     * Authentication constructor.
     *
     * @param Method\MethodInterface[] $methods
     */
    public function __construct(array $methods = [])
    {
        $this->methods = $methods;
    }

    /**
     * authenticate
     *
     * @param Credential $credential
     *
     * @return  bool|Credential
     */
    public function authenticate(Credential $credential)
    {
        $this->results = [];

        foreach ($this->methods AS $name => $method) {
            $result = $method->authenticate($credential);
            $status = $method->getStatus();

            $this->results[$name] = $status;
            $this->credential = $credential;

            if ($result === true && $status === static::SUCCESS) {
                $credential['_authenticated_method'] = $name;

                return true;
            }
        }

        return false;
    }

    /**
     * addMethod
     *
     * @param string          $name
     * @param MethodInterface $method
     *
     * @return  static
     */
    public function addMethod($name, MethodInterface $method)
    {
        $this->methods[$name] = $method;

        return $this;
    }

    /**
     * getMethod
     *
     * @param string $name
     *
     * @return  MethodInterface
     */
    public function getMethod($name)
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }

        return null;
    }

    /**
     * removeMethod
     *
     * @param string $name
     *
     * @return  $this
     */
    public function removeMethod($name)
    {
        if (isset($this->methods[$name])) {
            unset($this->methods[$name]);
        }

        return $this;
    }

    /**
     * Method to get property Results
     *
     * @return  integer[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Method to get property Credential
     *
     * @return  Credential
     */
    public function getCredential()
    {
        return $this->credential;
    }
}
