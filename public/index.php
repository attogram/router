<?php // attogram router  index.php  v0.0.2

use Attogram\Router\Router;

require_once('../vendor/autoload.php');

$router = new Router();

$router->allow('/', 'TestClass::home');
$router->allow('/home/', 'TestClass::home');
$router->allow('/test/', 'TestClass::test1');
$router->allow('/test/test/', 'TestClass::test2');
$router->allow('/test/test/test/', 'TestClass::test3');
$router->allow('/test/test/test/test/', 'TestClass::test4');
$router->allow('/test/test/test/test/test/', 'TestClass::test5');
$router->allow('/test/?/', 'TestClass::test2variable');
$router->allow('/test2/?/', 'TestClass::test2_2variable');
$router->allow('/test/?/test/', 'TestClass::test3variable');
$router->allow('/test/?/test/?/', 'TestClass::test4variable2');
$router->allow('/test/?/test/?/?/', 'TestClass::test5variable3');


$control  = $router->match();
$base     = $router->getUriBase();
$relative = $router->getUriRelative();
$full     = $base . $relative;
$vars     = $router->getVars();

$title = 'Attogram Router v' . $router::VERSION;

?><html><head><title><?= $title; ?></title><style type="text/css">
body { background-color:lightgrey; color:black; font-family:monospace; }
a:hover, a:active { background-color:yellow; }
h1 { display:inline; }
li { line-height:150%; }
.pre { white-space:pre; line-height:150%; }
.box { font-weight:bold; padding:0 4px 0 4px; display:inline; }
.empty { background-color:darkred; color:yellow; }
.good { background-color:darkgreen; color:yellow; }
.vars { display:block; }
</style></head><body>
<h1><?= $title; ?></h1>
<p class="pre">control  : <?php
    echo !empty($control)
        ? '<span class="box good">' . $control . '</span>'
        : '<span class="box empty">404</span>'; ?> &nbsp;
full     : <?= $full; ?> &nbsp;
base     : <?= $base; ?> &nbsp;
relative : <?= $relative; ?> &nbsp;
vars     : <?php
    echo !empty($vars)
        ? '<span class="box good vars">' . print_r($vars, true) . '</span>'
        : '<span class="box empty">null</span>'; ?> &nbsp;
_GET     : <?php
    echo !empty($_GET)
        ? '<span class="box good vars">' . print_r($_GET, true) . '</span>'
        : '<span class="box empty">null</span>'; ?> &nbsp;
</p>
<hr />
<ol>
    <li><a href="<?= $base; ?>"><?= $base; ?></a> (base)</li>
    <li><a href="<?= $full; ?>"><?= $full; ?></a> (full)</li>
    <li><a href="<?= $base; ?>/home/"><?= $base; ?>/home/</a></li>
    <li><a href="<?= $base; ?>/test/"><?= $base; ?>/test/</a></li>
    <li><a href="<?= $base; ?>/test/test/"><?= $base; ?>/test/test/</a></li>
    <li><a href="<?= $base; ?>/test/test/test/"><?= $base; ?>/test/test/test/</a></li>
    <li><a href="<?= $base; ?>/test/test/test/test/"><?= $base; ?>/test/test/test/test/</a></li>
    <li><a href="<?= $base; ?>/test/test/test/test/test/"><?= $base; ?>/test/test/test/test/test/</a></li>
    <li><a href="<?= $base; ?>/test/FOO/"><?= $base; ?>/test/FOO/</a></li>
    <li><a href="<?= $base; ?>/test2/FOO/"><?= $base; ?>/test2/FOO/</a></li>
    <li><a href="<?= $base; ?>/test/FOO/test/"><?= $base; ?>/test/FOO/test/</a></li>
    <li><a href="<?= $base; ?>/test/FOO/test/BAR/"><?= $base; ?>/test/FOO/test/BAR/</a></li>
    <li><a href="<?= $base; ?>/test/FOO/test/BAR/BAZ/"><?= $base; ?>/test/FOO/test/BAR/BAZ/</a></li>
    <li><a href="<?= $base; ?>/test/FOO/test/BAR/BAZ/?bar=baz"><?= $base; ?>/test/FOO/test/BAR/BAZ/?bar=baz</a></li>
    <li><a href="<?= $base; ?>?a=b&x=y"><?= $base; ?>?a=b&x=y</a></li>
    <li><a href="<?= $base; ?>/404/"><?= $base; ?>/404/</a></li>
</ol>
<hr />
<?= $title; ?>
- <a target="_blank" href="https://github.com/attogram/router">github</a>
- <a target="_blank" href="https://packagist.org/packages/attogram/router">packagist</a>
- <a target="_blank" href="https://codeclimate.com/github/attogram/router">codeclimate</a>
- <?= date('r'); ?>
</body></html>
