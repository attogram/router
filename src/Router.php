<?php
/**
 * The Attogram Router for PHP 7
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
    const VERSION = '3.0.1.pre.1';

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
     *          - variables are retrieved as an ordered array via: $router->getVars()
     *          - Examples:
     *              '/id/?'
     *              '/book/?/chapter/?'
     *              '/foo/?/?/?'
     *
     * control = anything you want, a string, a closure, an array, an object, an int, a float, whatever!
     *
     * @param string $route
     * @param mixed $control
     *
     * @uses $this->uriCount
     * @uses $this->routesExact
     * @uses $this->routesVariable
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
            $this->routesVariable[$route] = ['c' => $control, 'uri' => $routeUri]; // add variable route

            return; // Variable route found
        }
        $this->routesExact[$route] = ['c' => $control, 'uri' => $routeUri]; // add exact route
    }

    /**
     * Get the matching control or the current request
     *      - optionally, force a trailing slash on current request
     *
     * @uses $this->control
     * @uses $this->forceSlash
     * @uses $this->uriRelative
     * @return mixed|null
     */
    public function match()
    {
        // if forceSlash is ON, and there is no trailing slash on current request
        if ($this->forceSlash && (1 !== preg_match('#/$#', $this->uriRelative))) {
            $this->forceSlash();
        }
        // Find control for current request, first with exact matching, then with variable matching
        if ($this->matchExact() || $this->matchVariable()) {
            return $this->control; // Match found
        }

        return null; // No match found
    }

    /**
     * @param bool $full
     * @return string
     */
    public function getHome(bool $full = false): string
    {
        return $this->getPreFull($full) . $this->uriBase;
    }

    /**
     * @param bool $full
     * @return string
     */
    public function getCurrent(bool $full = false): string
    {
        return $this->getHome($full) . $this->uriRelative;
    }

    /**
     * @param bool $full
     * @return string
     */
    private function getPreFull(bool $full = false): string
    {
        if ($full) {
            return $this->getProtocol()  . '://' . $this->getServerName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return (
            (!empty($this->getServer('HTTPS')) && ($this->getServer('HTTPS') !== 'off'))
            || ($this->getServer('SERVER_PORT') == 443)
        )
            ? 'https'
            : 'http';
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->getServer('SERVER_NAME');
    }

    /**
     * Get an array of URI variables from the current request
     *
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }

    /**
     * set Force a trailing slash on all requests?
     *
     * @param bool $forceSlash
     * @uses $this->forceSlash
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
     * Redirect to a new URL, and exit
     *  - optionally set HTTP Response Code (301 = Moved Permanently, 302 = Found)
     *
     * @param string $url
     * @param int $httpResponseCode
     */
    public function redirect(string $url, int $httpResponseCode = 301)
    {
        header('Location: ' . $url, true, $httpResponseCode);

        exit; // After a redirect, we must exit to halt any further script execution
    }

    /**
     * get a value from a global array, or the whole global array
     *
     * @param string $global
     * @param string $name
     * @return array|string
     */
    private function getGlobal(string $global, string $name)
    {
        if (!isset($GLOBALS) || !isset($GLOBALS[$global]) || !is_array($GLOBALS[$global])) {
            return ''; // Global does not exist, or is not array
        }
        if (!$name) {
            return $GLOBALS[$global]; // return entire Global array
        }
        if (!empty($GLOBALS[$global][$name])) {
            return $GLOBALS[$global][$name]; // return requested Global variable
        }

        return ''; // Not Found or Empty
    }

    /**
     * Force a trailing slash on the current request
     *
     * @uses $this->uriBase
     * @uses $this->uriRelative
     */
    private function forceSlash()
    {
        // add a trailing slash to the current URL
        $url = $this->uriBase . $this->uriRelative . '/';
        // if there is a query string in the current request
        if (!empty($this->getGet())) {
            // add the query string to the redirect URL
            $url .= '?' . http_build_query($this->getGet());
        }
        $this->redirect($url);
    }

    /**
     * Match URI to an exact route
     *
     * @uses $this->control
     * @uses $this->routesExact
     * @uses $this->uri
     * @return bool
     */
    private function matchExact(): bool
    {
        foreach ($this->routesExact as $route) {
            if ($this->uri === $route['uri']) { // compare the current URI array to this route URI array
                $this->control = $route['c']; // set control or this exact match

                return true; // exact match found
            }
        }

        return false; // exact match not found
    }

    /**
     * Match URI to a variable route
     *
     * @uses $this->control
     * @uses $this->routesVariable
     * @return bool
     */
    private function matchVariable(): bool
    {
        foreach ($this->routesVariable as $route) {
            $this->matchVariableVars($route['uri']); // find variables
            if (empty($this->vars)) {
                continue; // no variable match yet
            }
            $this->control = $route['c']; // set control for this variable match

            return true; // variable match found
        }

        return false; // variable match not found
    }

    /**
     * Populates $this->vars if a variable match is found
     *
     * @param array $routeUri
     * @uses $this->uri
     * @uses $this->vars
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
}
