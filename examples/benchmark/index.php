<?php
declare(strict_types = 1);

namespace Attogram\Router\Examples;

use Attogram\Router\Router;

require '../../vendor/autoload.php';

$runs = 5;
$loops = 1000;
$precision = 8;
$results = [];

print '<p>Benchmark @ ' . date('Y-m-d H:i:s') . '</p>';

for ($run = 0; $run < $runs; $run++) {
    $result = runBench($loops);
    $results[] = $result;
    print "<p>run#$run loops:$loops result: " . round($result, $precision) . '</p>';
}

print "<h1>runs:$runs average: <b>"
    . round(array_sum($results) / count($results), $precision)
    . '</b></h1>';

function runBench(int $loops)
{
    $timer = microtime(true);
    for ($loop = 0; $loop < $loops; $loop++) {
        testSimple();
    }
    return microtime(true) - $timer;
}

function testSimple()
{
    $router = new Router;
    $router->allow('/', 'zero');
    $router->allow('/one', 'one');
    $router->allow('/one/two', 'two');
    $router->allow('/one/two/three', 'three');
    $router->allow('/one/two/three/four', 'four');
    $router->allow('/one/two/three/four/five', 'five');
    $router->match();
}
