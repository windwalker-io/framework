# Transaction Command

## Start Transaction

``` php
$tran = $this->db->getTransaction()->start();

try
{
    $this->db->setQuery($sql1)->execute();

    $this->db->setQuery($sql2)->execute();

    $this->db->setQuery($sql3)->execute();
}
catch (\Exception)
{
    $tran->rollback();
}

$tran->commit();
```
