<?php

namespace Attogram\Router;

/**
 * Attogram Router
 */
class Router
{
    const VERSION = '0.0.4';

    private $uriBase = '';
    private $uriRelative = '';
    private $uri = [];
    private $uriCount = 0;
    private $routes = [];
    private $routesExact = [];
    private $routesVariable = [];
    private $controls = [];
    private $vars = [];


    /**
     * __construct
     */
    public function __construct()
    {
        $this->setUri();
    }

    /**
     * @return void
     */
    private function setUri()
    {
        $this->uriBase = strtr($this->getServer('SCRIPT_NAME'), ['index.php' => '']);
        $rUri = preg_replace('/\?.*/', '', $this->getServer('REQUEST_URI')); // remove query
        $this->uriRelative = strtr($rUri, [$this->uriBase => '/']);
        $this->uriBase = rtrim($this->uriBase, '/'); // remove trailing slash from base URI
        $this->uri =$this->trimArray(explode('/', $this->uriRelative)); // make uri list
        if (preg_match('#/$#', $this->uriRelative)) { // If relative URI has slash at end
            return; // all is OK
        }
        $this->redirect($this->uriBase . $this->uriRelative . '/'); // Force trailing slash
    }

    /**
     * Allow a route
     * @param string $route
     * @param string $control
     */
    public function allow(string $route, string $control)
    {
        $this->routes[] = [
            'control' => $control,
            'route' => $this->trimArray(explode('/', $route)),
        ];
    }

    /**
     * Match request to a controller
     * @return string|null
     */
    public function match()
    {
        $this->uriCount = count($this->uri);
        $this->controls = array_column($this->routes, 'control');
        $this->setRoutingTypes();
        $control = $this->matchExact();
        if ($control) {
            return $control;
        }
        $control = $this->matchVariable();
        if ($control) {
            return $control;
        }
    }

    /**
     * split ->routing into ->routesExact and ->routesVariable
     * @return void
     */
    private function setRoutingTypes()
    {
        foreach (array_column($this->routes, 'route') as $routeId => $route) {
            if (in_array('?', $route)) {
                $this->routesVariable[$routeId] = $route;
                continue;
            }
            $this->routesExact[$routeId] = $route;
        }
    }

    /**
     * @return string|null
     */
    private function matchExact()
    {
        foreach ($this->routesExact as $routeId => $route) {
            if ($this->uri === $route) {
                return $this->controls[$routeId]; // matched - exact match
            }
        }
    }

    /**
     * @return string|null
     */
    private function matchVariable()
    {
        foreach ($this->routesVariable as $routeId => $route) {
            if ($this->uriCount !== count($route)) {
                continue; // match failed - not same size
            }
            $this->matchVariableSetVars($route);
            if (!empty($this->vars)) {
                return $this->controls[$routeId]; // matched - variable match
            }
        }
    }

    /**
     * @param array $route
     * @return void
     */
    private function matchVariableSetVars($route)
    {
        $this->vars = [];
        foreach ($route as $arrayId => $dir) {
            if ($dir !== '?' && $dir !== $this->uri[$arrayId]) {
                $this->vars = [];
                return; // match failed - no exact match, no variable match
            }
            if ($dir === '?') { // found a variable
                $this->vars[] = $this->uri[$arrayId];
            }
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
    }

    /**
     * get the value of a global _SERVER variable
     * @param string $name
     * @return mixed
     */
    private function getServer(string $name)
    {
        if (!empty($_SERVER[$name])) {
            return $_SERVER[$name];
        }
    }
}
