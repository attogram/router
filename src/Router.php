<?php

namespace Attogram\Router;

/**
 * Attogram Router
 */
class Router
{
    const VERSION = '0.0.2';

    private $uriBase = '';
    private $uriRelative = '';
    private $uri = [];
    private $uriCount = 0;
    private $routes = [];
    private $routing = [];
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
        $this->controls = array_column($this->routes, 'control');
        $this->routing = array_column($this->routes, 'route');
        $this->uriCount = count($this->uri);
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
     * @return string|null
     */
    private function matchExact()
    {
        foreach ($this->routing as $routeId => $route) { // Find exact match
            if ($this->uriCount !== count($route)) {
                continue; // match failed - not same size
            }
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
        foreach ($this->routing as $routeId => $route) { // Find variable match
            if (this->uriCount !== count($route)) {
                continue; // match failed - not same size
            }
            if (!in_array('?', $route)) {
                continue; // match failed - no variable matches defined
            }
            $this->vars = [];
            foreach ($route as $arrayId => $dir) {
                if ($dir !== '?' && $dir !== $this->uri[$arrayId]) {
                    $this->vars = [];
                    break; // match failed - no exact match, no variable match
                }
                if ($dir === '?') { // found a variable
                    $this->vars[] = $this->uri[$arrayId];
                }
            }
            if (!empty($this->vars)) {
                return $controls[$routeId]; // matched - variable match
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
