<?php
/**
 * The Attogram Router for PHP 7 - small, flexible, and surprisingly powerful
 *
 * @see https://github.com/attogram/router
 * @see https://getitdaily.com/attogram-router/
 *
 * @license MIT
 */

declare(strict_types = 1);

namespace Attogram\Router;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function header;
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
    const VERSION = '4.1.1-pre.1';

    private $control        = null;
    private $forceSlash     = false;
    private $routesExact    = [];
    private $routesVariable = [];
    private $uri            = [];
    private $uriBase        = '';
    private $uriCount       = 0;
    private $uriRelative    = '';
    private $vars           = [];

    /**
     * Router constructor.
     */
    public function __construct()
    {
        // Get the Base of the URI, without 'index.php'
        $this->uriBase = strtr($this->getServer('SCRIPT_NAME'), ['index.php' => '']);
        // make Relative URI - remove query string from the request (everything after ?)
        $this->uriRelative = preg_replace('/\?.*/', '', $this->getServer('REQUEST_URI'));
        // make Relative URI - remove the Base URI
        $this->uriRelative = strtr($this->uriRelative, [$this->uriBase => '/']);
        // remove trailing slash from Base URI
        $this->uriBase = rtrim($this->uriBase, '/');
        // make array from Relative URI
        $this->uri = $this->getUriArray($this->uriRelative);
        // directory depth of current request
        $this->uriCount = count($this->uri);
        // If needed, Bypass directive auto_globals_jit
        if (!isset($GLOBALS['_SERVER'])) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $_SERVER; // force compiler to populate _SERVER into GLOBALS
        }
    }

    /**
     * Allow a route
     *
     * $router->allow($route, $control);
     *
     * route = a string with the URI list, forward-slash delimited
     *
     *      Exact routing:
     *         Home:  '/'
     *         Page:  '/foo/bar'
     *           - preceding and trailing slashes are optional, except for top level '/'
     *
     *      Variable routing:
     *          - use a question mark to denote a URI segment as a variable
     *          - variables are retrieved via: $router->getVar(int $index)
     *          - Examples:
     *              '/id/?'             - retrieve with getVar(0)
     *              '/book/?/chapter/?' - retrieve with getVar(0) and getVar(1)
     *              '/foo/?/?/?'        - retrieve with getVar(0), getVar(1) and getVar(2)
     *
     * control = anything you want, a string, a closure, an array, an object, an int, a float, whatever!
     *
     * @param string $route
     * @param mixed $control
     */
    public function allow(string $route, $control)
    {
        // make an array of the route
        $routeUri = $this->getUriArray($route);
        // Is this route not the same size as the current URI?
        if ($this->uriCount !== count($routeUri)) {
            return; // Do not add route
        }
        // Single Question Mark denotes a variable routing
        if (in_array('?', $routeUri)) {
            $this->routesVariable[$route] = ['c' => $control, 'u' => $routeUri]; // add variable route

            return; // Variable route found
        }
        $this->routesExact[$route] = ['c' => $control, 'u' => $routeUri]; // add exact route
    }

    /**
     * Get the matching control for the current request
     *      - optionally, force a trailing slash on current request
     *
     * @return mixed|null
     */
    public function match()
    {
        // if forceSlash is ON, and there is no trailing slash on current request
        if ($this->forceSlash && !$this->hasTrailingSlash($this->uriRelative)) {
            $this->forceSlash();
        }
        // Find control for current request, first with exact matching, then with variable matching
        if ($this->matchExact() || $this->matchVariable()) {
            return $this->control; // Match found
        }

        return null; // No match found
    }

    /**
     * @return string
     */
    public function getHome(): string
    {
        return $this->uriBase . ($this->hasTrailingSlash($this->uriBase) ? '' : '/');
    }

    /**
     * @return string
     */
    public function getHomeFull(): string
    {
        return$this->getHostFull() . $this->getHome();
    }

    /**
     * @return string
     */
    public function getCurrent(): string
    {
        return $this->uriBase . $this->uriRelative;
    }

    /**
     * @return string
     */
    public function getCurrentFull(): string
    {
        return $this->getHostFull() . $this->getCurrent();
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        $serverName = $this->getServer('SERVER_NAME');
        $serverPort = $this->getServer('SERVER_PORT');

        return $serverName . (!in_array((int) $serverPort, [80, 443]) ? ':' . $serverPort : '');
    }

    /**
     * @return string
     */
    public function getHostFull(): string
    {
        return $this->getProtocol() . '://' . $this->getHost();
    }

    /**
     * @return string - 'http' or 'https'
     */
    public function getProtocol(): string
    {
        $https = $this->getServer('HTTPS');
        $serverPort = $this->getServer('SERVER_PORT');

        return (
            (!empty($https) && ($https !== 'off'))
            || ($serverPort == 443)
        )
            ? 'https'
            : 'http';
    }

    /**
     * Get a URI variable, based on index
     *
     * @param int $index
     * @return string|null
     */
    public function getVar(int $index = 0)
    {
        return isset($this->vars[$index]) ? $this->vars[$index] : null;
    }

    /**
     * Force a trailing slash on all requests?  Occurs in match()
     *
     * @param bool $forceSlash
     */
    public function setForceSlash(bool $forceSlash)
    {
        $this->forceSlash = $forceSlash;
    }

    /**
     * get the value of a global _SERVER variable, or the whole _SERVER array
     *
     * @param string $name
     * @return array|string
     */
    public function getServer(string $name = '')
    {
        return $this->getGlobal('_SERVER', $name);
    }

    /**
     * Redirect to a new URL, and exit
     *  - optionally set HTTP Response Code (301 = Moved Permanently, 302 = Found)
     *
     * @param string $url
     * @param int $httpResponseCode
     * @param bool $exitAfter - default: true
     */
    public function redirect(string $url, int $httpResponseCode = 301, bool $exitAfter = true)
    {
        header('Location: ' . $url, true, $httpResponseCode);
        if ($exitAfter) {
            exit; // After a redirect, we must exit to halt any further script execution
        }
    }

    /**
     * get a value from the global _GET array, or the whole _GET array
     *
     * @param string $name
     * @return array|string
     */
    public function getGet(string $name = '')
    {
        return $this->getGlobal('_GET', $name);
    }

    /**
     * get a value from a global array, or the whole global array
     *
     * @param string $global
     * @param string $name
     * @return array|null|string
     */
    private function getGlobal(string $global, string $name)
    {
        if (!isset($GLOBALS[$global]) // Global does not exist
            || !is_array($GLOBALS[$global]) // Global is not an array
            || ($name && !isset($GLOBALS[$global][$name])) // Global variable does not exist
        ) {
            return null;
        }
        if (!$name) {
            return $GLOBALS[$global]; // return entire Global array
        }

        return $GLOBALS[$global][$name]; // return requested Global variable
    }

    /**
     * @param string $uri
     * @return bool
     */
    private function hasTrailingSlash(string $uri): bool
    {
        return (1 === preg_match('#/$#', $uri)) ? true : false;
    }

    /**
     * Force a trailing slash on the current request
     */
    private function forceSlash()
    {
        // add a trailing slash to the current URL
        $url = $this->getCurrentFull() . '/';
        // if there is a query string in the current request
        $queryString = $this->getServer('QUERY_STRING');
        if (!empty($queryString)) {
            // add the query string to the redirect URL
            $url .= '?' . $queryString;
        }
        $this->redirect($url);
    }

    /**
     * Match URI to an exact route
     * @return bool
     */
    private function matchExact(): bool
    {
        foreach ($this->routesExact as $route) {
            if ($this->uri === $route['u']) { // compare the current URI array to this route URI array
                $this->control = $route['c']; // set control or this exact match

                return true; // exact match found
            }
        }

        return false; // exact match not found
    }

    /**
     * Match URI to a variable route
     * @return bool
     */
    private function matchVariable(): bool
    {
        foreach ($this->routesVariable as $route) {
            $this->matchVariableVars($route['u']); // find variables
            if (empty($this->vars)) {
                continue; // no variable match yet
            }
            $this->control = $route['c']; // set control for this variable match

            return true; // variable match found
        }

        return false; // variable match not found
    }

    /**
     * Populate an ordered array of URI segment variables
     *
     * @param array $routeUri
     */
    private function matchVariableVars(array $routeUri)
    {
        $this->vars = [];
        foreach ($routeUri as $index => $route) {
            if (!in_array($route, ['?', $this->uri[$index]])) {
                $this->vars = [];
                return; // match failed - no exact match, no variable match
            }
            if ($route === '?') { // found a variable
                $this->vars[] = $this->uri[$index];
            }
        }
    }

    /**
     * Build an array from a URI string
     *
     * @param string $uri
     * @return array
     */
    private function getUriArray(string $uri): array
    {
        $array = explode('/', $uri);
        if ($array[0] === '') { // If first segment is empty
            array_shift($array); // trim off first segment
        }

        if (count($array) <= 1) { // If array has 1 or less segments
            return $array;
        }
        if ($array[count($array)-1] === '') { // If last segment is empty
            array_pop($array); // trim off last segment
        }

        return $array;
    }
}
