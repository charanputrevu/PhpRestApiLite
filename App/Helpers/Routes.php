<?php
namespace Theincubator\PhpRestApiLite\Helpers;

use Theincubator\PhpRestApiLite\Helpers\Exceptions\RoutesException;

/**
 * Class to process and authenticate routes.
 *
 * @author charanputrevu
 */
class Routes {
    
    private $routes;
    
    private $routesNoAuthentication = [];
    
    public function __construct() {
        $this->routes = json_decode(file_get_contents(__DIR__.'/routes.json'), true)['routes'][0];
        $this->routesNoAuthentication = json_decode(file_get_contents(__DIR__.'/routes.json'), true)['noAuthenticationRoutes'];
    }
    
    /**
     * Check if the current route is valid or not
     * @param string $route
     * @param string $role
     * @param string $method
     * @return boolean
     */
    public function checkRoute(string $route, string $method) {
        $isAuthenticated = true;
        
        $routeParts = explode("/", $route);
        
        if ($route === '/' || $this->routes[$routeParts[1]]['route'] !== $route) {
            throw (new RoutesException)->invalidRoute();
        }
        
        if ($this->routes[$routeParts[1]]['method'] !== $method) {
            throw (new RoutesException)->invalidHttpMethod();
        }
        
        if (in_array($routeParts[1], $this->routesNoAuthentication)) {
            $isAuthenticated = false;
        }

        return $isAuthenticated;
    }
    
    /**
     * Get the role of a route.
     * @param string $route
     * @return string
     */
    public function getRouteRole(string $route) {
        return $this->routes[$route]['role'];
    }
}
