<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Record;

use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\DataMapper\AbstractDataMapper;
use Windwalker\DataMapper\DataMapper;
use Windwalker\DataMapper\Entity\Entity;
use Windwalker\Event\Dispatcher;
use Windwalker\Event\DispatcherAwareInterface;
use Windwalker\Event\DispatcherInterface;
use Windwalker\Event\Event;
use Windwalker\Event\EventInterface;
use Windwalker\Event\ListenerMapper;
use Windwalker\Record\Exception\NoResultException;

/**
 * Class Record
 *
 * @since 2.0
 */
class Record extends Entity
{
    const UPDATE_NULLS = true;

    const LOAD_DEFAULT = true;

    /**
     * Name of the database table to model.
     *
     * @var    string
     * @since  2.0
     */
    protected $table = '';

    /**
     * Property data.
     *
     * @var  array
     */
    protected $data = [];

    /**
     * Name of the primary key fields in the table.
     *
     * @var    array
     * @since  2.0
     */
    protected $keys = [];

    /**
     * Indicates that the primary keys autoincrement.
     *
     * @var    boolean
     * @since  2.0
     */
    protected $autoIncrement = null;

    /**
     * DatabaseDriver object.
     *
     * @var    AbstractDatabaseDriver
     * @since  2.0
     */
    protected $db;

    /**
     * Property dispatcher.
     *
     * @var  Dispatcher
     */
    protected $dispatcher;

    /**
     * Property fieldsCache.
     *
     * @var  array
     */
    protected static $fieldsCache = [];

    /**
     * Property mapper.
     *
     * @var  AbstractDataMapper
     */
    protected $mapper;

    /**
     * Object constructor to set table and key fields.  In most cases this will
     * be overridden by child classes to explicitly set the table and key fields
     * for a particular database table.
     *
     * @param   string             $table   Name of the table to model.
     * @param   mixed              $keys    Name of the primary key field in the table or array of field names that
     *                                      compose the primary key.
     * @param   AbstractDataMapper $mapper  The DataMapper Adapter to access database.
     *
     * @throws \Exception
     * @since   2.0
     */
    public function __construct($table = null, $keys = 'id', AbstractDataMapper $mapper = null)
    {
        // Set internal variables.
        $this->table = $this->table ?: $table;

        if (!$this->keys) {
            $this->keys = $keys;
        }

        $this->keys = (array) $this->keys;

        if ($this->autoIncrement === null) {
            $this->autoIncrement = count($this->keys) === 1;
        }

        $this->mapper = $mapper ?: new DataMapper($this->table, $this->keys);
        $this->db     = $this->mapper->getDb();

        // Initialise the table properties.
        $this->reset(false);

        if (!$this->table) {
            throw new \InvalidArgumentException('Table name should not empty.');
        }
    }

    /**
     * Magic getter to get a table field.
     *
     * @param   string $key     The key name.
     * @param   null   $default The default value.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function get($key, $default = null)
    {
        return parent::get($key, $default);
    }

    /**
     * Method to provide a shortcut to binding, checking and storing a AbstractTable
     * instance to the database table.  The method will check a row in once the
     * data has been stored and if an ordering filter is present will attempt to
     * reorder the table rows based on the filter.  The ordering filter is an instance
     * property name.  The rows that will be reordered are those whose value matches
     * the AbstractTable instance for the property specified.
     *
     * @param   mixed   $src         An associative array or object to bind to the AbstractTable instance.
     * @param   boolean $updateNulls True to update fields even if they are null.
     *
     * @return  static  Method allows chaining
     *
     * @throws \RuntimeException
     *
     * @since   2.0
     */
    public function save($src, $updateNulls = false)
    {
        return $this
            // Attempt to bind the source to the instance.
            ->bind($src)
            // Run any sanity checks on the instance and verify that it is ready for storage.
            ->validate()
            // Attempt to store the properties to the database table.
            ->store($updateNulls);
    }

    /**
     * Method to bind an associative array or object to the AbstractTable instance.  This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed $src          An associative array or object to bind to the AbstractTable instance.
     * @param   bool  $replaceNulls Replace NULL value.
     *
     * @return static Method allows chaining
     *
     * @throws \InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($src, $replaceNulls = false)
    {
        // If the source value is not an array or object return false.
        if (!is_object($src) && !is_array($src)) {
            throw new \InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
        }

        if ($src instanceof \Traversable) {
            $src = iterator_to_array($src);
        }

        // If the source value is an object, get its accessible properties.
        if (is_object($src)) {
            $src = get_object_vars($src);
        }

        $fields = $this->getFields();

        // Event
        $this->triggerEvent(
            'onBefore' . ucfirst(__FUNCTION__), [
            'src' => &$src,
            'fields' => $fields,
            'replaceNulls' => &$replaceNulls,
        ]
        );

        // Bind the source value, excluding the ignored fields.
        foreach ($src as $k => $v) {
            if ($v === null && !$replaceNulls) {
                continue;
            }

            $this->$k = $v;
        }

        // Event
        $this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

        return $this;
    }

    /**
     * Method to load a row from the database by primary key and bind the fields
     * to the AbstractTable instance properties.
     *
     * @param   mixed   $keys    An optional primary key value to load the row by, or an array of fields to match.  If
     *                           not set the instance property value is used.
     * @param   boolean $reset   True to reset the default values before loading the new row.
     *
     * @return  static  Method allows chaining
     * @throws \Windwalker\Record\Exception\NoResultException
     *
     * @since   2.0
     * @throws  \RuntimeException
     * @throws  \UnexpectedValueException
     * @throws  \InvalidArgumentException
     */
    public function load($keys = null, $reset = true)
    {
        // Event
        $this->triggerEvent(
            'onBefore' . ucfirst(__FUNCTION__), [
            'conditions' => &$keys,
            'reset' => &$reset,
        ]
        );

        if ($reset) {
            $this->reset();
        }

        // If keys empty, use inner values as keys.
        if (empty($keys)) {
            $empty = true;
            $keys  = [];

            // If empty, use the value of the current key
            foreach ($this->keys as $key) {
                $empty      = $empty && empty($this->$key);
                $keys[$key] = $this->$key;
            }

            // If empty primary key there's is no need to load anything
            if ($empty) {
                return $this;
            }
        }

        $result = $this->getDataMapper()->findOne($keys);

        // Check that we have a result.
        if ($result->isNull()) {
            throw new NoResultException('No result.');
        }

        // Bind the object with the row and return.
        $row = $this->bind($result);

        // Event
        $this->triggerEvent(
            'onAfter' . ucfirst(__FUNCTION__), [
            'result' => &$row,
        ]
        );

        return $row;
    }

    /**
     * Method to delete a row from the database table by primary key value.
     *
     * @param   mixed $conditions An optional primary key value to delete.  If not set the instance property value is
     *                            used.
     *
     * @return  static  Method allows chaining
     *
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    public function delete($conditions = null)
    {
        if ($conditions !== null) {
            $dataset = $this->getDataMapper()->find($conditions);

            $record = clone $this;

            foreach ($dataset as $data) {
                $record->reset(false)
                    ->bind($data)
                    ->delete();
            }

            return $this;
        }

        $conditions = [];

        foreach ((array) $this->getKeyName(true) as $key) {
            $conditions[$key] = $this->$key;
        }

        // Event
        $this->triggerEvent(
            'onBefore' . ucfirst(__FUNCTION__), [
            'conditions' => &$conditions,
        ]
        );

        // If no primary key is given, return false.
        if ($conditions === []) {
            throw new \UnexpectedValueException('Null primary key not allowed.');
        }

        if (!$this->getDataMapper()->delete($conditions)) {
            throw new \RuntimeException('Delete fail with unknown reason.');
        }

        // Event
        $this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

        return $this;
    }

    /**
     * Method to perform sanity checks on the AbstractTable instance properties to ensure
     * they are safe to store in the database.  Child classes should override this
     * method to make sure the data they are storing in the database is safe and
     * as expected before storage.
     *
     * @return  static  Method allows chaining
     *
     * @since   2.0
     *
     * @throws  \RuntimeException
     */
    public function validate()
    {
        return $this;
    }

    /**
     * Method to store a row in the database from the AbstractTable instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * AbstractTable instance.
     *
     * @param   boolean $updateNulls True to update fields even if they are null.
     *
     * @return  static  Method allows chaining
     *
     * @since   2.0
     */
    public function store($updateNulls = false)
    {
        $new = !$this->{$this->getKeyName()};

        $action = $new ? 'Create' : 'Update';

        // @Event: Create / Update
        $this->triggerEvent(
            'onBefore' . $action, [
            'updateNulls' => &$updateNulls,
        ]
        );

        // @Event: Store
        $this->triggerEvent(
            'onBefore' . ucfirst(__FUNCTION__), [
            'updateNulls' => &$updateNulls,
        ]
        );

        // Do Action
        // If a primary key exists update the object, otherwise insert it.
        $this->getDataMapper()->saveOne($this, $this->getKeyName(true), $updateNulls);

        // @Event: Store
        $this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

        // @Event: Create / Update
        $this->triggerEvent(
            'onAfter' . $action, [
            'updateNulls' => &$updateNulls,
        ]
        );

        return $this;
    }

    /**
     * create
     *
     * @return  static
     *
     * @throws \InvalidArgumentException
     */
    public function create()
    {
        // Event
        $this->triggerEvent('onBefore' . ucfirst(__FUNCTION__), []);

        $this->triggerEvent('onBeforeStore');

        // Do Action
        $this->getDataMapper()->createOne($this);

        // Event
        $this->triggerEvent('onAfterStore');

        $this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

        return $this;
    }

    /**
     * update
     *
     * @param bool $updateNulls
     *
     * @return  static
     */
    public function update($updateNulls = false)
    {
        // Event
        $this->triggerEvent(
            'onBefore' . ucfirst(__FUNCTION__), [
            'updateNulls' => &$updateNulls,
        ]
        );

        $this->triggerEvent(
            'onBeforeStore', [
            'updateNulls' => &$updateNulls,
        ]
        );

        // Do Action
        $this->getDataMapper()->updateOne($this, $this->getKeyName(true), $updateNulls);

        // Event
        $this->triggerEvent(
            'onAfterStore', [
            'updateNulls' => &$updateNulls,
        ]
        );

        $this->triggerEvent('onAfter' . ucfirst(__FUNCTION__));

        return $this;
    }

    /**
     * Get the table name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param   string $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTableName($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * loadFields
     *
     * @param bool $reset
     *
     * @return \stdClass[]
     */
    public function getFields($reset = false)
    {
        if (!$this->table) {
            return $this->fields;
        }

        if ($this->fields === null || $reset) {
            foreach ($this->getDataMapper()->getFields() as $field) {
                $this->addField($field);
            }
        }

        return $this->fields;
    }

    /**
     * Method to get the primary key field name for the table.
     *
     * @param   boolean $multiple True to return all primary keys (as an array) or false to return just the first one
     *                            (as a string).
     *
     * @return  array|mixed  Array of primary key field names or string containing the first primary key field.
     *
     * @since   2.0
     */
    public function getKeyName($multiple = false)
    {
        if ($multiple) {
            return (array) $this->keys;
        }

        $keys = (array) $this->keys;

        return (string) $keys[0];
    }

    /**
     * Validate that the primary key has been set.
     *
     * @return  boolean  True if the primary key(s) have been set.
     *
     * @since   2.0
     */
    public function hasPrimaryKey()
    {
        if ($this->autoIncrement) {
            $empty = true;

            foreach ($this->keys as $key) {
                $empty = $empty && !$this->$key;
            }

            return !$empty;
        }

        $conditions = [];

        foreach ($this->getKeyName(true) as $key) {
            $conditions[$key] = $this->$key;
        }

        return $this->getDataMapper()->find($conditions);
    }

    /**
     * Check a field value exists in database or not, to keep a field unique.
     *
     * @param   string $field The field name to check.
     * @param   mixed  $value The value to check.
     *
     * @return bool
     *
     * @throws \Windwalker\Record\Exception\NoResultException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function valueExists($field, $value = null)
    {
        $record = new static($this->table, $this->keys, $this->mapper);

        if ($value === null) {
            $value = $this->$field;
        }

        try {
            $record->load([$field => $value]);
        } catch (NoResultException $e) {
            return false;
        }

        if ($record->$field != $value) {
            return false;
        }

        // check record keys same as self
        $same = [];

        foreach ($this->keys as $key) {
            $same[] = ($record->$key == $this->$key);
        }

        // Key not same, means same value exists in other record.
        if (in_array(false, $same, true)) {
            return true;
        }

        return false;
    }

    /**
     * triggerEvent
     *
     * @param   string|Event $event
     * @param   array        $args
     *
     * @return  EventInterface
     *
     * @since   2.1
     */
    public function triggerEvent($event, $args = [])
    {
        $dispatcher = $this->getDispatcher();

        if (!$dispatcher instanceof DispatcherInterface) {
            return null;
        }

        $args['record'] = $this;

        $event = $this->dispatcher->triggerEvent($event, $args);

        $innerListener = [$this, $event->getName()];

        if (!$event->isStopped() && is_callable($innerListener)) {
            call_user_func($innerListener, $event);
        }

        return $event;
    }

    /**
     * Method to get property Dispatcher
     *
     * @return  DispatcherInterface
     *
     * @since   2.1
     */
    public function getDispatcher()
    {
        if (!$this->dispatcher && class_exists(Dispatcher::class)) {
            $this->dispatcher = new Dispatcher();

            if (is_subclass_of($this, DispatcherAwareInterface::class)) {
                ListenerMapper::add($this);
            }
        }

        return $this->dispatcher;
    }

    /**
     * Method to set property dispatcher
     *
     * @param   DispatcherInterface $dispatcher
     *
     * @return  static  Return self to support chaining.
     *
     * @since   2.1
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Method to reset class properties to the defaults set in the class
     * definition. It will ignore the primary key as well as any private class
     * properties.
     *
     * @param bool $loadDefault
     *
     * @return  static
     *
     * @since   2.0
     */
    public function reset($loadDefault = true)
    {
        $this->data = [];

        // Get the default values for the class from the table.
        foreach ((array) $this->getFields() as $k => $v) {
            $this->data[$k] = $loadDefault ? $v->Default : null;
        }

        return $this;
    }

    /**
     * loadDefault
     *
     * @param bool $replace
     *
     * @return static
     */
    public function loadDefault($replace = false)
    {
        foreach ((array) $this->getFields() as $k => $v) {
            if ($replace || $this->data[$k] === null) {
                $this->data[$k] = $v->Default;
            }
        }

        return $this;
    }

    /**
     * Method to get property Mapper
     *
     * @return  AbstractDataMapper
     * @throws \Exception
     */
    public function getDataMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new DataMapper($this->table, $this->keys);
        }

        return $this->mapper;
    }

    /**
     * Method to set property mapper
     *
     * @param   AbstractDataMapper $mapper
     *
     * @return  static  Return self to support chaining.
     */
    public function setDataMapper($mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }
}
