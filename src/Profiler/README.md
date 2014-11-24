# Windwalker Profiler

Windwalker Profiler can help us profiler some process information for debug.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/profiler": "~2.0"
    }
}
```

## Create A Profiler And Mark A Point

``` php
use Windwalker\Profiler\Profiler;

$profiler = new Profiler;

$profiler->mark('StartRender');

// Execute some code...

$profiler->mark('AfterRender');

// Execute some code...

$profiler->mark('End');
```

Now your can get the elapsed time between two points:

``` php
$profiler->getTimeBetween('StartRender', 'AfterRender');
```

Or get memory amount between two points:

``` php
// Return memory bytes
$profiler->getMemoryBetween('StartRender', 'AfterRender');
```

## Output Result

``` php
echo $profiler->render();
```
``` php
Notes 0.000 seconds (+0.000); 0.00 MB (+0.000) - StartRender
Notes 1.000 seconds (+1.000); 3.00 MB (+3.000) - AfterRender
Notes 1.813 seconds (+0.813); 6.24 MB (+3.240) - End
```

## Benchmark

Benchmark is a convenience object to test two or more tasks executing time.

``` php
use Windwalker\Profiler\Banchmark;

$benchmark = new Benchmark;

$benchmark->addTask('task1', function()
{
    md5(uniqid());
})
->addTask('task2', function()
{
    sha1(uniqid());
});

$benchmark->execute(10000);

echo $benchmark->render();
```

The output

```
task1 => 0.204897 s
task2 => 0.205108 s
```

Use Other format

``` php
$benchmark->setTimeFormat(Benchmark::MILLI_SECOND)->execute(10000);

echo $benchmark->render();

/* Result
task1 => 187.489986 ms
task2 => 207.049847 ms
*/
```

``` php
$benchmark->setTimeFormat(Benchmark::MICRO_SECOND)->execute(10000);

echo $benchmark->render();

/* Result
task1 => 198050.9758 μs
task2 => 206343.173981 μs
*/
```

### Custom Render Handler

``` php
$benchmark->setRenderOneHandler(function($name, $result, $round, $format)
{
    return $name . ' : ' . round($result, $round);
});

$benchmark->render();
```
