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

    private $empty = '<span class="empty">empty</span>';

    /**
     * @param array $tests
     */
    public function __construct(array $tests)
    {
        session_start();
        $this->checkForceSlash();
        $this->tests = $tests;
        $this->setupRouter();
        $this->htmlHeader();
        $this->pageHeader();
        $this->testResults();
        $this->testList();
        $this->pageHeader();
        $this->htmlFooter();
    }

    public function checkForceSlash()
    {
        if (isset($_GET['forceSlash'])) {
            $_SESSION['forceSlash'] = $_GET['forceSlash'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . str_replace('index.php', '', $_SERVER['PHP_SELF']));
            return;
        }
    }

    public function setupRouter()
    {
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
            . '<h1>' . $name . ' <small>v' . Router::VERSION . '</small></h1>'
            . '<br /><a href="' . $this->router->getHome() . '/../">About</a>'
            . ' - <a href="' . $this->router->getHome() . '">RESET</a>'
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
            . '<tr><th>route</th><th>control</th><th>examples</th></tr>';
        foreach ($this->tests as $test) {
            foreach ($test['test'] as $link) {
                print '<tr>'
                    . '<td>' . $test['route'] . '</td>'
                    . '<td>(' . gettype($test['control']) . ') ' . print_r($test['control'], true) . '</td>'
                    . '<td><a href="' . $this->router->getHome() . $link . '">'
                    . $this->router->getHome() . $link . '</a></td>'
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
            . '<tr><td>geVars()</td><td>' .  $this->getVarResults() . '</td></tr>'
            . '<tr><td>$_GET</td><td>' . $this->getGetResults() . '</td></tr>'
            . '<tr><td>forceSlash</td><td>'
            . ((isset($_SESSION['forceSlash']) && $_SESSION['forceSlash']) ? 'true' : 'false') . '</td></tr>'
            . '<tr><td>getCurrent()</td><td>' . $this->router->getCurrent() . '</td></tr>'
            . '<tr><td>getHome()</td><td>' . $this->router->getHome() . '</td></tr>'
            . '<tr><td>getCurrent(true)</td><td>' . $this->router->getCurrent(true) . '</td></tr>'
            . '<tr><td>getHome(true)</td><td>' . $this->router->getHome(true) . '</td></tr>'
            . '</table><p>[ Router setup: '
            . '<a href="?forceSlash=1">Force Slash</a> - <a href="?forceSlash=0">Do Not Force Slash</a> ]</p>';
    }

    public function getMatchResults()
    {
        $match = $this->router->match();
        switch ($match) {
            case '':
                return $this->empty;
            default:
                return '<span class="full">(' . gettype($match) . ') ' . print_r($match, true) . '</span>';
        }
    }

    public function getVarResults()
    {
        if (empty($this->router->getVars())) {
            return $this->empty;
        }
        $varsResults = '<span class="full">';
        foreach ($this->router->getVars() as $name => $value) {
            $varsResults .= "$name = $value<br />";
        }

        return $varsResults . '</span>';
    }

    public function getGetResults()
    {
        if (empty($_GET)) {
            return $this->empty;
        }
        $getResults = '<span class="full">';
        foreach ($_GET as $name => $value) {
            $getResults .= htmlentities((string) $name)
                . ' = ' . htmlentities((string) $value) . '<br />';
        }

        return $getResults . '</span>';
    }
}
