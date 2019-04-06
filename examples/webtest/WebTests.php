<?php
declare(strict_types = 1);

namespace Attogram\Router\Tests;

use Attogram\Router\Router;

class WebTests
{
    /** @var Router */
    public $router;

    /** @var array */
    public $tests = [];

    /** @var float */
    public $timer;

    /**
     * @param array $tests
     */
    public function __construct(array $tests)
    {
        session_start();
        $this->tests = $tests;
        $this->setupRouter();
        $this->checkForceSlash();
        $this->htmlHeader();
        $this->pageHeader();
        $this->testResults();
        $this->testList();
        print '<br style="clear:both;" />';
        $this->pageHeader();
        $this->htmlFooter();
    }

    public function checkForceSlash()
    {
        if (isset($_GET['forceSlash'])) {
            $_SESSION['forceSlash'] = $_GET['forceSlash'];
            $this->router->redirect($this->router->getHomeFull(), 302);
            return;
        }
    }

    public function setupRouter()
    {
        $this->timer = microtime(true);
        $this->router = new Router();
        if (isset($_SESSION['forceSlash'])) {
            $this->router->setForceSlash((bool)$_SESSION['forceSlash']);
        }

        // Setup Test Routes
        foreach ($this->tests as $test) {
            $this->router->allow($test['route'], $test['control']);
        }
    }

    public function htmlHeader()
    {
        print '<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Attogram Router v' . Router::VERSION . ' Test Page</title>
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
<body>';
    }

    public function htmlFooter()
    {
        print '</body></html>';
    }

    public function pageHeader()
    {
        $name = 'attogram/router';
        print '<div class="hdr">'
            . '<h1>Attogram Router Web Tests</h1>'
            . '<br /><a href="' . $this->router->getHome() . '">RESET test</a>'
            . ' - <a target="_blank" href="https://github.com/' . $name . '">Github</a>'
            . ' - <a target="_blank" href="https://getitdaily.com/attogram-router/">Getitdaily</a>'
            . ' - <a target="_blank" href="https://packagist.org/packages/' . $name . '">Packagist</a>'
            . ' - <a target="_blank" href="https://codeclimate.com/github/' . $name . '">Codeclimate</a>'
            . ' - <a target="_blank" href="https://travis-ci.org/' . $name . '">Travis-CI</a>'
            . '</div>';
    }

    public function testList()
    {
        print '<table>'
            . '<tr><th>route</th><th>control</th><th>web tests</th></tr>';
        foreach ($this->tests as $test) {
            foreach ($test['test'] as $link) {
                $testLink = $this->router->getHome() . ltrim($link, '/');
                print '<tr>'
                    . '<td>' . $test['route'] . '</td>'
                    . '<td>(' . gettype($test['control']) . ') ' . print_r($test['control'], true) . '</td>'
                    . '<td><a href="' . $testLink . '">' . $testLink . '</a></td>'
                    . '</tr>';
            }
        }
        print '</table>';
    }

    public function testResults()
    {
        print '<table style="float:left;">'
            . '<tr><th colspan="2">Test Results @ ' . gmdate('Y-m-d H:i:s') . ' UTC</th></tr>'
            . '<tr><td>match()</td><td>' . $this->getMatchResults() . '</td></tr>'
            . '<tr><td>getVar(0)</td><td>' .  $this->router->getVar(0) . '</td></tr>'
            . '<tr><td>getVar(1)</td><td>' .  $this->router->getVar(1) . '</td></tr>'
            . '<tr><td>getVar(2)</td><td>' .  $this->router->getVar(2) . '</td></tr>'
            . '<tr><td>getVar(3)</td><td>' .  $this->router->getVar(3) . '</td></tr>'
            . '<tr><td>getVar(4)</td><td>' .  $this->router->getVar(4) . '</td></tr>'
            . '<tr><td>getCurrent()</td><td>' . $this->router->getCurrent() . '</td></tr>'
            . '<tr><td>getHome()</td><td>' . $this->router->getHome() . '</td></tr>'
            . '<tr><td>getCurrentFull()</td><td>' . $this->router->getCurrentFull() . '</td></tr>'
            . '<tr><td>getHomeFull()</td><td>' . $this->router->getHomeFull() . '</td></tr>'
            . '<tr><td>getHost()</td><td>' . $this->router->getHost() . '</td></tr>'
            . '<tr><td>getHostFull()</td><td>' . $this->router->getHostFull() . '</td></tr>'
            . '<tr><td>getProtocol()</td><td>' . $this->router->getProtocol() . '</td></tr>'
            . '<tr><td>VERSION</td><td>' . Router::VERSION . '</td></tr>'
            . '<tr><td>forceSlash</td><td>'
                . ((isset($_SESSION['forceSlash']) && $_SESSION['forceSlash']) ? 'true' : 'false') . '</td></tr>'
            . "<tr><td>getServer('REQUEST_URI')</td><td>" . $this->router->getServer('REQUEST_URI') . '</td></tr>'
            . "<tr><td>getServer('QUERY_STRING')</td><td>" . $this->router->getServer('QUERY_STRING') . '</td></tr>'
            . "<tr><td>getServer('SCRIPT_NAME')</td><td>" . $this->router->getServer('SCRIPT_NAME') . '</td></tr>'
            . "<tr><td>getServer('SERVER_NAME')</td><td>" . $this->router->getServer('SERVER_NAME') . '</td></tr>'
            . "<tr><td>getServer('HTTPS')</td><td>" . $this->router->getServer('HTTPS') . '</td></tr>'
            . "<tr><td>getServer('SERVER_PORT')</td><td>" . $this->router->getServer('SERVER_PORT') . '</td></tr>'
            . '<tr><td>getGet()</td><td>' . $this->getGetResults() . '</td></tr>'
            . '<tr><td>auto_globals_jit</td><td>' . (ini_get('auto_globals_jit') ? 'true' : 'false') . '</td></tr>'
            . '<tr><td>Benchmark time</td><td>' . $this->timer . '</td></tr>'
            . '</table>'
            . '<p>[ Router setup: '
            . '<a href="?forceSlash=1">Force Slash</a> - <a href="?forceSlash=0">Do Not Force Slash</a> ]</p>';
    }

    public function getMatchResults()
    {
        $match = $this->router->match();
        $this->timer = microtime(true) - $this->timer;

        return '(' . gettype($match) . ') ' . print_r($match, true);
    }

    public function getGetResults()
    {
        if (empty($this->router->getGet())) {
            return '';
        }
        $getResults = '';
        foreach ($this->router->getGet() as $name => $value) {
            $getResults .= htmlentities((string) $name)
                . ' = ' . htmlentities((string) $value) . '<br />';
        }

        return $getResults;
    }
}
