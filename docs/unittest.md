# Unit Test

## Generate Test Scaffold

### `gen-test.php` Usage

```
$ php bin/gen-test.php <Package> <Folder and Class Name> [Test File Position]
```

### Generate In Test Folder

Generate `Windwalker\Query\QueryElement` to `src/Query/Test/QueryElementTest.php`

``` bash
$ php bin/gen-test.php Query QueryElement
```

Generate `Windwalker\Query\Mysql\MysqlQuery` to `src/Query/Test/MysqlQueryTest.php`

``` bash
$ php bin/gen-test.php Query Mysql/MysqlQuery
```

### Custom Folder

Generate `Windwalker\Query\Mysql\MysqlQuery` to `src/Query/Test/Mysql/MysqlQueryTest.php`

``` bash
$ php bin/gen-test.php Query Mysql/MysqlQuery Mysql/MysqlQueryTest
```
