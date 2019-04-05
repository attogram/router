<?php
declare(strict_types = 1);

use Attogram\Router\Router;

require '../../vendor/autoload.php';

$router = new Router();

$title = 'Timezone String Control - Attogram Router Examples';

$homeLink = '<a href="' . $router->getHome() . '/../">Attogram Router</a>'
    . ' - <a href="' . $router->getHome() . '">' . $title . '</a>';

$header = '<html lang="en"><head><title>' . $title . '</title></head><body>' . $homeLink . '<hr />';
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
$vars = $router->getVars();
if (!empty($vars[0])) {
    $timeZone = $vars[0];
}
if (!empty($vars[1])) {
    $timeZone .= '/' . $vars[1];
}
if (!empty($vars[2])) {
    $timeZone .= '/' . $vars[2];
}
if (!@date_default_timezone_set($timeZone)) {
    header('HTTP/1.0 404 Not Found');
    die("$header <h1>Timezone Not Found</h1> $footer");
}
print $header . '<h1>' . $timeZone . '<br />' . date('r') . '</h1><p>Timezones ';
foreach (timezone_identifiers_list() as $id) {
    print ' - <a href="' . $router->getHome() . '/' . $id . '">' . $id . '</a>';
}
print '</p>' . $footer;
