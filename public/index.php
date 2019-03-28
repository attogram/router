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
        'control' => 'top-level',
        'test' => [
            '/',
            '/?foo=bar',
            '/?foo=bar&alpha=beta',
        ],
    ],
    [
        'route' => '/test',
        'control' => 'test-no-slash',
        'test' => [
            '/test',
            '/test?foo=bar&alpha=beta',
        ],
    ],
    [
        'route' => '/test/',
        'control' => 'test-slash',
        'test' => [
            '/test/',
            '/test/?foo=bar&alpha=beta',
        ],
    ],
    [
        'route' => '/var/?/',
        'control' => 'test-var-1',
        'test' => [
            '/var/Foo',
            '/var/Foo/',
            '/var/Foo?foo=bar&alpha=beta',
            '/var/Foo/?foo=bar&alpha=beta',
        ],
    ],
    [
        'route' => '/var/?/?/',
        'control' => 'test-var-2',
        'test' => [
            '/var/Foo/Bar',
            '/var/Foo/Bar/',
            '/var/Foo/Bar?foo=bar&alpha=beta',
            '/var/Foo/Bar/?foo=bar&alpha=beta',
        ],
    ],
    [
        'route' => '/var/?/?/?/',
        'control' => 'test-var-3',
        'test' => [
            '/var/Foo/Bar/Alpha/',
        ],
    ],
    [
        'route' => '/var/?/?/?/?/',
        'control' => 'test-var-4',
        'test' => [
            '/var/Foo/Bar/Alpha/Beta/',
        ],
    ],
];

$forceSlash = false;
if (isset($_SESSION['forceSlash'])) {
    $forceSlash = $_SESSION['forceSlash'];
}
$router = new Router($forceSlash);

foreach ($tests as $test) {
    $router->allow($test['route'], $test['control']);
}

$control  = $router->match();
$base     = $router->getUriBase();
$relative = $router->getUriRelative();
$full     = $base . $relative;
$vars     = $router->getVars();

$title = 'Attogram Router v' . $router::VERSION;

?><html lang="en"><head><title><?= $title; ?></title><style type="text/css">
body { background-color:lightgrey; color:black; font-family:monospace; }
a:hover, a:active { background-color:yellow; }
h1 { display:inline; }
li { line-height:150%; }
.pre { white-space:pre; line-height:150%; }
.box { font-weight:bold; padding:0 4px 0 4px; display:inline; }
.empty { background-color: #e6b3b6; color: #000000; }
.good { background-color: #a8e88a; color: #000000; }
.vars { display:block; }
</style></head><body>
<h1><?= $title; ?></h1>
<p class="pre">control  : <?php
    echo !empty($control)
        ? '<span class="box good">' . $control . '</span>'
        : '<span class="box empty">404</span>'; ?> &nbsp;
full      : <?= $full; ?> &nbsp;
base      : <?= $base; ?> &nbsp;
relative  : <?= $relative; ?> &nbsp;
forceSlash: <?= $forceSlash ? 'true' : 'false' ?> &nbsp; [<a href="?forceSlash=1">Force Slash</a> - <a href="?forceSlash=0">Do Not Force Slash</a>] &nbsp;
vars      : <?php
    if (!empty($vars)) {
        foreach ($vars as $name => $value) {
            print "$name=$value &nbsp; ";
        }
    } else {
        print '<span class="box empty">null</span>';
    } ?> &nbsp;
_GET      : <?php
    if (!empty($_GET)) {
        foreach ($_GET as $name => $value) {
            print htmlentities($name) . '=' . htmlentities($value) . ' &nbsp; ';
        }
    } else {
        print '<span class="box empty">null</span>';
    }
 ?>
</p>
<hr />
<ol>
    <li>[base] <a href="<?= $base; ?>"><?= $base; ?></a></li>
    <li>[full] <a href="<?= $full; ?>"><?= $full; ?></a></li>
<?php
    foreach ($tests as $test) {
        foreach ($test['test'] as $link) {
            print '<li>[' . $test['control'] . '] <a href="' . $base . $link . '">' . $base . $link . '</a></li>';
        }
    }
?>
</ol>
<hr />
<?= $title; ?>
 - <a target="_blank" href="https://github.com/attogram/router">github</a>
 - <a target="_blank" href="https://packagist.org/packages/attogram/router">packagist</a>
 - <a target="_blank" href="https://codeclimate.com/github/attogram/router">codeclimate</a>
 - <?= date('r'); ?>
</body></html>
