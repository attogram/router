<?php

namespace Attogram\Router;

/**
 * Attogram Router
 */
class Router
{
    const VERSION = '0.1.0';

    public $forceSlashAtEnd = false;

    private $allow = [];
    private $control = '';
    private $controls = [];
    private $routesExact = [];
    private $routesVariable = [];
    private $uriBase = '';
    private $uriRelative = '';
    private $uri = [];
    private $uriCount = 0;
    private $vars = [];

    /**
     * __construct
     * Sets: ->uriBase, ->uriRelative, ->uri, and ->uriCount
     * - optionally forces slash at end of URL
     */
    public function __construct()
    {
        $this->uriBase = strtr($this->getServer('SCRIPT_NAME'), ['index.php' => '']);
        $rUri = preg_replace('/\?.*/', '', $this->getServer('REQUEST_URI')); // remove query
        $this->uriRelative = strtr($rUri, [$this->uriBase => '/']);
        $this->uriBase = rtrim($this->uriBase, '/'); // remove trailing slash from base URI
        $this->uri = $this->trimArray(explode('/', $this->uriRelative)); // make uri list
        $this->uriCount = count($this->uri);
        if ($this->forceSlashAtEnd && 1 !== preg_match('#/$#', $this->uriRelative)) { // no slash at end of URL?
            $this->redirect($this->uriBase . $this->uriRelative . '/'); // Force trailing slash
        }
    }

    /**
     * Allow a route
     * @param string $route
     * @param string $control
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
     * @return string|null
     */
    public function match()
    {
        $this->controls = array_column($this->allow, 'control');
        $this->setRouting();
        if ($this->matchExact() || $this->matchVariable()) {
            return $this->control;
        }
    }

    /**
     * Split routes into ->routesExact and ->routesVariable
     * @return void
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
     * @return array
     */
    private function trimRoutesByUriSize()
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
     * @return bool
     */
    private function matchExact()
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
     * @return bool
     */
    private function matchVariable()
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
     * Set ->vars[] if a variable match is found
     * @param array $route
     * @return void
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
    private function trimArray(array $array)
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
     * @param string $url
     */
    private function redirect($url)
    {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        exit;
    }

    /**
     * get the value of a global _SERVER variable
     * @param string $name
     * @return mixed
     */
    private function getServer($name)
    {
        if (!empty($_SERVER[$name])) {
            return $_SERVER[$name];
        }
    }
    /**
     * @return string
     */
    public function getUriBase()
    {
        return $this->uriBase;
    }

    /**
     * @return string
     */
    public function getUriRelative()
    {
        return $this->uriRelative;
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }
}
