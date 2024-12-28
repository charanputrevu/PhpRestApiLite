<?php
namespace Theincubator\PhpRestApiLite;


use Theincubator\PhpRestApiLite\Helpers\Exceptions\RoutesException;
use Theincubator\PhpRestApiLite\Helpers\Enums\HttpResponseCode;
/**
 * Basic controller class.
 *
 * @author charanputrevu
 */
class Controller {
    protected array $data = [
        'httpCode' => 200,
        'data' => array()
    ];
    
    /**
     * Has JSON data sent by POST request..
     * @var array
     */
    protected array $postData = [];
    
    /**
     * Has data sent by GET request. Extracted from $_GET.
     * @var array
     */
    protected array $getData = [];
    
    /**
     * 
     * @var HttpResponseCode
     */
    private int $httpCode = 200;
    
    /**
     * 
     * @var bool
     */
    private bool $success = true;


    public function setPostData($data) {
        $this->postData = $data;
    }
    
    public function setGetData($data) {
        $this->getData = $data;
    }
    
    public function __call($name, $arguments) {
        if(!method_exists($this, $name)) {
            throw (new RoutesException)->notFound();
        }
    }
    
    public function setHttpCode (HttpResponseCode $code) {
        $this->httpCode = $code->value;
        if ($this->httpCode > 399) {
            $this->success = false;        }
    }
    
    public function getHttpCode() {
        return $this->httpCode;
    }
    
    public function isSuccess() {
        return $this->success;
    }
}
