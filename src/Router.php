<?php
/**
 * The Attogram Router
 *
 * @see https://github.com/attogram/router
 * @license MIT
 */

declare(strict_types = 1);

namespace Attogram\Router;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function header;
use function http_build_query;
use function in_array;
use function preg_match;
use function preg_replace;
use function rtrim;
use function strtr;

/**
 * Class Router
 * @package Attogram\Router
 */
class Router
{
    const VERSION = '1.1.1';

    private $control        = '';
    private $forceSlash     = false;
    private $routesExact    = [];
    private $routesVariable = [];
    private $uriBase        = '';
    private $uriRelative    = '';
    private $uri            = [];
    private $uriCount       = 0;
    private $vars           = [];

    /**
     * @uses $this->uri
     * @uses $this->uriBase
     * @uses $this->uriCount
     * @uses $this->uriRelative
     */
    public function __construct()
    {
        $this->uriBase = strtr($this->getServer('SCRIPT_NAME'), ['index.php' => '']);
        $this->uriRelative = preg_replace('/\?.*/', '', $this->getServer('REQUEST_URI')); // remove query from URI
        $this->uriRelative = strtr($this->uriRelative, [$this->uriBase => '/']);
        $this->uriBase = rtrim($this->uriBase, '/'); // remove trailing slash from base URI
        $this->uri = $this->trimArray(explode('/', $this->uriRelative)); // make URI list
        $this->uriCount = count($this->uri); // directory depth of URI
    }

    /**
     * @param bool $forceSlash
     * @uses $this->forceSlash
     */
    public function setForceSlash(bool $forceSlash)
    {
        $this->forceSlash = $forceSlash;
    }

    /**
     * @param string $route
     * @param string $control
     * @uses $this->uriCount
     * @uses $this->routesExact
     * @uses $this->routesVariable
     */
    public function allow(string $route, string $control)
    {
        $route = $this->trimArray(explode('/', $route)); // make an array of the route
        if ($this->uriCount !== count($route)) { // Is this route is same size as current URI?
            return; // Do not add route
        }
        if (in_array('?', $route)) { // Question Mark ? character denotes a variable routing
            $this->routesVariable[$control] = $route; // This route is a variable routing

            return;
        }
        $this->routesExact[$control] = $route; // This route is an exact routing
    }

    /**
     * @uses $this->control
     * @return string
     */
    public function match(): string
    {
        $this->checkForceSlash();
        if ($this->matchExact() || $this->matchVariable()) {
            return $this->control;
        }

        return '';
    }

    /**
     * @uses $_GET
     * @uses $this->forceSlash
     * @uses $this->uriBase
     * @uses $this->uriRelative
     */
    private function checkForceSlash()
    {
        if (!$this->forceSlash || !(1 !== preg_match('#/$#', $this->uriRelative))) {
            return;
        }
        $redirectUrl = $this->uriBase . $this->uriRelative . '/';
        if (!empty($_GET)) {
            $redirectUrl .= '?' . http_build_query($_GET);
        }
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirectUrl);

        exit; // After a redirect, we must exit to halt any further script execution
    }

    /**
     * Match URI to an exact route
     * @uses $this->control
     * @uses $this->routesExact
     * @return bool
     */
    private function matchExact(): bool
    {
        foreach ($this->routesExact as $control => $route) {
            if ($this->uri === $route) {
                $this->control = $control; // matched - exact match

                return true;
            }
        }
        return false;
    }

    /**
     * Match URI to a variable route
     * @uses $this->control
     * @uses $this->routesVariable
     * @return bool
     */
    private function matchVariable(): bool
    {
        foreach ($this->routesVariable as $control => $route) {
            $this->matchVariableVars($route);
            if (!empty($this->vars)) {
                $this->control = $control; // matched - variable match

                return true;
            }
        }
        return false;
    }

    /**
     * Set vars if a variable match is found
     * @param array $routes
     * @uses $this->uri
     * @uses $this->vars
     */
    private function matchVariableVars(array $routes)
    {
        $this->vars = [];
        foreach ($routes as $control => $route) {
            if (!in_array($route, ['?', $this->uri[$control]])) {
                $this->vars = [];

                return; // match failed - no exact match, no variable match
            }
            if ($route === '?') { // found a variable
                $this->vars[] = $this->uri[$control];
            }
        }
    }

    /**
     * @param array $array
     * @return array
     */
    private function trimArray(array $array): array
    {
        if ($array[0] === '') { // trim off first empty element
            array_shift($array);
        }
        if (count($array) <= 1) {
            return $array;
        }
        if ($array[count($array)-1] === '') { // trim off last empty element
            array_pop($array);
        }

        return $array;
    }

    /**
     * get the value of a global _SERVER variable
     * @param string $name
     * @uses $_SERVER
     * @return string
     */
    private function getServer(string $name): string
    {
        if (!empty($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getUriBase(): string
    {
        return $this->uriBase;
    }

    /**
     * @return string
     */
    public function getUriRelative(): string
    {
        return $this->uriRelative;
    }

    /**
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }
}
