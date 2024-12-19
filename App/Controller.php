<?php
namespace Theincubator\PhpRestApiLite;
/*
 * Copyrights BloodInfo@2022
 * This is copyrighted software for public service distribution. Any illegal software use and manipulation will be prosecuted.  * 
 */

use Theincubator\PhpRestApiLite\Helpers\Exceptions\RoutesException;
/**
 * Basic controller. Has all common parts a controller requires.
 *
 * @author charanputrevu
 */
class Controller {
    protected $data = [
        'httpCode' => 200,
        'data' => array()
    ];
    
    protected $postData;
    
    protected $getData;
    
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
}
