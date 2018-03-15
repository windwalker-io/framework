<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue;

use Windwalker\Queue\Job\JobInterface;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\OptionAccessTrait;

/**
 * The QueueMessage class.
 *
 * @since  3.2
 */
class QueueMessage implements \JsonSerializable
{
    use OptionAccessTrait;

    /**
     * Property id.
     *
     * @var  int|string
     */
    protected $id;

    /**
     * Property attempts.
     *
     * @var  int
     */
    protected $attempts = 0;

    /**
     * Message body from remote server.
     *
     * @var  array
     */
    protected $body = [];

    /**
     * Message body from remote server.
     *
     * @var  string
     */
    protected $rawBody = [];

    /**
     * Property delay.
     *
     * @var  int
     */
    protected $delay = 0;

    /**
     * Property deleted.
     *
     * @var  bool
     */
    protected $deleted = false;

    /**
     * QueueMessage constructor.
     *
     * @param JobInterface $job
     * @param array        $data
     * @param int          $delay
     * @param array        $options
     */
    public function __construct(JobInterface $job = null, array $data = [], $delay = 0, array $options = [])
    {
        if ($job !== null) {
            $this->setJob($job);
        }

        if ($data) {
            $this->setData($data);
        }

        $this->setDelay($delay);
        $this->setOptions($options);
    }

    /**
     * get
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return  mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->body[$name])) {
            return $this->body[$name];
        }

        return $default;
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return  static
     */
    public function set($name, $value)
    {
        $this->body[$name] = $value;

        return $this;
    }

    /**
     * Method to get property Id
     *
     * @return  int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Method to set property id
     *
     * @param   int|string $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to get property Attempts
     *
     * @return  int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Method to set property attempts
     *
     * @param   int $attempts
     *
     * @return  static  Return self to support chaining.
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Method to get property Job
     *
     * @return  string
     */
    public function getJob()
    {
        return Arr::get($this->body, 'job', '');
    }

    /**
     * Method to set property job
     *
     * @param   string $job
     *
     * @return  static  Return self to support chaining.
     */
    public function setJob($job)
    {
        $this->body['job'] = $job;

        return $this;
    }

    /**
     * Method to get property Data
     *
     * @return  array
     */
    public function getData()
    {
        return Arr::get($this->body, 'data', []);
    }

    /**
     * Method to set property data
     *
     * @param   array $data
     *
     * @return  static  Return self to support chaining.
     */
    public function setData(array $data)
    {
        $this->body['data'] = $data;

        return $this;
    }

    /**
     * Method to get property Queue
     *
     * @return  string
     */
    public function getQueueName()
    {
        return Arr::get($this->body, 'queue', '');
    }

    /**
     * Method to set property queue
     *
     * @param   string $queue
     *
     * @return  static  Return self to support chaining.
     */
    public function setQueueName($queue)
    {
        $this->body['queue'] = $queue;

        return $this;
    }

    /**
     * Method to get property Body
     *
     * @return  array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Method to set property body
     *
     * @param   array $body
     *
     * @return  static  Return self to support chaining.
     */
    public function setBody($body)
    {
        $this->body = (array) $body;

        return $this;
    }

    /**
     * Method to get property RawData
     *
     * @return  string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * Method to set property rawData
     *
     * @param   string $rawBody
     *
     * @return  static  Return self to support chaining.
     */
    public function setRawBody($rawBody)
    {
        $this->rawBody = $rawBody;

        return $this;
    }

    /**
     * Method to get property Name
     *
     * @return  string
     */
    public function getName()
    {
        return Arr::get($this->body, 'name', '');
    }

    /**
     * Method to set property name
     *
     * @param   string $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName($name)
    {
        $this->body['name'] = $name;

        return $this;
    }

    /**
     * Method to get property Delay
     *
     * @return  int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Method to set property delay
     *
     * @param   int $delay
     *
     * @return  static  Return self to support chaining.
     */
    public function setDelay($delay)
    {
        $this->delay = (int) $delay;

        return $this;
    }

    /**
     * isDeleted
     *
     * @param bool $bool
     *
     * @return  bool|static
     */
    public function isDeleted($bool = null)
    {
        if ($bool === null) {
            return $this->deleted;
        }

        $this->deleted = (bool) $bool;

        return $this;
    }

    /**
     * jsonSerialize
     *
     * @return  array
     *
     * @throws \InvalidArgumentException
     */
    public function jsonSerialize()
    {
        return $this->body;
    }
}
