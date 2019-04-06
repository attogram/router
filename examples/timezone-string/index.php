<?php
declare(strict_types = 1);

use Attogram\Router\Router;

require '../../vendor/autoload.php';

$router = new Router();

$title = 'Timezone String Control - Attogram Router Examples';

$homeLink = '<a href="' . $router->getHome() . '../">Attogram Router</a>'
    . ' - <a href="' . $router->getHome() . '">' . $title . '</a>';

$header = '<html lang="en"><head><title>' . $title . '</title>'
    . '<style>a { text-decoration:none; }</style>'
    . '</head><body>' . $homeLink . '<hr />';
$footer = '<footer><hr />' . $homeLink . '</footer></body></html>';

$router->allow('/', 'timezones');
$router->allow('?', 'timezones');
$router->allow('?/?', 'timezones');
$router->allow('?/?/?', 'timezones');

if ($router->match() !== 'timezones') {
    header('HTTP/1.0 404 Not Found');
    die("$header <h1>Page Not Found</h1> $footer");
}

$timeZone = 'UTC';
if (!empty($router->getVar(0))) {
    $timeZone = $router->getVar(0);
}
if (!empty($router->getVar(1))) {
    $timeZone .= '/' . $router->getVar(1);
}
if (!empty($router->getVar(2))) {
    $timeZone .= '/' . $router->getVar(2);
}
if (!@date_default_timezone_set($timeZone)) {
    header('HTTP/1.0 404 Not Found');
    die("$header <h1>Timezone Not Found</h1> $footer");
}
print $header . '<h1>' . $timeZone . '<br />' . date('r') . '</h1><p>Timezones ';
foreach (timezone_identifiers_list() as $id) {
    print ' - <a href="' . $router->getHome() . $id . '">' . $id . '</a>';
}
print '</p>' . $footer;
