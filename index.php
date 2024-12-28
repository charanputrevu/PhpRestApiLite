<?php
/*
 * PHP Rest API Lite is free script to jumpstart deveoping REST APIs with JSON as data exchange format. Uses JWT for basic security. You can manipulate, extend to suit your uses
 * and also contribute.
 * 
 * Put forth by: The Incubator.
 */
require_once 'vendor/autoload.php';

use Theincubator\PhpRestApiLite\Helpers\Settings;
use Theincubator\PhpRestApiLite\Controller\UserController;
use Theincubator\PhpRestApiLite\Helpers\Routes;
use Theincubator\PhpRestApiLite\Helpers\JWT;
use Theincubator\PhpRestApiLite\Controller;
use Theincubator\PhpRestApiLite\TestController;

/**
 * Entry point for rest APIs. Route and token verification are done here.
 * If verification is successful respective Controller is invoked and response is sent back.
 *
 * @author charanputrevu
 */
class WebService {
    
    /**
     * Request JSON is stored in this variable.
     * @var array
     */
    private $data;
    
    /**
     * Has a copy of sanitized $_GET super global array
     * @var array
     */
    private $get;
    
    /**
     * Holds JWT object.
     * @var JWT
     */
    private $jwt;
    
    /**
     * Contains request headers.
     * @var array
     */
    private $headers;
    
    /**
     * Has response JSON array.
     * @var array
     */
    private $payload;
    
    /**
     * Contains response HTTP code.
     * @var int
     */
    private $httpCode = 200;
    
    /**
     * Has error string.
     * @var string
     */
    private $error = '';
    
    /**
     * Contains current route URL.
     * @var string
     */
    private $route;
    
    /**
     * Has route params from the URL.
     * @var array
     */
    private $routeParams;
    
    /**
     * Has the HTTP method of the request.
     * @var string
     */
    private $requestMethod;
    
    /**
     * Contains if the current route is an authenticated route or not.
     * @var bool
     */
    private $isAuthenticated = false;
    
    public function __construct() {
        $settings = new Settings();
        $this->extractHeaders();
        $this->setRouteDetails($settings->getProperty('endpoint'));
        $this->checkRoute();
        if ($this->isAuthenticated && !$settings->getProperty('useJwt')) {
            $this->verifyToken();
        }
        $this->extractData();
        $this->executeController();
        $this->sendResponse();
    }

    /**
     * Extract the headers from the request.
     */
    private function extractHeaders() {
        $this->headers = getallheaders();
    }

    /**
     * Verify the JWT token received in the headers.
     */
    private function verifyToken() {
        try {
            $this->jwt = new JWT();
            $this->jwt->decodeToken($this->headers);
        } catch (Exception $ex) {
            $this->httpCode = $ex->getCode();
            $this->error = $ex->getMessage();
            $this->sendResponse();
        }
    }

    /**
     * Extract and set route details.
     */
    private function setRouteDetails(string $endpoint) {
        $uriParts = explode($endpoint.'/', $_SERVER['REDIRECT_URL']);
        $routeParts = explode('/', $uriParts[1]);
        $this->route = array_shift($routeParts).'/'.array_shift($routeParts);
        $this->routeParams = $routeParts;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check route is valid or not.
     */
    private function checkRoute() {
        $routes = new Routes();
        try {
            $this->isAuthenticated = $routes->checkRoute($this->route, $this->requestMethod);
        } catch (Exception $ex) {
            $this->httpCode = $ex->getCode();
            $this->error = $ex->getMessage();
            $this->sendResponse();
        }
    }

    /**
     * Extract data from the request.
     */
    private function extractData() {
        $this->get = $this->sanitizeArray($_GET);
        try {
            $this->data = $this->sanitizeArray(json_decode(file_get_contents('php://input'), true));
        } catch (Exception $ex) {
            $this->httpCode = 400;
            $this->error = $ex->getMessage();
        }
    }
    
    /**
     * Sanitize data.
     * @param mixed $data
     * @return mixed
     */
    private function sanitizeData(mixed $data): mixed {
        if (is_string($data)) {
            return htmlentities($data);
        }
    }
    
    /**
     * Sanitize a multidimensional array recursively.
     *
     * @param array $data The input array to sanitize.
     * @param callable|null $callback Optional callback for sanitization. Defaults to htmlentities.
     * @return array The sanitized array.
     */
    private function sanitizeArray(array $data): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = sanitizeArray($value);
            } else {
                $data[$key] = $this->sanitizeData($value);
            }
        }
        return $data;
    }

    /**
     * Execute the required method from the controller.
     */
    private function executeController() {
        $routeParts = explode('/', $this->route);
        $method = array_pop($routeParts);
        $data = array();
        $controller = 'Theincubator\\PhpRestApiLite\\Controllers\\'.ucfirst(array_pop($routeParts)).'Controller';
        try {
            $controllerClass = new $controller();
            if (!$controllerClass instanceof Controller) {
                throw new Exception('Invalid Controller. Please check if class '.$controllerClass.' extends Theincubator\PhpRestApiLite\Controller', 500);
            }
            $controllerClass->setGetData($this->get);
            if (!empty($this->data)) {
                $controllerClass->setPostData($this->data);
            }
            if (empty($this->routeParams)) {
                $data = $controllerClass->$method();
            } else {
                $data = $controllerClass->$method(...$this->routeParams);
            }
            $this->payload['data'] = $data;
            $this->payload['success'] = $controllerClass->isSuccess();
            $this->httpCode = $controllerClass->getHttpCode();
        } catch (Exception $ex) {
            $this->httpCode = $ex->getCode();
            $this->error = $ex->getMessage();
            $this->sendResponse();
        }
        
    }

    /**
     * Send back response after processing the request.
     */
    private function sendResponse() {
        http_response_code($this->httpCode);
        if ($this->error !== '') {
            $this->payload['message'] = $this->error;
            $this->payload['success'] = false;
        }
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($this->payload);
        die();
    }
    
}
new WebService();
