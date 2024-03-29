<?php
 
namespace Nettuts;
 
Class Router
{
	protected $routes;
    protected $request;
    protected $errorHandler;
 
    public function __construct()
    {
        $env = \Slim\Environment::getInstance();
        $this->request = new \Slim\Http\Request($env);
        $this->routes = array();
    }

    public function addRoutes($routes)
	{
	    foreach ($routes as $route => $path) {
	 
	        $method = "any";
	 
	        if (strpos($path, "@") !== false) {
	            list($path, $method) = explode("@", $path);
	        }
	         
	        $func = $this->processCallback($path);
	 
	        $r = new \Slim\Route($route, $func);
	        $r->setHttpMethods(strtoupper($method));
	 
	        array_push($this->routes, $r);
	    }
	}

	protected function processCallback($path)
	{
	    $class = "Main";
	 
	    if (strpos($path, ":") !== false) {
	        list($class, $path) = explode(":", $path);
	    }
	 
	    $function = ($path != "") ? $path : "index";
	 
	    $func = function () use ($class, $function) {
	        $class = '\Controller\\' . $class;
	        $class = new $class();
	 
	        $args = func_get_args();
	 
	        return call_user_func_array(array($class, $function), $args);
	    };
	 
	    return $func;
	}

	public function run()
	{
	    $display404 = true;
	    $uri = $this->request->getResourceUri();
	    $method = $this->request->getMethod();
	 
	    foreach ($this->routes as $i => $route) {
	        if ($route->matches($uri)) {
	            if ($route->supportsHttpMethod($method) || $route->supportsHttpMethod("ANY")) {
	                call_user_func_array($route->getCallable(), array_values($route->getParams()));
	                $display404 = false;
	            }
	        }
	    }
	 
	    if ($display404) {
		    if (is_callable($this->errorHandler)) {
		        call_user_func($this->errorHandler);
		    } else {
		        echo "404 - route not found";
		    }
		}
	}
	public function set404Handler($path)
	{
	    $this->errorHandler = $this->processCallback($path);
	}
}