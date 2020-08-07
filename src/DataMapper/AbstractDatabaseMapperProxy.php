<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Data\Data;
use Windwalker\Data\DataSet;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Event\DispatcherInterface;
use Windwalker\Event\Event;
use Windwalker\String\StringInflector;

/**
 * The AbstractDataMapperProxy class.
 *
 * @see    DataMapper
 * @see    AbstractDataMapper
 *
 * phpcs:disable
 *
 * @method  static DataSet|Data[]  find($conditions = [], $order = null, $start = null, $limit = null, $key = null)
 * @method  static DataSet|Data[]  findAll($order = null, $start = null, $limit = null, $key = null)
 * @method  static \Iterator       findIterate($conditions = [], $order = null, $start = null, $limit = null, $key = null)
 * @method  static Data            findOne($conditions = [], $order = null)
 * @method  static array           findColumn($column, $conditions = [], $order = null, $start = null, $limit = null, $key = null)
 * @method  static mixed           findResult($conditions = [], $order = null)
 * @method  static DataSet|Data[]  create($dataset)
 * @method  static Data            createOne($data)
 * @method  static DataSet|Data[]|mixed  copy($conditions = [], array $initData = [], bool $removeKey = false)
 * @method  static Data|mixed      copyOne($conditions = [], array $initData = [], bool $removeKey = false)
 * @method  static DataSet|Data[]  update($dataset, $condFields = null, $updateNulls = false)
 * @method  static Data            updateOne($data, $condFields = null, $updateNulls = false)
 * @method  static boolean         updateBatch($data, $conditions = [])
 * @method  static DataSet|Data[]  flush($dataset, $conditions = [])
 * @method  static Data[]|array[]  sync($dataset, $conditions = [], ?array $compareKeys = null)
 * @method  static DataSet|Data[]  save($dataset, $condFields = null, $updateNulls = false)
 * @method  static Data            saveOne($data, $condFields = null, $updateNulls = false)
 * @method  static Data|mixed      findOneOrCreate($conditions, $initData = null, bool $mergeConditions = true)
 * @method  static Data|mixed      updateOneOrCreate($data, $initData = null, ?array $condFields = null, bool $updateNulls = false)
 * @method  static boolean         delete($conditions)
 * @method  static boolean         useTransaction($yn = null)
 * @method  static Event                triggerEvent($event, $args = [])
 * @method  static DispatcherInterface  getDispatcher()
 * @method  static AbstractDataMapper   setDispatcher(DispatcherInterface $dispatcher)
 * @method  static DataMapper  addTable($alias, $table, $condition = null, $joinType = 'LEFT', $prefix = null)
 * @method  static DataMapper  removeTable($alias)
 * @method  static DataMapper  call($columns)
 * @method  static DataMapper  group($columns)
 * @method  static DataMapper  having($conditions, ...$args)
 * @method  static DataMapper  innerJoin($table, $condition = [])
 * @method  static DataMapper  join($type, $alias, $table, $condition = null, $prefix = null)
 * @method  static DataMapper  leftJoin($alias, $table, $condition = null, $prefix = null)
 * @method  static DataMapper  order($columns)
 * @method  static DataMapper  limit($limit = null, $offset = null)
 * @method  static DataMapper  outerJoin($alias, $table, $condition = null, $prefix = null)
 * @method  static DataMapper  rightJoin($alias, $table, $condition = null, $prefix = null)
 * @method  static DataMapper  select($columns)
 * @method  static DataMapper  where($conditions, ...$args)
 * @method  static DataMapper  whereIn($column, array $values)
 * @method  static DataMapper  whereNotIn($column, array $values)
 * @method  static DataMapper  orWhere($conditions)
 * @method  static DataMapper  bind($key = null, $value = null, $dataType = \PDO::PARAM_STR, $length = 0, $driverOptions = [])
 * @method  static DataMapper  forUpdate()
 * @method  static DataMapper  suffix(string $string)
 * @method  static AbstractDatabaseDriver  getDb()
 * @method  static DataMapper  setDataClass(string $class)
 * @method  static DataMapper  setDatasetClass(string $class)
 * @method  static DataMapper  pipe(callable $handler)
 * @method  static DataMapper  handleQuery(callable $handler)
 *
 * phpcs:enable
 *
 * @since  3.0
 */
class AbstractDatabaseMapperProxy
{
    /**
     * Property table.
     *
     * @var  string
     */
    protected static $table;

    /**
     * Property alias.
     *
     * @var  string
     */
    protected static $alias;

    /**
     * Property keys.
     *
     * @var  array|string
     */
    protected static $keys;

    /**
     * Property dataClass.
     *
     * @var  string
     */
    protected static $dataClass;

    /**
     * Property dataSetClass.
     *
     * @var  string
     */
    protected static $dataSetClass;

    /**
     * Property instances.
     *
     * @var  DataMapper[]
     */
    protected static $instances = [];

    /**
     * is triggered when invoking inaccessible methods in an object context.
     *
     * @param $name      string
     * @param $arguments array
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $instance = static::getInstance();

        return call_user_func_array([$instance, $name], $arguments);
    }

    /**
     * is triggered when invoking inaccessible methods in a static context.
     *
     * @param $name      string
     * @param $arguments array
     *
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = static::getInstance();

        return call_user_func_array([$instance, $name], $arguments);
    }

    /**
     * initialise
     *
     * @param DatabaseMapperInterface $mapper
     *
     * @return  void
     */
    protected static function init(DatabaseMapperInterface $mapper)
    {
    }

    /**
     * getInstance
     *
     * @param   string $table
     *
     * @return  DataMapper
     * @throws \Exception
     */
    public static function getInstance($table = null)
    {
        $table = $table ?: static::$table;

        return static::createDataMapper($table);
    }

    /**
     * createDataMapper
     *
     * @param   string                 $table Table name.
     * @param   string|array           $keys  Primary key, default will be `id`.
     * @param   AbstractDatabaseDriver $db    Database adapter.
     *
     * @return DataMapper
     * @throws \Exception
     */
    public static function createDataMapper($table = null, $keys = null, $db = null)
    {
        $table = $table ?: static::$table;
        $keys = $keys ?: static::$keys;

        $mapper = new DataMapper($table, $keys, $db);

        if (static::$alias !== null) {
            $mapper->alias(static::$alias);
        } else {
            $mapper->alias(StringInflector::getInstance()->toSingular($table));
        }

        if (static::$dataClass) {
            $mapper->setDataClass(static::$dataClass);
        }

        if (static::$dataSetClass) {
            $mapper->setDatasetClass(static::$dataSetClass);
        }

        $mapper->getDispatcher()->addListener(new static());

        static::init($mapper);

        return $mapper;
    }

    /**
     * setDataMapper
     *
     * @param string     $table
     * @param DataMapper $mapper
     *
     * @return  void
     */
    public static function setDataMapper($table, DataMapper $mapper)
    {
        static::$instances[$table] = $mapper;
    }

    /**
     * reset
     *
     * @return  void
     */
    public static function reset()
    {
        static::$instances = [];
    }
}
