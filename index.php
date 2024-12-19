<?php

/*
 * Copyrights BloodInfo@2022
 * This is copyrighted software for public service distribution. Any illegal software use and manipulation will be prosecuted.  * 
 */
error_reporting(E_ERROR);
ini_set('display_errors', 1);
require_once 'vendor/autoload.php';



use Theincubator\PhpRestApiLite\Helpers\Settings;
use Theincubator\PhpRestApiLite\Controller\UserController;
use Theincubator\PhpRestApiLite\Helpers\Routes;
use Theincubator\PhpRestApiLite\Helpers\JWT;
/**
 * Main class to process APIs
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
        $this->setRouteDetails();
        $this->checkRoute();
        if ($this->isAuthenticated && !$settings->getProperty('devMode')) {
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
    private function setRouteDetails() {
        $uriParts = explode('WebService/', $_SERVER['REQUEST_URI']);
        $routeParts = explode('/', $uriParts[1]);
        $this->route = array_shift($routeParts).'/'.array_shift($routeParts);
        $this->routeParams = $routeParts;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Check route is valid or not
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
        $this->get = $_GET;
        try {
            $this->data = json_decode(file_get_contents('php://input'), true);
        } catch (Exception $ex) {
            $this->httpCode = 400;
            $this->error = $ex->getMessage();
        }
    }
    
    /**
     * Execute the required method from the controller.
     */
    private function executeController() {
        $routeParts = explode('/', $this->route);
        $method = array_pop($routeParts);
        $data = array();
        $controller = ucfirst(array_pop($routeParts)).'Controller';
        try {
            $controllerClass = new $controller();
            $controllerClass->setGetData($this->get);
            $controllerClass->setPostData($this->data);
            if (empty($this->routeParams)) {
                $data = $controllerClass->$method();
            } else {
                $data = $controllerClass->$method(...$this->routeParams);
            }
            $this->payload['data'] = $data['data'];
            $this->httpCode = $data['httpCode'];
        } catch (Exception $ex) {
            $this->httpCode = $ex->getCode();
            $this->error = $ex->getMessage();
            $this->sendResponse();
        }
        
    }
    
    /**
     * Send a response after processing the request
     */
    private function sendResponse() {
        http_response_code($this->httpCode);
        $this->payload['success'] = true;
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
