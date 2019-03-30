<?php
/**
 * Attogram Router
 * Test Page
 */
declare(strict_types = 1);

use Attogram\Router\Router;

require_once('../vendor/autoload.php');

session_start();

if (isset($_GET['forceSlash'])) {
    $_SESSION['forceSlash'] = $_GET['forceSlash'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . str_replace('index.php', '', $_SERVER['PHP_SELF']));
    return;
}

$tests = [
    [
        'route' => '/',
        'control' => 'top',
        'test' => [
            '/',
            '/?foo=bar&alpha=beta',
        ],
    ],
    [
        'route' => '/foo/',
        'control' => 'with-slash',
        'test' => [
            '/foo/',
            '/foo/?foo=bar&alpha=beta',
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
        'test' => [
            '/exact',
            '/exact/',
            '/exact/?alpha=beta&gamma=omega',
        ],
    ],
    [
        'route' => '/var/?/',
        'control' => '1-variable-route',
        'test' => [
            '/var/Foo',
            '/var/Foo/',
            '/var/Foo?alpha=beta&gamma=omega',
            '/var/Foo/?alpha=beta&gamma=omega',
        ],
    ],
    [
        'route' => '/var/?/?/',
        'control' => '2-variables-route',
        'test' => [
            '/var/Foo/Bar/',
            '/var/Foo/Bar?alpha=beta&gamma=omega',
        ],
    ],
    [
        'route' => '/var/?/?/?/',
        'control' => '3-variables-route',
        'test' => [
            '/var/Foo/Bar/Alpha/',
        ],
    ],
    [
        'route' => '/var/?/?/?/?/',
        'control' => '4-variables-route',
        'test' => [
            '/var/Foo/Bar/Alpha/Omega/',
        ],
    ],
];

// Setup Router
$router = new Router();
if (isset($_SESSION['forceSlash'])) {
    $router->setForceSlash((bool) $_SESSION['forceSlash']);
}
// Setup Test Routes
foreach ($tests as $test) {
    $router->allow($test['route'], $test['control']);
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Attogram Router v<?= $router::VERSION ?> Test Page</title>
<style>
    body { background-color:white; color:black; font-family:monospace; margin:0; }
    a:hover, a:active { background-color:yellow; }
    h1 { display:inline; }
    li { line-height:150%; }
    table, tr, th, td { border:1px solid black; border-collapse:collapse; padding:4px; margin:10px; }
    p { margin: 10px; }
    .hdr { background-color:#2ed5ee; color: black; padding:10px; }
    .hdr a { color: #000000; }
    .empty { background-color: #fff0f0; color:black; font-style:italic; }
    .full { background-color: #d2ffda; color:black; font-weight: bold; }
</style>
</head>
<body>
<?php
pageHeader();
testResults();
testList();
pageHeader();
?>
</body>
</html>
<?php

function pageHeader()
{
    $name = 'attogram/router';
    print '<div class="hdr">'
        . '<h1>' . $name . ' <small>v' . Router::VERSION . '</small></h1>'
        . '<br />@ '
        . ' <a target="_blank" href="https://github.com/' . $name . '">Github</a>'
        . ' - <a target="_blank" href="https://packagist.org/packages/' . $name . '">Packagist</a>'
        . ' - <a target="_blank" href="https://codeclimate.com/github/' . $name . '">Codeclimate</a>'
        . ' - <a target="_blank" href="https://travis-ci.org/' . $name . '">Travis-CI</a>'
        . '</div>';
}

function testList()
{
    global $router, $tests;
    print '<table>'
        . '<tr><th>control</th><th>route</th><th>tests</th></tr>'
        . '<tr>'
            . '<td>-</td><td>-</td>'
            . '<td><a href="' . $router->getUriBase() . '">' . $router->getUriBase() . '</a></td>'
        . '</tr>'
        ;
    foreach ($tests as $test) {
        foreach ($test['test'] as $link) {
            print '<tr>'
                . '<td>' . $test['control'] . '</td>'
                . '<td>' . $test['route'] . '</td>'
                . '<td><a href="' . $router->getUriBase() . $link . '">'
                . $router->getUriBase() . $link . '</a></td>'
                . '</tr>';
        }
    }
    print '</table>';
}

function testResults()
{
    global $router;

    $empty = '<span class="empty">empty</span>';

    $match = $router->match();
    switch ($match) {
        case '':
            $matchResults = $empty;
            break;
        default:
            $matchResults = '<span class="full">' .$match . '</span>';
    }

    $varsResults = $empty;
    if (!empty($router->getVars())) {
        $varsResults = '<span class="full">';
        foreach ($router->getVars() as $name => $value) {
            $varsResults .= "$name = $value<br />";
        }
        $varsResults .= '</span>';
    }

    $getResults = $empty;
    if (!empty($_GET)) {
        $getResults = '<span class="full">';
        foreach ($_GET as $name => $value) {
            $getResults .= htmlentities($name) . ' = ' . htmlentities($value) . '<br />';
        }
        $getResults .= '</span>';
    }

    print '<table>'
        . '<tr><th colspan="2">Test Results @ ' . gmdate('Y-m-d H:i:s') . ' UTC</th></tr>'
        . '<tr><td>$router->match()</td><td>' . $matchResults . '</td></tr>'
        . '<tr><td>$router->getUriBase()</td><td>' . $router->getUriBase() . '</td></tr>'
        . '<tr><td>$router->getUriRelative()</td><td>' . $router->getUriRelative() . '</td></tr>'
        . '<tr><td>$router->geVars()</td><td>' . $varsResults . '</td></tr>'
        . '<tr><td>$_GET</td><td>' . $getResults . '</td></tr>'
        . '<tr><td>forceSlash</td><td>'
        . ((isset($_SESSION['forceSlash']) && $_SESSION['forceSlash']) ? 'true' : 'false')
        . '</td></tr>'
        . '</table>'
        . '</div>'
        . '<p>[ Router setup: '
        . '<a href="?forceSlash=1">Force Slash</a> - '
        . '<a href="?forceSlash=0">Do Not Force Slash</a>'
        . ' ]</p>';
}
