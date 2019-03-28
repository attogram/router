<?php
/**
 * The Attogram Router
 * @see https://github.com/attogram/router
 */

declare(strict_types = 1);

namespace Attogram\Router;

use function array_column;
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
    const VERSION = '1.0.4';

    private $allow          = [];
    private $control        = '';
    private $controls       = [];
    private $forceSlash     = false;
    private $routesExact    = [];
    private $routesVariable = [];
    private $uriBase        = '';
    private $uriRelative    = '';
    private $uri            = [];
    private $uriCount       = 0;
    private $vars           = [];

    /**
     * @param bool $forceSlash
     */
    public function setForceSlash(bool $forceSlash)
    {
        $this->forceSlash = $forceSlash;
    }

    /**
     * Allow a route
     * @param string $route
     * @param string $control
     * @uses $this->allow
     */
    public function allow($route, $control)
    {
        $this->allow[] = [
            'route' => $this->trimArray(explode('/', $route)),
            'control' => $control,
        ];
    }

    /**
     * Match request to a controller
     * @uses $this->allow
     * @uses $this->controls
     * @return string
     */
    public function match(): string
    {
        $this->setUriProperties();
        $this->controls = array_column($this->allow, 'control');
        $this->setRouting();
        if ($this->matchExact() || $this->matchVariable()) {
            return $this->control;
        }

        return '';
    }

    /**
     * @uses $this->uri
     * @uses $this->uriBase
     * @uses $this->uriCount
     * @uses $this->uriRelative
     */
    private function setUriProperties()
    {
        $this->uriBase = strtr($this->getServer('SCRIPT_NAME'), ['index.php' => '']);
        $rUri = preg_replace('/\?.*/', '', $this->getServer('REQUEST_URI')); // remove query from URI
        $this->uriRelative = strtr($rUri, [$this->uriBase => '/']);
        $this->uriBase = rtrim($this->uriBase, '/'); // remove trailing slash from base URI
        if ($this->forceSlash && (1 !== preg_match('#/$#', $this->uriRelative))) { // If no slash at end of URI?
            $redirectUrl = $this->uriBase . $this->uriRelative . '/';
            if (!empty($_GET)) {
                $redirectUrl .= '?' . http_build_query($_GET);
            }
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirectUrl);
            exit;
        }
        $this->uri = $this->trimArray(explode('/', $this->uriRelative)); // make URI list
        $this->uriCount = count($this->uri); // directory depth of URI
    }

    /**
     * Split routes into ->routesExact and ->routesVariable
     * @uses $this->routesExact
     * @uses $this->routesVariable
     */
    private function setRouting()
    {
        foreach ($this->trimRoutesByUriSize() as $routeId => $route) {
            if (in_array('?', $route)) {
                $this->routesVariable[$routeId] = $route;
                continue;
            }
            $this->routesExact[$routeId] = $route;
        }
    }

    /**
     * Get an array of allowed routes that are the same size as the current URI
     * @uses $this->allow
     * @uses $this->uriCount
     * @return array
     */
    private function trimRoutesByUriSize(): array
    {
        $routes = [];
        foreach (array_column($this->allow, 'route') as $routeId => $route) {
            if ($this->uriCount === count($route)) {
                $routes[$routeId] = $route;
            }
        }
        return $routes;
    }

    /**
     * Match URI to an exact route
     * @uses $this->control
     * @uses $this->controls
     * @uses $this->routesExact
     * @return bool
     */
    private function matchExact(): bool
    {
        foreach ($this->routesExact as $routeId => $route) {
            if ($this->uri === $route) {
                $this->control = $this->controls[$routeId]; // matched - exact match
                return true;
            }
        }

        return false;
    }

    /**
     * Match URI to a variable route
     * @uses $this->control
     * @uses $this->controls
     * @uses $this->routesVariable
     * @return bool
     */
    private function matchVariable(): bool
    {
        foreach ($this->routesVariable as $routeId => $route) {
            $this->matchVariableVars($route);
            if (!empty($this->vars)) {
                $this->control = $this->controls[$routeId]; // matched - variable match
                return true;
            }
        }

        return false;
    }

    /**
     * Set vars if a variable match is found
     * @param array $route
     * @uses $this->uri
     * @uses $this->vars
     */
    private function matchVariableVars(array $route)
    {
        $this->vars = [];
        foreach ($route as $arrayId => $dir) {
            if (!in_array($dir, ['?', $this->uri[$arrayId]])) {
                $this->vars = [];
                return; // match failed - no exact match, no variable match
            }
            if ($dir === '?') { // found a variable
                $this->vars[] = $this->uri[$arrayId];
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
    private function getServer($name): string
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
