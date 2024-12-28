<?php
namespace Theincubator\PhpRestApiLite\Helpers\Exceptions;

use Exception;
/**
 * Class to handle exceptions related to routes.
 *
 * @author charanputrevu
 */
class RoutesException extends Exception{
    public function invalidRoute() {
        return new Exception("Route Not Found", 404);
    }
    
    public function invalidHttpMethod() {
        return new Exception("Invalid HTTP Method", 405);
    }
    
    public function notFound() {
        return new Exception("Controller or method for route is not found", 404);
    }
}
