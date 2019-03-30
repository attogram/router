<?php

use Attogram\Router\Tests\WebTests;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once __DIR__ . '/WebTests.php';

new WebTests([
    [
        'route' => '/',
        'control' => 'top',
        'test' => [
            '/',
            '/?alpha=beta&gamma=omega',
        ],
    ],
    [
        'route' => '/foo/',
        'control' => 'with-slash',
        'test' => [
            '/foo/',
            '/foo/?alpha=beta&gamma=omega',
        ],
    ],
    [
        'route' => '/foo',
        'control' => 'no-slash',
        'test' => [
            '/foo',
            '/foo?alpha=beta&gamma=omega',
        ],
    ],
    [
        'route' => '/exact/',
        'control' => 'exact-route',
        'test' => ['/exact'],
    ],
    [
        'route' => '/var/?/',
        'control' => '1-variable-route',
        'test' => ['/var/Foo/'],
    ],
    [
        'route' => '/var/?/?/',
        'control' => '2-variables-route',
        'test' => ['/var/Foo/Bar/'],
    ],
    [
        'route' => '/var/?/?/?/',
        'control' => '3-variables-route',
        'test' => ['/var/Foo/Bar/Alpha/'],
    ],
    [
        'route' => '/var/?/?/?/?/',
        'control' => '4-variables-route',
        'test' => ['/var/Foo/Bar/Alpha/Omega/'],
    ],
    [
        'route' => '/string/',
        'control' => 'string_control',
        'test' => ['/string/'],
    ],
    [
        'route' => '/int/',
        'control' => 123456789,
        'test' => ['/int/'],
    ],
    [
        'route' => '/float/',
        'control' => 3.141592653,
        'test' => ['/float/'],
    ],
    [
        'route' => '/bool/',
        'control' => true,
        'test' => ['/bool/'],
    ],
    [
        'route' => '/array/',
        'control' => ['hello' => 'world'],
        'test' => ['/array/'],
    ],
    [
        'route' => '/object/',
        'control' => new stdClass(),
        'test' => ['/object/'],
    ],
    [
        'route' => '/closure/',
        'control' =>
function () {
    return 'hello world';
},
        'test' => ['/closure/'],
    ],
]);
